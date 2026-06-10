<?php
session_start();

require_once __DIR__ . '/clases/Csrf.php';
require_once __DIR__ . '/clases/SanitizarEntrada.php';
require_once __DIR__ . '/clases/TwoFactorAuth.php';

$pendiente = $_SESSION['usuario_pendiente_2fa'] ?? null;
if (!$pendiente) {
    header('Location: login.php');
    exit;
}

$issuer = 'Laboratorio de Autenticación';
$usuario = (string) $pendiente['Usuario'];
$secret = (string) $pendiente['secret_2fa'];
$uri = TwoFactorAuth::otpauthUri($issuer, $usuario, $secret);
$qrHtml = TwoFactorAuth::qrHtml($uri);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar 2FA | Laboratorio de Autenticación</title>
    <link rel="stylesheet" href="assets/auth.css">
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card auth-card-wide">
            <div class="brand-row">
                <div class="brand-mark" aria-hidden="true">2F</div>
                <div>
                    <h1>Activar 2FA</h1>
                    <p class="lead">Escanea el QR con Google Authenticator y confirma el codigo.</p>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo SanitizarEntrada::salidaHtml($flash['tipo'] ?? 'info'); ?>">
                    <?php echo SanitizarEntrada::salidaHtml($flash['mensaje'] ?? ''); ?>
                </div>
            <?php endif; ?>

            <div class="twofa-layout">
                <div class="qr-box">
                    <?php echo $qrHtml; ?>
                </div>
                <div class="setup-panel">
                    <p class="muted">Cuenta</p>
                    <strong><?php echo SanitizarEntrada::salidaHtml($usuario); ?></strong>

                    <p class="muted">Clave manual</p>
                    <code class="secret-code"><?php echo SanitizarEntrada::salidaHtml($secret); ?></code>

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
                        >
                        <button type="submit" class="button-primary">Verificar codigo</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
