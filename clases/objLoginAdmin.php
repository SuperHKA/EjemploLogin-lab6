<?php

final class ValidacionLogin
{
    private ?object $usuarioEncontrado = null;
    private string $usuario;
    private string $contrasena;
    private string $ip;
    private mod_db $db;
    private bool $loginExitoso = false;

    public function __construct(string $usuario, string $contrasena, string $ipRemoto, mod_db $db)
    {
        $this->usuario = SanitizarEntrada::usuario($usuario);
        $this->contrasena = $contrasena;
        $this->ip = $ipRemoto;
        $this->db = $db;
    }

    public function logger(): bool
    {
        $this->usuarioEncontrado = $this->db->buscarUsuario($this->usuario);
        return $this->usuarioEncontrado !== null;
    }

    public function autenticar(): bool
    {
        if (!$this->usuarioEncontrado) {
            $this->loginExitoso = false;
            return false;
        }

        $this->loginExitoso = password_verify($this->contrasena, $this->usuarioEncontrado->HashMagic);
        return $this->loginExitoso;
    }

    public function registrarIntentos(): void
    {
        $estado = $this->loginExitoso ? 'exitoso' : 'fallido';
        $this->db->registrarIntento($this->usuario, $estado, $this->ip);
    }

    public function getIntentoLogin(): bool
    {
        return $this->loginExitoso;
    }

    public function getUsuario(): string
    {
        return $this->usuario;
    }

    public function getUsuarioEncontrado(): ?object
    {
        return $this->usuarioEncontrado;
    }
}
