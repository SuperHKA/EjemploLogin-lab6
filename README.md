# Laboratorio Login con 2FA

## Descripción

Este proyecto corresponde al laboratorio de autenticación desarrollado en PHP. Implementa un sistema básico de login con registro de usuarios, manejo de sesiones, protección CSRF, sanitización de datos, hash de contraseñas y autenticación de dos factores mediante Google Authenticator.

El sistema permite registrar usuarios, iniciar sesión con credenciales válidas, configurar un segundo factor de autenticación mediante código QR y validar códigos temporales TOTP antes de permitir el acceso al dashboard protegido.

## Tecnologías utilizadas

* PHP
* MySQL
* PDO
* Composer
* Sonata Google Authenticator
* HTML
* CSS
* WAMPServer

## Estructura del proyecto

```text
EjemploLogin-lab6/
├── assets/
│   └── auth.css                  # Estilos visuales del login, registro, 2FA y dashboard
│
├── clases/
│   ├── Csrf.php                  # Generación y validación de tokens CSRF
│   ├── SanitizarEntrada.php      # Limpieza y escape de datos de entrada/salida
│   ├── TwoFactorAuth.php         # Generación/verificación de códigos 2FA y QR
│   ├── mysql.inc.php             # Conexión PDO y operaciones con MySQL
│   ├── objLoginAdmin.php         # Validación de credenciales e intentos de login
│   └── objRegistroUsuario.php    # Lógica de registro de nuevos usuarios
│
├── vendor/                       # Dependencias instaladas con Composer
│   ├── autoload.php
│   ├── composer/
│   └── sonata-project/
│
├── composer.json                 # Dependencia principal: google-authenticator
├── composer.lock                 # Versiones exactas instaladas por Composer
│
├── dashboard.php                 # Panel principal luego de autenticación exitosa
├── index.php                     # Procesa el formulario de login
├── login.php                     # Vista del formulario de inicio de sesión
├── registro.php                  # Vista y procesamiento del registro de usuarios
├── salir.php                     # Cierre de sesión
├── setup_2fa.php                 # Configuración inicial de autenticación 2FA
└── validar_2fa.php               # Validación del código 2FA
```

## Funcionalidades principales

* Registro de usuarios.
* Inicio de sesión con usuario y contraseña.
* Validación de campos requeridos.
* Validación de correo electrónico.
* Validación de usuario y correo duplicados.
* Hash de contraseñas con `password_hash()`.
* Verificación de contraseñas con `password_verify()`.
* Protección contra ataques CSRF mediante tokens.
* Sanitización de entradas.
* Escape seguro de salidas HTML.
* Registro de intentos de inicio de sesión.
* Generación de secreto 2FA por usuario.
* Generación de código QR para Google Authenticator.
* Validación de códigos temporales TOTP.
* Sesión adicional para confirmar autenticación 2FA.
* Dashboard protegido.
* Cierre de sesión seguro.

## Dependencias

El proyecto utiliza Composer para administrar dependencias PHP.

Para instalar las dependencias, ejecutar desde la raíz del proyecto:

```bash
composer install
```

La dependencia principal utilizada para la autenticación de dos factores es:

```bash
sonata-project/google-authenticator
```

En caso de no tener la dependencia instalada, se puede agregar con:

```bash
composer require sonata-project/google-authenticator
```

## Configuración del entorno

El proyecto fue desarrollado para ejecutarse en WAMPServer.

Ruta local del proyecto:

```text
C:\wamp64\www\Laboratorios\EjemploLogin-lab6
```

URL local esperada:

```text
http://localhost/Laboratorios/EjemploLogin-lab6/login.php
```

## Configuración de la base de datos

La base de datos utilizada se llama:

```text
company_info
```

El archivo de conexión se encuentra en:

```text
clases/mysql.inc.php
```

El sistema utiliza PDO para conectarse a MySQL.

Se configuró un usuario de base de datos con privilegios mínimos, evitando el uso del usuario `root`.

Usuario configurado:

```text
login_user
```

Este usuario debe tener permisos únicamente sobre la base de datos `company_info`.

Permisos recomendados:

