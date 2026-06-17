<?php
// ── Configuración de correo ───────────────────────────────────────────────
// Cambia MAIL_MODE a 'prod' cuando despliegues en producción
define('MAIL_MODE', 'prod');   // 'dev' = muestra código en pantalla | 'prod' = envía email

define('MAIL_HOST',     'mail.unisalud.com.mx');
define('MAIL_PORT',     465);
define('MAIL_USER',     'noreply@unisalud.com.mx');
define('MAIL_PASS',     'pa$$w0rd210426#');
define('MAIL_FROM',     'noreply@unisalud.com.mx');
define('MAIL_FROM_NAME','UniSalud UNACAR');
