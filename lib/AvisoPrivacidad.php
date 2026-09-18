<?php
/**
 * Aviso de privacidad del portal ciudadano (Ley N° 29733, deber de informar).
 *
 * Quien presenta un trámite entrega su DNI, nombre, celular, correo y dirección.
 * Antes de hacerlo debe saber quién trata esos datos, para qué, con quién se
 * comparten, cuánto se guardan y cómo ejercer sus derechos. El portal muestra
 * este aviso y no deja registrar sin aceptarlo (también lo exige el servidor).
 *
 * El texto lo decide cada institución con su área legal y se guarda en el panel
 * (Configuración → Aviso de privacidad). Mientras no lo haga, se usa un texto
 * base armado con los datos de la institución.
 */
require_once __DIR__ . '/Configuracion.php';
require_once __DIR__ . '/Institucion.php';

class AvisoPrivacidad
{
    const LARGO_MAXIMO = 15000;

    /** El texto vigente: el del panel o, si está vacío, el texto base. */
    public static function texto(): string
    {
        $propio = trim(Configuracion::obtener('privacidad_texto'));
        return $propio !== '' ? $propio : self::textoBase();
    }

    public static function esPropio(): bool
    {
        return trim(Configuracion::obtener('privacidad_texto')) !== '';
    }

    /** Texto base con los datos de la institución. Debe revisarlo su área legal. */
    public static function textoBase(): string
    {
        $i = Institucion::datos();
        // Los datos de la institución se guardan con entidades HTML (&amp;, &#039;)
        $plano = function ($valor) {
            return trim(html_entity_decode((string) $valor, ENT_QUOTES, 'UTF-8'));
        };
        $razon = $plano($i['razon']);
        $direccion = $plano($i['direccion'] ?? '');
        $correo = $plano($i['email'] ?? '');
        $contacto = $correo !== '' ? "al correo $correo" : 'en la mesa de partes de la institución';

        return "Responsable del tratamiento: $razon" . ($direccion !== '' ? ", con domicilio en $direccion" : '') . ".\n\n"
            . "Qué datos se recogen: número de DNI, nombres y apellidos, celular, correo electrónico y dirección de "
            . "quien presenta el trámite; RUC y razón social si lo presenta en representación de una persona jurídica; "
            . "los documentos que adjunta, y la dirección IP y la fecha y hora de cada envío.\n\n"
            . "Para qué se usan: registrar y atender su trámite, comunicarle su número de expediente, notificarle las "
            . "observaciones y el resultado, permitirle consultar su estado y dejar constancia de lo actuado. No se usan "
            . "para otros fines.\n\n"
            . "Son obligatorios: sin ellos no es posible registrar ni atender el trámite.\n\n"
            . "Con quién se comparten: con el personal de las áreas que intervienen en su trámite y con las entidades a las "
            . "que la ley obligue a remitirlos. Para operar el sistema intervienen proveedores de servicios tecnológicos "
            . "(correo electrónico y validación del DNI) que solo tratan los datos por encargo de la institución.\n\n"
            . "Cuánto tiempo se guardan: el que establezcan las normas de archivo aplicables a los expedientes "
            . "administrativos.\n\n"
            . "Sus derechos: puede solicitar el acceso, la rectificación, la cancelación o la oposición al tratamiento de sus "
            . "datos (derechos ARCO) $contacto. Si considera que no fue atendido, puede acudir a la Autoridad Nacional de "
            . "Protección de Datos Personales del Ministerio de Justicia y Derechos Humanos.\n\n"
            . "Los datos se guardan en el banco de datos de trámite documentario de la institución, con medidas de seguridad "
            . "para evitar su pérdida, alteración o acceso no autorizado.";
    }

    /** El texto en HTML: cada línea en blanco separa un párrafo. */
    public static function html(): string
    {
        $html = '';
        foreach (preg_split('/\n\s*\n/', self::texto()) as $parrafo) {
            $parrafo = trim($parrafo);
            if ($parrafo !== '') {
                $html .= '<p>' . nl2br(htmlspecialchars($parrafo, ENT_QUOTES, 'UTF-8')) . '</p>';
            }
        }
        return $html;
    }
}
