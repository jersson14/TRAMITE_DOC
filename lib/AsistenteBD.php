<?php
/**
 * Asistente que responde preguntas sobre los datos del sistema.
 *
 * Trabaja en dos pasos con la IA: primero le pide traducir la pregunta a una
 * consulta SELECT sobre el esquema descrito en EsquemaConsulta; esa consulta
 * pasa por ConsultaSegura (que la valida, la acota al área y la ejecuta) y
 * después le pide redactar la respuesta con las filas obtenidas.
 *
 * Así el chat contesta preguntas que nadie programó de antemano —"cuántos
 * trámites de Contabilidad se vencieron en agosto", "qué áreas no dieron
 * acuse"— en vez de las cuatro respuestas fijas que tenía antes.
 *
 * Si la pregunta no se puede responder con datos (un saludo, una duda de uso),
 * se contesta sin consultar nada.
 */
require_once __DIR__ . '/FabricaIA.php';
require_once __DIR__ . '/ConsultaSegura.php';
require_once __DIR__ . '/EsquemaConsulta.php';
require_once __DIR__ . '/Institucion.php';

class AsistenteBD
{
    /** Filas que se le muestran a la IA para que redacte (las demás se resumen). */
    const FILAS_AL_MODELO = 40;
    /** Reintentos cuando la consulta generada no pasa la validación. */
    const INTENTOS = 2;

    private $ia;
    private $esAdmin;
    private $areaId;
    private $areaNombre;
    private $rol;

    /** El cliente se recibe aparte para poder probar la cadena sin llamar a la API. */
    public function __construct(bool $esAdmin, ?int $areaId, string $areaNombre, string $rol, ?ClienteIA $ia = null)
    {
        $this->ia = $ia ?: FabricaIA::cliente();
        $this->esAdmin = $esAdmin;
        $this->areaId = $areaId;
        $this->areaNombre = $areaNombre;
        $this->rol = $rol;
    }

    public function disponible(): bool
    {
        return $this->ia->disponible();
    }

    /**
     * Responde la pregunta. Devuelve:
     * ['mensaje', 'sql' => ?string, 'tabla' => ?['columnas','filas'], 'total', 'recortada', 'aviso' => ?string]
     */
    public function responder(string $pregunta): array
    {
        $sql = null;
        $motivos = [];

        for ($intento = 1; $intento <= self::INTENTOS; $intento++) {
            $generada = $this->pedirConsulta($pregunta, $motivos);
            if ($generada === null) {
                // La IA decidió que no hace falta consultar la base
                return ['mensaje' => $this->conversar($pregunta), 'sql' => null, 'tabla' => null, 'total' => 0, 'recortada' => false];
            }
            try {
                $resultado = ConsultaSegura::ejecutar($generada, $this->esAdmin, $this->areaId);
                $sql = $resultado['sql'];
                return [
                    'mensaje'   => $this->redactar($pregunta, $resultado),
                    'sql'       => $sql,
                    'tabla'     => ['columnas' => $resultado['columnas'], 'filas' => $resultado['filas']],
                    'total'     => count($resultado['filas']),
                    'recortada' => $resultado['recortada'],
                ];
            } catch (RuntimeException $e) {
                // Se le dice qué estuvo mal para que lo corrija en el siguiente intento
                $motivos[] = $e->getMessage();
                error_log('[ASISTENTE] intento ' . $intento . ': ' . $e->getMessage());
            }
        }

        throw new RuntimeException('No pude armar una consulta válida para eso. ' . end($motivos));
    }

