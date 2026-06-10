<?php

final class RegistroUsuario
{
    private mod_db $db;

    public function __construct(mod_db $db)
    {
        $this->db = $db;
    }

    public function registrar(array $entrada): array
    {
        $nombre = SanitizarEntrada::limpiarCadena($entrada['nombre'] ?? '');
        $apellido = SanitizarEntrada::limpiarCadena($entrada['apellido'] ?? '');
        $usuario = SanitizarEntrada::usuario($entrada['usuario'] ?? '');
        $correo = SanitizarEntrada::correo($entrada['correo'] ?? '');
        $sexo = strtoupper(SanitizarEntrada::limpiarCadena($entrada['sexo'] ?? ''));
        $contrasena = (string) ($entrada['contrasena'] ?? '');
        $confirmar = (string) ($entrada['confirmar_contrasena'] ?? '');
        $errores = [];

        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }
        if ($apellido === '') {
            $errores[] = 'El apellido es obligatorio.';
        }
        if (strlen($usuario) < 4) {
            $errores[] = 'El usuario debe tener al menos 4 caracteres.';
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo no tiene un formato valido.';
        }
        if (!in_array($sexo, ['M', 'F', 'O'], true)) {
            $errores[] = 'Seleccione un sexo valido.';
        }
        if (strlen($contrasena) < 8) {
            $errores[] = 'La contrasena debe tener al menos 8 caracteres.';
        }
        if ($contrasena !== $confirmar) {
            $errores[] = 'Las contrasenas no coinciden.';
        }
        if ($usuario !== '' && $this->db->buscarUsuario($usuario)) {
            $errores[] = 'El usuario ya existe.';
        }
        if ($correo !== '' && $this->db->buscarCorreo($correo)) {
            $errores[] = 'El correo ya esta registrado.';
        }

        if ($errores) {
            return [
                'ok' => false,
                'errores' => $errores,
                'usuario' => null,
            ];
        }

        $secret = TwoFactorAuth::generarSecret();
        $id = $this->db->insertarUsuario([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'usuario' => $usuario,
            'correo' => $correo,
            'hash' => password_hash($contrasena, PASSWORD_DEFAULT),
            'sexo' => $sexo,
            'secret_2fa' => $secret,
        ]);

        $this->db->registrarTrazabilidad('usuarios', 'REGISTRO_USUARIO', $id, $usuario);

        return [
            'ok' => true,
            'errores' => [],
            'usuario' => $this->db->buscarUsuario($usuario),
        ];
    }
}
