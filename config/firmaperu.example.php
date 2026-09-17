<?php
/**
 * Firma Perú (PCM) - firma con DNI electrónico o token criptográfico.
 *
 * Copie este archivo como config/firmaperu.php y complete los datos. Ese archivo
 * NO se sube al repositorio (tiene credenciales).
 *
 * Para obtener las credenciales, la entidad pública debe solicitarlas a la
 * Secretaría de Gobierno y Transformación Digital de la PCM (servicio Firma
 * Perú). Se entregan un client_id y un client_secret, y los usuarios deben tener
 * instalado el Firmador de Firma Perú en su computadora con el lector de DNIe o
 * el token.
 *
 * Mientras 'habilitado' sea false o falten las credenciales, el sistema muestra
 * la opción con un aviso y permite firmar con certificado en archivo (.pfx/.p12).
 */
return [
    'habilitado'    => false,
    'client_id'     => '',
    'client_secret' => '',
    // Puerto local del Firmador de Firma Perú (el valor por defecto lo indica la PCM)
    'puerto_local'  => 48596,
];