```sql
SELECT, INSERT, UPDATE, DELETE
```

Para verificar los privilegios concedidos:

```sql
SHOW GRANTS FOR 'login_user'@'localhost';
```

## Tablas utilizadas

El laboratorio utiliza las siguientes tablas principales:

```text
usuarios
intentos_login
trazabilidad_acciones
```

### Tabla `usuarios`

Almacena los datos principales de cada usuario registrado, incluyendo:

* Nombre
* Apellido
* Usuario
* Correo
* HashMagic
* Sexo
* secret_2fa
* FechaSistema

El campo `secret_2fa` almacena el secreto generado para Google Authenticator.

### Tabla `intentos_login`

Registra los intentos de inicio de sesión, tanto exitosos como fallidos.

Puede almacenar información como:

* Usuario
* Estado del intento
* Dirección IP
* Fecha y hora del intento

### Tabla `trazabilidad_acciones`

Registra acciones importantes realizadas dentro del sistema, como registros o modificaciones relevantes.

## Flujo de autenticación

El flujo general del sistema es el siguiente:

1. El usuario accede a `login.php`.
2. Ingresa usuario y contraseña.
3. El formulario se procesa en `index.php`.
4. Si las credenciales son incorrectas, se registra el intento fallido.
5. Si las credenciales son correctas, se valida si el usuario tiene 2FA configurado.
6. Si el usuario no tiene 2FA configurado, se redirige a `setup_2fa.php`.
7. En `setup_2fa.php`, el sistema genera un secreto y un código QR.
8. El usuario escanea el QR con Google Authenticator.
9. Luego introduce el código temporal generado por la aplicación.
10. El código se valida en `validar_2fa.php`.
11. Si el código es correcto, se crea la sesión de 2FA verificada.
12. El usuario accede a `dashboard.php`.

## Archivos principales

### `login.php`

Contiene la vista del formulario de inicio de sesión.

### `index.php`

Procesa el formulario de login. Valida las credenciales del usuario y registra los intentos de inicio de sesión.

### `registro.php`

Contiene la vista y procesamiento del formulario de registro de usuarios. Valida los datos, aplica sanitización, genera el hash de contraseña y guarda el usuario en la base de datos.

### `setup_2fa.php`

Genera el secreto 2FA para el usuario, crea la URL compatible con Google Authenticator y muestra el código QR para escanearlo.

### `validar_2fa.php`

Permite al usuario introducir el código temporal generado por Google Authenticator. Si el código es válido, se concede acceso al sistema.

### `dashboard.php`

Panel principal protegido. Solo puede accederse si el usuario inició sesión correctamente y completó la verificación 2FA.

### `salir.php`

Cierra la sesión activa y redirige al usuario al login.

## Clases principales

### `Csrf.php`

Clase encargada de generar y validar tokens CSRF para proteger formularios contra solicitudes no autorizadas.

### `SanitizarEntrada.php`

Clase con métodos estáticos para limpiar, validar y escapar datos de entrada y salida.

Ejemplos de responsabilidades:

* Limpiar texto.
* Sanitizar correos.
* Validar valores permitidos.
* Escapar contenido HTML.

### `TwoFactorAuth.php`

Clase encargada de manejar la autenticación de dos factores.

Responsabilidades principales:

* Generar secreto 2FA.
* Generar URL de QR.
* Validar códigos TOTP.
* Integrarse con Google Authenticator.

### `mysql.inc.php`

Archivo encargado de la conexión a la base de datos mediante PDO y operaciones relacionadas con MySQL.

### `objLoginAdmin.php`

Clase encargada de validar credenciales de usuario, verificar contraseñas y registrar intentos de login.

### `objRegistroUsuario.php`

Clase encargada de manejar la lógica del registro de nuevos usuarios.

Responsabilidades principales:

* Validar datos.
* Verificar usuario o correo duplicado.
* Generar hash de contraseña.
* Insertar usuario en la base de datos.
* Registrar acciones de trazabilidad.

## Seguridad implementada

El sistema aplica varias medidas básicas de seguridad:

### Uso de usuario MySQL sin privilegios de superusuario

