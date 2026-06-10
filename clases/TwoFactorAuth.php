<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

final class TwoFactorAuth
{
    public static function generarSecret(int $length = 20): string
    {
        $authenticator = new GoogleAuthenticator(6, $length);
        return $authenticator->generateSecret();
    }

    public static function otpauthUri(string $issuer, string $usuario, string $secret): string
    {
        $label = rawurlencode($issuer . ':' . $usuario);
        $issuerParam = rawurlencode($issuer);

        return "otpauth://totp/$label?secret=$secret&issuer=$issuerParam&algorithm=SHA1&digits=6&period=30";
    }

    public static function qrHtml(string $uri): string
    {
        $tcpdfFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'TCPDF-main' . DIRECTORY_SEPARATOR . 'tcpdf_barcodes_2d.php';

        if (is_file($tcpdfFile)) {
            require_once $tcpdfFile;
            $barcode = new TCPDF2DBarcode($uri, 'QRCODE,M');
            return $barcode->getBarcodeHTML(5, 5, '#151515');
        }

        return '<p class="alert alert-error">No se encontro TCPDF para generar el QR.</p>';
    }

    public static function verificarCodigo(string $secret, string $codigo, int $window = 4): bool
    {
        $codigo = trim($codigo);

        if (!preg_match('/^\d{6}$/', $codigo)) {
            return false;
        }

        $secret = trim($secret);
        $authenticator = new GoogleAuthenticator();
        return $authenticator->checkCode($secret, $codigo, $window);
    }
}
