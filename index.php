<?php
session_start();

require_once __DIR__ . '/clases/Csrf.php';
require_once __DIR__ . '/clases/SanitizarEntrada.php';
require_once __DIR__ . '/clases/mysql.inc.php';
require_once __DIR__ . '/clases/TwoFactorAuth.php';
require_once __DIR__ . '/clases/objLoginAdmin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$usuario = $_POST['usuario'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
    $_SESSION['flash'] = [
        'tipo' => 'error',
        'mensaje' => 'La sesion expiro. Intenta nuevamente.',
    ];
    $_SESSION['old_login'] = ['usuario' => SanitizarEntrada::usuario($usuario)];
    header('Location: login.php');
    exit;
}

try {
    $db = new mod_db();
    $ipRemota = $_SERVER['REMOTE_ADDR'] ?? '';
    $login = new ValidacionLogin($usuario, $contrasena, $ipRemota, $db);

    $usuarioExiste = $login->logger();
    $autenticado = $usuarioExiste && $login->autenticar();
    $login->registrarIntentos();

    if (!$autenticado) {
        $_SESSION['flash'] = [
            'tipo' => 'error',
            'mensaje' => 'Usuario o contrasena incorrectos.',
        ];
        $_SESSION['old_login'] = ['usuario' => $login->getUsuario()];
        header('Location: login.php');
        exit;
    }

    $usuarioData = $login->getUsuarioEncontrado();
    $secret = trim((string) ($usuarioData->secret_2fa ?? ''));
    $requiereSetup = $secret === '';

    if ($requiereSetup) {
        $secret = TwoFactorAuth::generarSecret();
        $db->actualizarSecret2fa((int) $usuarioData->id, $secret);
    }

    session_regenerate_id(true);
    $_SESSION['usuario_pendiente_2fa'] = [
        'id' => (int) $usuarioData->id,
        'Usuario' => $usuarioData->Usuario,
        'Correo' => $usuarioData->Correo,
        'secret_2fa' => $secret,
    ];
    unset($_SESSION['autenticado'], $_SESSION['Usuario'], $_SESSION['2fa_verificado']);

    $db->registrarTrazabilidad('usuarios', 'PASSWORD_OK', (int) $usuarioData->id, $usuarioData->Usuario);

    header('Location: ' . ($requiereSetup ? 'setup_2fa.php' : 'validar_2fa.php'));
    exit;
} catch (Throwable $error) {
    $_SESSION['flash'] = [
        'tipo' => 'error',
        'mensaje' => 'No se pudo procesar el login. Revisa la conexion o intenta mas tarde.',
    ];
    $_SESSION['old_login'] = ['usuario' => SanitizarEntrada::usuario($usuario)];
    header('Location: login.php');
    exit;
}