    /** Le pide a la IA solo el SQL. Devuelve null si contesta que no hace falta. */
    private function pedirConsulta(string $pregunta, array $motivos): ?string
    {
        $prompt = "Eres un traductor de preguntas en español a consultas SQL para MariaDB 10.4.\n"
            . "Base de datos de un sistema de trámite documentario de una entidad pública del Perú.\n\n"
            . "ESQUEMA DISPONIBLE (no existen otras tablas ni columnas):\n\n"
            . EsquemaConsulta::paraPrompt($this->esAdmin)
            . "REGLAS OBLIGATORIAS:\n"
            . "1. Responde ÚNICAMENTE con la consulta SQL, sin explicaciones, sin ```, sin punto y coma.\n"
            . "2. Solo SELECT. Una sola sentencia. Sin comentarios, sin WITH, sin subconsultas a otras bases.\n"
            . "3. Incluye siempre LIMIT (máximo " . ConsultaSegura::LIMITE_FILAS . ").\n"
            . "4. Da nombres claros en español a las columnas con AS (ej. AS expediente, AS 'Área').\n"
            . "5. Muestra nombres, no claves: une con area (area_cod) y tipo_documento (tipodocumento_id).\n"
            . "6. Fechas: usa DATE_FORMAT(campo, '%d/%m/%Y') o '%d/%m/%Y %H:%i' para mostrarlas.\n"
            . "7. Si la pregunta NO necesita datos de la base (saludo, agradecimiento, duda de uso del sistema),\n"
            . "   responde exactamente: NO_CONSULTA\n\n"
            . "CONTEXTO:\n"
            . "- Hoy es " . date('d/m/Y') . " (" . date('Y-m-d') . ").\n"
            . "- Institución: " . Institucion::datos()['sigla'] . ".\n"
            . "- Quien pregunta es " . $this->rol
            . ($this->esAdmin
                ? " y ve los trámites de toda la institución.\n"
                : " del área \"" . $this->areaNombre . "\", cuyo area_cod es " . (int) $this->areaId . ".\n"
                  . "  Sus tablas documento, movimiento y documento_anexo ya vienen limitadas a los trámites\n"
                  . "  en que participó su área, pero eso incluye los que ya pasaron a otra: para \"lo mío\",\n"
                  . "  \"mis pendientes\" o \"mi área\" filtra por area_cod = " . (int) $this->areaId
                  . " (area_destino en documento, areadestino_id en movimiento).\n")
            . "- El plazo de un trámite corre desde el último movimiento PRINCIPAL que lo trasladó\n"
            . "  (movimiento.mov_fecharegistro), no desde doc_fecharegistro, y dura dias_respuesta días\n"
            . "  hábiles. doc_fecharecepcion solo lo traen los trámites del portal del ciudadano: no lo\n"
            . "  uses para calcular atrasos. Si piden atrasos, compara con ese movimiento usando DATEDIFF\n"
            . "  y aclara en la respuesta que son días corridos, no hábiles.\n"
            . "- Los feriados que no cuentan para los plazos están en la tabla feriado.\n"
            . "- Un trámite está en curso si doc_estatus NO es FINALIZADO ni RECHAZADO.\n"
            . "- El movimiento PRINCIPAL es el que traslada la responsabilidad; COPIA es solo conocimiento.\n"
            . "- Sin acuse de recepción = movimiento con mov_recibido_fecha IS NULL.\n\n";

        if ($motivos) {
            $prompt .= "La consulta anterior fue rechazada por: " . implode(' | ', $motivos) . "\nCorrígela.\n\n";
        }
        $prompt .= "PREGUNTA: " . $pregunta . "\nSQL:";

        $respuesta = $this->ia->generarTexto($prompt, 0.0, 700);
        $texto = trim($respuesta);
        if ($texto === '' || stripos($texto, 'NO_CONSULTA') === 0) {
            return null;
        }
        return $texto;
    }

