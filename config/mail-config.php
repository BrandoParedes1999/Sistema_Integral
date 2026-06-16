<?php
// ── Configuración de correo ───────────────────────────────────────────────
// Cambia MAIL_MODE a 'prod' cuando despliegues en producción
define('MAIL_MODE', 'prod');   // 'dev' = muestra código en pantalla | 'prod' = envía email

define('MAIL_HOST',     'mail.unisalud.com.mx');
define('MAIL_PORT',     465);
define('MAIL_USER',     '_mainaccount@unisalud.com.mx');
define('MAIL_PASS',     'TU_CONTRASEÑA_CPANEL');   // <-- pon aquí tu contraseña de cPanel
define('MAIL_FROM',     '_mainaccount@unisalud.com.mx');
define('MAIL_FROM_NAME','UniSalud UNACAR');
