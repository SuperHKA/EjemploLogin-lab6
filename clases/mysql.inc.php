<?php

class mod_db
{
    private PDO $conexion;

    public function __construct()
    {
        $sql_host = 'localhost';
        $sql_name = 'company_info';
        $sql_user = 'login_user';
        $sql_pass = 'Login12345*';

        $dsn = "mysql:host=$sql_host;dbname=$sql_name;charset=utf8mb4";
        $this->conexion = new PDO($dsn, $sql_user, $sql_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ]);
    }

    public function getConexion(): PDO
    {
        return $this->conexion;
    }

    public function buscarUsuario(string $usuario): ?object
    {
        $stmt = $this->conexion->prepare('SELECT * FROM usuarios WHERE Usuario = :usuario LIMIT 1');
        $stmt->execute(['usuario' => $usuario]);
        $usuarioEncontrado = $stmt->fetch();

        return $usuarioEncontrado ?: null;
    }

    public function buscarCorreo(string $correo): ?object
    {
        $stmt = $this->conexion->prepare('SELECT * FROM usuarios WHERE Correo = :correo LIMIT 1');
        $stmt->execute(['correo' => $correo]);
        $correoEncontrado = $stmt->fetch();

        return $correoEncontrado ?: null;
    }

    public function insertarUsuario(array $data): int
    {
        $sql = 'INSERT INTO usuarios
            (Nombre, Apellido, Usuario, Correo, HashMagic, Sexo, secret_2fa)
            VALUES
            (:nombre, :apellido, :usuario, :correo, :hash, :sexo, :secret_2fa)';

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'usuario' => $data['usuario'],
            'correo' => $data['correo'],
            'hash' => $data['hash'],
            'sexo' => $data['sexo'],
            'secret_2fa' => $data['secret_2fa'],
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    public function actualizarSecret2fa(int $id, string $secret): bool
    {
        $stmt = $this->conexion->prepare('UPDATE usuarios SET secret_2fa = :secret WHERE id = :id');
        return $stmt->execute([
            'secret' => $secret,
            'id' => $id,
        ]);
    }

    public function registrarIntento(string $usuario, string $estado, ?string $ipRemota): bool
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO intentos_login (Usuario, estado, ipRemota) VALUES (:usuario, :estado, :ipRemota)'
        );

        return $stmt->execute([
            'usuario' => $usuario !== '' ? $usuario : 'desconocido',
            'estado' => $estado,
            'ipRemota' => $ipRemota,
        ]);
    }

    public function registrarTrazabilidad(string $tabla, string $accion, ?int $codigoRegistro, ?string $usuario): bool
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO trazabilidad_acciones (Tabla, Acciones, CodigoRegistro, Usuario)
             VALUES (:tabla, :accion, :codigoRegistro, :usuario)'
        );

        return $stmt->execute([
            'tabla' => $tabla,
            'accion' => $accion,
            'codigoRegistro' => $codigoRegistro,
            'usuario' => $usuario,
        ]);
    }
}