    /**
     * Cambia los nombres de personas por [persona N] antes de enviar las filas a
     * la IA. El mismo nombre recibe siempre el mismo número, así la IA puede
     * decir «tres trámites del mismo remitente» sin conocerlo. La tabla que ve
     * quien preguntó no pasa por aquí y conserva los nombres.
     */
    public static function ocultarPersonas(array $filas): array
    {
        $alias = [];
        foreach ($filas as $i => $fila) {
            foreach ($fila as $columna => $valor) {
                if ($valor === null || $valor === '' || is_numeric($valor)
                    || !preg_match(EsquemaConsulta::PATRON_NOMBRE_PERSONA, $columna)
                    || preg_match(EsquemaConsulta::PATRON_NO_PERSONA, $columna)) {
                    continue;
                }
                $clave = mb_strtoupper(trim((string) $valor));
                if (!isset($alias[$clave])) {
                    $alias[$clave] = '[persona ' . (count($alias) + 1) . ']';
                }
                $filas[$i][$columna] = $alias[$clave];
            }
        }
        return $filas;
    }

    /** Le pide redactar la respuesta a partir de las filas obtenidas. */
    private function redactar(string $pregunta, array $resultado): string
    {
        $filas = $resultado['filas'];
        $total = count($filas);
        $muestra = self::ocultarPersonas(array_slice($filas, 0, self::FILAS_AL_MODELO));

        $prompt = "Eres el asistente del sistema de trámite documentario de " . Institucion::datos()['sigla'] . ".\n"
            . "Responde en español, claro y breve, a quien preguntó. Usa los datos tal como están.\n\n"
            . "REGLAS:\n"
            . "- No inventes datos ni columnas que no estén en el resultado.\n"
            . "- Si el resultado está vacío, dilo con naturalidad y sugiere cómo reformular.\n"
            . "- Para pocas filas, redacta; para varias, usa una lista corta con lo esencial.\n"
            . "- No repitas la tabla completa: debajo de tu respuesta ya se muestra al usuario.\n"
            . "- No menciones SQL ni nombres de tablas o columnas internas.\n"
            . "- Los nombres de personas llegan ocultos como [persona 1], [persona 2]: el mismo número es\n"
            . "  la misma persona. No los repitas; di «el remitente», «el firmante» o «esa persona».\n"
            . "  Quien pregunta ve los nombres en la tabla debajo de tu respuesta.\n"
            . ($this->esAdmin ? '' :
                "- Solo se consultan los trámites en que participó el área \"" . $this->areaNombre . "\":\n"
                . "  si preguntaron por otra área y no hay filas, dilo así, no como si esa área no tuviera nada.\n")
            . "- Máximo 120 palabras.\n\n"
            . "PREGUNTA: " . $pregunta . "\n"
            . "FILAS OBTENIDAS ($total" . ($total > count($muestra) ? ", se muestran " . count($muestra) : '') . "):\n"
            . json_encode($muestra, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR) . "\n\n"
            . "RESPUESTA:";

        $texto = trim($this->ia->generarTexto($prompt, 0.3, 700));
        if ($texto === '') {
            return $total
                ? "Encontré $total resultado(s) para tu consulta."
                : 'No encontré registros que cumplan con eso.';
        }
        return $texto;
    }

    /** Preguntas que no son de datos: uso del sistema, saludos. */
    private function conversar(string $pregunta): string
    {
        $prompt = "Eres el asistente del sistema de trámite documentario de " . Institucion::datos()['sigla'] . ".\n"
            . "Quien escribe es " . $this->rol . ($this->esAdmin ? '' : ' del área ' . $this->areaNombre) . ".\n"
            . "Responde en español, en menos de 90 palabras. Puedes explicar cómo se usa el sistema\n"
            . "(registrar trámites, derivar, dar acuse de recepción, plazos en días hábiles, firma digital,\n"
            . "reportes y exportaciones, comunicados) y ofrecer ejemplos de preguntas sobre los datos:\n"
            . "«¿cuántos trámites tengo pendientes?», «expedientes vencidos por área», «trámites sin acuse».\n"
            . "No inventes cifras: para dar números hace falta consultar, y eso se hace con otra pregunta.\n\n"
            . "MENSAJE: " . $pregunta . "\nRESPUESTA:";

        $texto = trim($this->ia->generarTexto($prompt, 0.4, 500));
        return $texto !== '' ? $texto : 'Puedo consultar los trámites, plazos y movimientos del sistema. ¿Qué necesitas saber?';
    }
}
