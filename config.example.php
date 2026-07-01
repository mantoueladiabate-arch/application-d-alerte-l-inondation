<?php
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'base_inondation');
define('DB_USER', 'postgres');
define('DB_PASS', 'VOTRE_MOT_DE_PASSE_POSTGRES');
define('DB_DSN',  'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME);

// HSMS.CI - API SMS
define('SMS_TOKEN',         'VOTRE_TOKEN_HSMS');
define('SMS_CLIENT_ID',     'VOTRE_CLIENT_ID');
define('SMS_CLIENT_SECRET', 'VOTRE_CLIENT_SECRET');
