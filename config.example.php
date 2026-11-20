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

// Clé partagée attendue dans l'en-tête X-Api-Key pour les appels de l'appli mobile
// (contact/api_alerte.php, contact/api_communes.php). À régénérer avant la mise en prod.
define('MOBILE_API_KEY', 'VOTRE_CLE_API_MOBILE');
