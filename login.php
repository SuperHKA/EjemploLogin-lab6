<?php
session_start();

require_once __DIR__ . '/clases/Csrf.php';
require_once __DIR__ . '/clases/SanitizarEntrada.php';

if (($_SESSION['autenticado'] ?? '') === 'SI' && ($_SESSION['2fa_verificado'] ?? false) === true) {
    header('Location: dashboard.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
$old = $_SESSION['old_login'] ?? [];
unset($_SESSION['flash'], $_SESSION['old_login']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Laboratorio de Autenticación</title>
    <link rel="stylesheet" href="assets/auth.css">
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card auth-card-compact">
            <div class="brand-mark" aria-hidden="true">LA</div>
            <h1>Panel de acceso</h1>
            <p class="lead">Ingresa con tu usuario y continua con la verificacion 2FA.</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo SanitizarEntrada::salidaHtml($flash['tipo'] ?? 'info'); ?>">
                    <?php echo SanitizarEntrada::salidaHtml($flash['mensaje'] ?? ''); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php" class="auth-form" autocomplete="on">
                <?php echo Csrf::input(); ?>
                <label for="usuario">Usuario</label>
                <input
                    id="usuario"
                    name="usuario"
                    type="text"
                    minlength="4"
                    value="<?php echo SanitizarEntrada::salidaHtml($old['usuario'] ?? ''); ?>"
                    required
                    autofocus
                >

                <label for="contrasena">Contrasena</label>
                <input id="contrasena" name="contrasena" type="password" required>

                <button type="submit" class="button-primary">Entrar</button>
            </form>

            <p class="switch-link">
                No tienes cuenta?
                <a href="registro.php">Crear registro</a>
            </p>
        </section>
    </main>
</body>
</html>
