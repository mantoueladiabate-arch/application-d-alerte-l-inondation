<?php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'base_inondation');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'postgres');
define('DB_DSN',  'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME);

// HSMS.CI - API SMS
define('SMS_TOKEN',         getenv('SMS_TOKEN') ?: '0eebac3b6594eb3c37b675f8ab0299629f5d96f9');
define('SMS_CLIENT_ID',     getenv('SMS_CLIENT_ID') ?: 'OVLSERVICES_GtC9346');
define('SMS_CLIENT_SECRET', getenv('SMS_CLIENT_SECRET') ?: 'OVLSERVICES20251024094434.7668209a8NIMFxWldCgbbmFPCZ');

// Clé partagée attendue dans l'en-tête X-Api-Key pour les appels de l'appli mobile
// (contact/api_alerte.php, contact/api_communes.php). À régénérer avant la mise en prod.
define('MOBILE_API_KEY', getenv('MOBILE_API_KEY') ?: 'd145eb36cd83db8b33748bd1341e2a303f5ffe140a94482e');
