<?php
session_start();

require_once __DIR__ . '/clases/Csrf.php';
require_once __DIR__ . '/clases/SanitizarEntrada.php';
require_once __DIR__ . '/clases/mysql.inc.php';
require_once __DIR__ . '/clases/TwoFactorAuth.php';

if (($_SESSION['autenticado'] ?? '') === 'SI' && ($_SESSION['2fa_verificado'] ?? false) === true) {
    header('Location: dashboard.php');
    exit;
}

$pendiente = $_SESSION['usuario_pendiente_2fa'] ?? null;
if (!$pendiente) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
        $_SESSION['flash'] = [
            'tipo' => 'error',
            'mensaje' => 'La sesion expiro. Intenta nuevamente.',
        ];
        header('Location: validar_2fa.php');
        exit;
    }

    $codigo = SanitizarEntrada::codigo2fa($_POST['codigo'] ?? '');
    $secret = (string) ($pendiente['secret_2fa'] ?? '');

    if (!TwoFactorAuth::verificarCodigo($secret, $codigo)) {
        $_SESSION['flash'] = [
            'tipo' => 'error',
            'mensaje' => 'Codigo 2FA incorrecto o vencido.',
        ];
        header('Location: validar_2fa.php');
        exit;
    }

    try {
        $db = new mod_db();
        $db->registrarTrazabilidad('usuarios', 'LOGIN_2FA_OK', (int) $pendiente['id'], (string) $pendiente['Usuario']);
    } catch (Throwable $error) {
        // La trazabilidad no debe impedir una autenticacion ya validada.
    }

    session_regenerate_id(true);
    $_SESSION['autenticado'] = 'SI';
    $_SESSION['Usuario'] = (string) $pendiente['Usuario'];
    $_SESSION['usuario_id'] = (int) $pendiente['id'];
    $_SESSION['correo'] = (string) $pendiente['Correo'];
    $_SESSION['2fa_verificado'] = true;
    unset($_SESSION['usuario_pendiente_2fa']);

    header('Location: dashboard.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validar 2FA | Laboratorio de Autenticación</title>
    <link rel="stylesheet" href="assets/auth.css">
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card auth-card-compact">
            <div class="brand-mark" aria-hidden="true">2F</div>
            <h1>Codigo 2FA</h1>
            <p class="lead">Escribe el codigo actual de Google Authenticator.</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo SanitizarEntrada::salidaHtml($flash['tipo'] ?? 'info'); ?>">
                    <?php echo SanitizarEntrada::salidaHtml($flash['mensaje'] ?? ''); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="validar_2fa.php" class="auth-form">
                <?php echo Csrf::input(); ?>
                <label for="codigo">Codigo de 6 digitos</label>
                <input
                    id="codigo"
                    name="codigo"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    autocomplete="one-time-code"
                    required
                    autofocus
                >
                <button type="submit" class="button-primary">Validar acceso</button>
            </form>

            <p class="switch-link">
                Necesitas escanear el QR?
                <a href="setup_2fa.php">Ver configuracion</a>
            </p>
        </section>
    </main>
</body>
</html>
