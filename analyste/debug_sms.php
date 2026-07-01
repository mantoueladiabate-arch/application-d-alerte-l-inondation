<?php
require_once __DIR__ . '/../config.php';

$pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ── VÉRIFICATION DU SOLDE SMS ────────────────────────────────────────────
$soldeResult = null;
{
    $payload = json_encode(['clientid' => SMS_CLIENT_ID, 'clientsecret' => SMS_CLIENT_SECRET]);
    $ch = curl_init('https://www.hsms.ci/api/check-sms/');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . SMS_TOKEN,
            'Content-Type: application/json'
        ]
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    $soldeResult = ['http' => $code, 'curl_error' => $err, 'data' => json_decode($resp, true), 'raw' => $resp];
}

// Récupérer les contacts
$contacts = $pdo->query("SELECT id, nom, prenom, contact1 FROM contacts WHERE contact1 IS NOT NULL AND contact1 <> ''")->fetchAll(PDO::FETCH_ASSOC);

// ── TEST TRAITEMENT INCIDENT ────────────────────────────────────────────
$traitResult = null;
if (isset($_POST['traiter_id'])) {
    $incidentId = (int)$_POST['traiter_id'];

    // Récupérer l'incident
    $s = $pdo->prepare("SELECT i.*, c.commune AS commune_nom FROM informations i LEFT JOIN communes_abidjan c ON c.id = i.commune WHERE i.id = :id");
    $s->execute([':id' => $incidentId]);
    $inc = $s->fetch(PDO::FETCH_ASSOC);

    $zone = ($inc['quartier'] ? $inc['quartier'] . ', ' : '') . ($inc['commune_nom'] ?? $inc['commune']);
    $prep = preg_match('/^[AEIOUÀÂÉÈÊËÎÏÔÙÛÜaeiouyàâéèêëîïôùûü]/u', $zone) ? "d'" : 'de ';
    $introMap = [
        'Inondation'   => "Forte pluie et risque d'inondation dans la zone {$prep}{$zone}.",
        'Erosion'      => "Risque d'érosion signalé dans la zone {$prep}{$zone}.",
        'Eboulement'   => "Risque d'éboulement signalé dans la zone {$prep}{$zone}.",
        'Effondrement' => "Risque d'effondrement signalé dans la zone {$prep}{$zone}.",
    ];
    $intro = $introMap[$inc['risques']] ?? "Incident dans la zone {$prep}{$zone}.";
    $smsMessage = html_entity_decode(
        "ALERTE INONDATION\n{$intro}\nÉvite les routes inondées. Mets-toi en sécurité.\nÉcoute les consignes des autorités.",
        ENT_QUOTES | ENT_HTML5, 'UTF-8'
    );

    $traitResult = ['incident' => $inc, 'message' => $smsMessage, 'envois' => []];

    foreach ($contacts as $c) {
        $tel = preg_replace('/\s+/', '', $c['contact1']);
        if (!str_starts_with($tel, '+')) $tel = '+225' . $tel;

        $payload = json_encode(['clientid' => SMS_CLIENT_ID, 'clientsecret' => SMS_CLIENT_SECRET, 'telephone' => $tel, 'message' => $smsMessage]);
        $ch = curl_init('https://www.hsms.ci/api/envoi-sms/');
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . SMS_TOKEN, 'Content-Type: application/json']]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        $traitResult['envois'][] = ['tel' => $tel, 'http' => $code, 'erreur' => $err, 'reponse' => json_decode($resp, true)];
    }
}