La conexión no utiliza `root`. Se creó un usuario específico para la aplicación con privilegios mínimos sobre la base de datos.

### Hash de contraseñas

Las contraseñas no se almacenan en texto plano. Se utiliza:

```php
password_hash()
```

Para verificar contraseñas se utiliza:

```php
password_verify()
```

### Protección CSRF

Los formularios utilizan tokens Anti-CSRF para prevenir solicitudes maliciosas.

### Sanitización de entradas

Los datos ingresados por el usuario son limpiados antes de procesarse o almacenarse.

### Escape de salida HTML

Los datos mostrados en pantalla se escapan para reducir el riesgo de ataques XSS.

### Autenticación 2FA

El sistema utiliza códigos temporales TOTP generados por Google Authenticator como segundo factor de autenticación.

### Manejo de sesiones

Se utilizan sesiones para controlar el acceso del usuario. Además, se crea una segunda variable de sesión para confirmar que la validación 2FA fue exitosa.

Ejemplo:

```php
$_SESSION['2fa_verificado'] = true;
```

### Regeneración de sesión

Después de una autenticación exitosa se puede utilizar:

```php
session_regenerate_id(true);
```

Esto ayuda a reducir riesgos de fijación de sesión.

## Flujo de cierre de sesión

El archivo `salir.php` destruye la sesión activa y redirige al usuario al formulario de login.

## Evidencias recomendadas para la entrega

Para documentar correctamente el laboratorio, se recomienda incluir capturas de:

1. Repositorio del proyecto.
2. Base de datos `company_info`.
3. Tablas creadas.
4. Usuario MySQL sin root.
5. Resultado de `SHOW GRANTS`.
6. Formulario de registro.
7. Validación de usuario o correo duplicado.
8. Login funcionando.
9. Código QR generado.
10. Cuenta agregada en Google Authenticator.
11. Pantalla de validación 2FA.
12. Acceso exitoso al dashboard.
13. Registros en `intentos_login`.
14. Registros en `usuarios`.
15. Cierre de sesión.

## Instalación rápida

1. Clonar o copiar el proyecto dentro de:

```text
C:\wamp64\www\Laboratorios\EjemploLogin-lab6
```

2. Crear la base de datos:

```sql
CREATE DATABASE company_info;
```

3. Crear las tablas necesarias.

4. Crear un usuario MySQL con privilegios mínimos.

5. Configurar la conexión en:

```text
clases/mysql.inc.php
```

6. Instalar dependencias:

```bash
composer install
```

7. Abrir el proyecto en el navegador:

```text
http://localhost/Laboratorios/EjemploLogin-lab6/login.php
```

## Estado del laboratorio

El proyecto cumple con los puntos principales solicitados:

* Registro de usuarios.
* Validación de datos.
* Sanitización.
* Hash de contraseñas.
* Login con sesiones.
* Registro de intentos.
* Autenticación 2FA.
* Código QR.
* Validación de código temporal.
* Usuario MySQL sin privilegios de superusuario.
* Protección CSRF.
* Dashboard protegido.

## Conclusión

Durante el desarrollo de este laboratorio se implementó un sistema de autenticación completo utilizando PHP y MySQL, aplicando buenas prácticas de seguridad tanto en el manejo de usuarios como en la protección de la información. Se logró integrar exitosamente el registro de usuarios, el inicio de sesión seguro mediante contraseñas cifradas y la autenticación de dos factores (2FA) utilizando Google Authenticator.

Además, se reforzó la seguridad de la aplicación mediante la implementación de tokens CSRF, sanitización de entradas, escape de salidas HTML, manejo adecuado de sesiones y el uso de un usuario de base de datos con privilegios mínimos. Estas medidas ayudan a reducir riesgos asociados a ataques comunes como XSS, CSRF, robo de sesiones y acceso no autorizado.

En conclusión, este laboratorio permitió comprender la importancia de implementar múltiples capas de seguridad en aplicaciones web modernas, demostrando cómo la combinación de autenticación tradicional y autenticación de dos factores incrementa significativamente la protección de las cuentas de usuario y la integridad del sistema.
