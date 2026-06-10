<?php
session_start();

require_once __DIR__ . '/clases/Csrf.php';
require_once __DIR__ . '/clases/SanitizarEntrada.php';
require_once __DIR__ . '/clases/mysql.inc.php';
require_once __DIR__ . '/clases/TwoFactorAuth.php';
require_once __DIR__ . '/clases/objRegistroUsuario.php';

if (($_SESSION['autenticado'] ?? '') === 'SI' && ($_SESSION['2fa_verificado'] ?? false) === true) {
    header('Location: dashboard.php');
    exit;
}

$errores = [];
$old = [
    'nombre' => '',
    'apellido' => '',
    'usuario' => '',
    'correo' => '',
    'sexo' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'nombre' => SanitizarEntrada::limpiarCadena($_POST['nombre'] ?? ''),
        'apellido' => SanitizarEntrada::limpiarCadena($_POST['apellido'] ?? ''),
        'usuario' => SanitizarEntrada::usuario($_POST['usuario'] ?? ''),
        'correo' => SanitizarEntrada::correo($_POST['correo'] ?? ''),
        'sexo' => strtoupper(SanitizarEntrada::limpiarCadena($_POST['sexo'] ?? '')),
    ];

    if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
        $errores[] = 'La sesion expiro. Recarga el formulario e intenta nuevamente.';
    } else {
        try {
            $db = new mod_db();
            $registro = new RegistroUsuario($db);
            $resultado = $registro->registrar($_POST);

            if ($resultado['ok']) {
                $usuario = $resultado['usuario'];
                session_regenerate_id(true);
                $_SESSION['usuario_pendiente_2fa'] = [
                    'id' => (int) $usuario->id,
                    'Usuario' => $usuario->Usuario,
                    'Correo' => $usuario->Correo,
                    'secret_2fa' => $usuario->secret_2fa,
                ];
                $_SESSION['flash'] = [
                    'tipo' => 'success',
                    'mensaje' => 'Registro creado. Escanea el QR para activar 2FA.',
                ];
                header('Location: setup_2fa.php');
                exit;
            }

            $errores = $resultado['errores'];
        } catch (Throwable $error) {
            $errores[] = 'No se pudo completar el registro. Verifica la base de datos e intenta de nuevo.';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro | Laboratorio de Autenticación</title>
    <link rel="stylesheet" href="assets/auth.css">
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card auth-card-wide">
            <div class="brand-row">
                <div class="brand-mark" aria-hidden="true">LA</div>
                <div>
                    <h1>Crear cuenta</h1>
                    <p class="lead">Registra tus datos y activa Google Authenticator.</p>
                </div>
            </div>

            <?php if ($errores): ?>
                <div class="alert alert-error">
                    <strong>Revisa estos datos:</strong>
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo SanitizarEntrada::salidaHtml($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="registro.php" class="auth-form form-grid" autocomplete="on">
                <?php echo Csrf::input(); ?>

                <div>
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" type="text" value="<?php echo SanitizarEntrada::salidaHtml($old['nombre']); ?>" required>
                </div>

                <div>
                    <label for="apellido">Apellido</label>
                    <input id="apellido" name="apellido" type="text" value="<?php echo SanitizarEntrada::salidaHtml($old['apellido']); ?>" required>
                </div>

                <div>
                    <label for="usuario">Usuario</label>
                    <input id="usuario" name="usuario" type="text" minlength="4" value="<?php echo SanitizarEntrada::salidaHtml($old['usuario']); ?>" required>
                </div>

                <div>
                    <label for="correo">Correo</label>
                    <input id="correo" name="correo" type="email" value="<?php echo SanitizarEntrada::salidaHtml($old['correo']); ?>" required>
                </div>

                <div>
                    <label for="sexo">Sexo</label>
                    <select id="sexo" name="sexo" required>
                        <option value="">Seleccionar</option>
                        <option value="M" <?php echo $old['sexo'] === 'M' ? 'selected' : ''; ?>>Masculino</option>
                        <option value="F" <?php echo $old['sexo'] === 'F' ? 'selected' : ''; ?>>Femenino</option>
                        <option value="O" <?php echo $old['sexo'] === 'O' ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>

                <div>
                    <label for="contrasena">Contrasena</label>
                    <input id="contrasena" name="contrasena" type="password" minlength="8" required>
                </div>

                <div class="full-span">
                    <label for="confirmar_contrasena">Confirmar contrasena</label>
                    <input id="confirmar_contrasena" name="confirmar_contrasena" type="password" minlength="8" required>
                </div>

                <button type="submit" class="button-primary full-span">Crear cuenta</button>
            </form>

            <p class="switch-link">
                Ya tienes cuenta?
                <a href="login.php">Volver al login</a>
            </p>
        </section>
    </main>
</body>
</html>