// Récupérer les incidents
$incidents = $pdo->query("SELECT id, risques, quartier, commune, new_statut FROM informations ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telephone = $_POST['telephone'];
    $message   = $_POST['message'];

    // Formater le numéro
    $tel = preg_replace('/\s+/', '', $telephone);
    if (!str_starts_with($tel, '+')) {
        if (str_starts_with($tel, '00225'))      $tel = '+' . substr($tel, 2);
        elseif (str_starts_with($tel, '225'))    $tel = '+' . $tel;
        else                                      $tel = '+225' . $tel;
    }

    $payload = json_encode([
        'clientid'     => SMS_CLIENT_ID,
        'clientsecret' => SMS_CLIENT_SECRET,
        'telephone'    => $tel,
        'message'      => $message
    ]);

    $ch = curl_init('https://www.hsms.ci/api/envoi-sms/');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . SMS_TOKEN,
            'Content-Type: application/json'
        ]
    ]);
    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    $result = [
        'tel'        => $tel,
        'http'       => $httpCode,
        'curl_error' => $curlError,
        'payload'    => $payload,
        'reponse'    => $response,
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Debug SMS</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #0f0; padding: 20px; }
        h2 { color: #ff0; }
        h3 { color: #0ff; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #444; padding: 6px 10px; text-align: left; }
        th { background: #333; color: #ff0; }
        input, textarea, select { background: #222; color: #0f0; border: 1px solid #444; padding: 6px; width: 100%; margin-bottom: 8px; font-family: monospace; }
        button { background: #0a0; color: #fff; border: none; padding: 10px 20px; cursor: pointer; font-size: 14px; }
        .ok  { color: #0f0; font-weight: bold; }
        .err { color: #f55; font-weight: bold; }
        pre  { background: #111; padding: 10px; border: 1px solid #333; white-space: pre-wrap; word-break: break-all; }
        .section { background: #1e1e1e; border: 1px solid #333; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
    </style>
</head>
<body>

<h2>🔍 DEBUG SMS</h2>

<!-- SOLDE SMS -->
<div class="section">
    <h3>SOLDE SMS</h3>
    <?php if ($soldeResult['curl_error']): ?>
        <p class="err">Erreur cURL : <?= htmlspecialchars($soldeResult['curl_error']) ?></p>
    <?php elseif ($soldeResult['http'] !== 200): ?>
        <p class="err">HTTP <?= $soldeResult['http'] ?> — <?= htmlspecialchars($soldeResult['raw']) ?></p>
    <?php else: $d = $soldeResult['data']; ?>
        <p>Application : <strong><?= htmlspecialchars($d['Application'] ?? '—') ?></strong></p>
        <p>SMS disponibles : <strong class="<?= ($d['SMS disponibles'] ?? 0) > 0 ? 'ok' : 'err' ?>"><?= htmlspecialchars($d['SMS disponibles'] ?? '0') ?></strong></p>
        <?php if (!empty($d['wallet_balance'])): ?>
        <p>Portefeuille : <strong><?= htmlspecialchars($d['wallet_balance']) ?> <?= htmlspecialchars($d['wallet_currency'] ?? '') ?></strong></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- RÉSULTAT TRAITEMENT -->
<?php if ($traitResult): ?>
<div class="section">
    <h3>RÉSULTAT TRAITEMENT INCIDENT #<?= $traitResult['incident']['id'] ?></h3>
    <p>Message envoyé :</p>
    <pre><?= htmlspecialchars($traitResult['message']) ?></pre>
    <?php foreach ($traitResult['envois'] as $e): ?>
    <p>→ <strong><?= htmlspecialchars($e['tel']) ?></strong>
       HTTP <strong class="<?= $e['http']==200?'ok':'err' ?>"><?= $e['http'] ?></strong>
       <?php if ($e['erreur']): ?><span class="err">Erreur: <?= htmlspecialchars($e['erreur']) ?></span><?php endif; ?>
       <?php if (!empty($e['reponse']['success'])): ?><span class="ok">✓ Envoyé</span><?php else: ?><span class="err">✗ <?= htmlspecialchars(json_encode($e['reponse'])) ?></span><?php endif; ?>
    </p>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- RÉSULTAT -->
<?php if ($result): ?>
<div class="section">
    <h3>RÉSULTAT ENVOI</h3>
    <p>Numéro formaté : <strong><?= htmlspecialchars($result['tel']) ?></strong></p>
    <p>HTTP Code : <strong class="<?= $result['http'] == 200 ? 'ok' : 'err' ?>"><?= $result['http'] ?></strong></p>
    <?php if ($result['curl_error']): ?>
        <p class="err">Erreur cURL : <?= htmlspecialchars($result['curl_error']) ?></p>
    <?php endif; ?>
    <p>Payload envoyé :</p>
    <pre><?= htmlspecialchars(json_encode(json_decode($result['payload']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
    <p>Réponse API :</p>
    <pre><?= htmlspecialchars(json_encode(json_decode($result['reponse']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
</div>
<?php endif; ?>

<!-- CONTACTS EN BASE -->
<div class="section">
    <h3>CONTACTS EN BASE (<?= count($contacts) ?>)</h3>
    <?php if (empty($contacts)): ?>
        <p class="err">⚠ Aucun contact trouvé !</p>
    <?php else: ?>
    <table>
        <tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Téléphone</th></tr>
        <?php foreach ($contacts as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['nom']) ?></td>
            <td><?= htmlspecialchars($c['prenom']) ?></td>
            <td><?= htmlspecialchars($c['contact1']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>

<!-- INCIDENTS RÉCENTS -->
<div class="section">
    <h3>INCIDENTS RÉCENTS — Cliquer pour simuler le SMS</h3>
    <table>
        <tr><th>ID</th><th>Type</th><th>Quartier</th><th>Commune</th><th>Statut</th><th>Action</th></tr>
        <?php foreach ($incidents as $i): ?>
        <tr>
            <td><?= $i['id'] ?></td>
            <td><?= htmlspecialchars($i['risques']) ?></td>
            <td><?= htmlspecialchars($i['quartier']) ?></td>
            <td><?= htmlspecialchars($i['commune']) ?></td>
            <td><?= htmlspecialchars($i['new_statut']) ?></td>
            <td>
                <form method="POST" style="margin:0">
                    <input type="hidden" name="traiter_id" value="<?= $i['id'] ?>">
                    <button type="submit" style="background:#a00;color:#fff;border:none;padding:3px 8px;cursor:pointer">▶ Envoyer SMS</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- FORMULAIRE TEST -->
<div class="section">
    <h3>ENVOYER UN SMS TEST</h3>
    <form method="POST">
        <label>Numéro de téléphone :</label>
        <input type="text" name="telephone" value="<?= htmlspecialchars($contacts[0]['contact1'] ?? '') ?>" required>

        <label>Message :</label>
        <textarea name="message" rows="6">ALERTE INONDATION
Forte pluie et risque d'inondation dans la zone d'Allabra.
Évite les routes inondées. Mets-toi en sécurité.
Écoute les consignes des autorités.</textarea>

        <button type="submit">▶ ENVOYER SMS</button>
    </form>
</div>

<!-- CONFIG -->
<div class="section">
    <h3>CONFIG SMS</h3>
    <p>CLIENT_ID : <code><?= defined('SMS_CLIENT_ID') ? substr(SMS_CLIENT_ID, 0, 8) . '...' : '<span class="err">NON DÉFINI</span>' ?></code></p>
    <p>TOKEN : <code><?= defined('SMS_TOKEN') ? substr(SMS_TOKEN, 0, 8) . '...' : '<span class="err">NON DÉFINI</span>' ?></code></p>
</div>

</body>
</html>
