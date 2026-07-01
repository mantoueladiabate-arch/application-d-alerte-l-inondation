<?php
require_once __DIR__ . '/../config.php';

$telephone = '2250151516084'; // sans le +

$payload = json_encode([
    'clientid'     => SMS_CLIENT_ID,
    'clientsecret' => SMS_CLIENT_SECRET,
    'telephone'    => $telephone,
    'message'      => 'Test alerte inondation - ' . date('H:i:s')
]);

// Étape 1 : trouver l'URL finale après redirection
$ch = curl_init('https://www.hsms.ci/api/envoi-sms');
curl_setopt_array($ch, [
    CURLOPT_NOBODY         => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT        => 10,
]);
curl_exec($ch);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

echo "<pre>URL finale après redirect : $finalUrl\n\n";

// Étape 2 : envoyer le POST directement à l'URL finale
$ch2 = curl_init($finalUrl);
curl_setopt_array($ch2, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . SMS_TOKEN,
        'Content-Type: application/json'
    ]
]);
$response  = curl_exec($ch2);
$httpCode  = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch2);
curl_close($ch2);

echo "HTTP Code : $httpCode\n";
if ($curlError) echo "Erreur cURL : $curlError\n";
echo "Réponse API :\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";
