<?php

final class SanitizarEntrada
{
    public static function limpiarCadena(?string $cadena): string
    {
        return trim(strip_tags((string) $cadena));
    }

    public static function usuario(?string $usuario): string
    {
        $usuario = self::limpiarCadena($usuario);
        return preg_replace('/[^a-zA-Z0-9_.-]/', '', $usuario) ?? '';
    }

    public static function correo(?string $correo): string
    {
        $correo = trim((string) $correo);
        $correo = filter_var($correo, FILTER_SANITIZE_EMAIL);
        return strtolower((string) $correo);
    }

    public static function codigo2fa(?string $codigo): string
    {
        return preg_replace('/\D/', '', (string) $codigo) ?? '';
    }

    public static function salidaHtml(?string $valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}
