<?php
session_start();

require_once __DIR__ . '/clases/SanitizarEntrada.php';

if (($_SESSION['autenticado'] ?? '') !== 'SI' || ($_SESSION['2fa_verificado'] ?? false) !== true) {
    unset($_SESSION['Usuario'], $_SESSION['usuario_id'], $_SESSION['correo'], $_SESSION['2fa_verificado']);
    header('Location: login.php');
    exit;
}

$usuario = (string) ($_SESSION['Usuario'] ?? '');
$correo = (string) ($_SESSION['correo'] ?? '');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Laboratorio de Autenticación</title>
    <link rel="stylesheet" href="assets/auth.css">
</head>
<body class="dashboard-body">
    <header class="topbar">
        <div class="topbar-brand">
            <span class="brand-mark small" aria-hidden="true">LA</span>
            <span>Laboratorio de Autenticación</span>
        </div>
        <a class="button-secondary" href="salir.php">Cerrar sesion</a>
    </header>

    <main class="dashboard-shell">
        <section class="welcome-panel">
            <p class="eyebrow">Sesion verificada con 2FA</p>
            <h1>Bienvenido, <?php echo SanitizarEntrada::salidaHtml($usuario); ?></h1>
            <p><?php echo SanitizarEntrada::salidaHtml($correo); ?></p>
        </section>

        <section class="metric-grid" aria-label="Resumen">
            <article class="metric-card">
                <span>Estado</span>
                <strong>Activo</strong>
            </article>
            <article class="metric-card">
                <span>Autenticacion</span>
                <strong>2FA OK</strong>
            </article>
            <article class="metric-card">
                <span>Panel</span>
                <strong>Seguro</strong>
            </article>
        </section>
    </main>
</body>
</html>
