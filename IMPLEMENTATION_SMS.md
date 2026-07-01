# Guide d'implémentation du SMS avec HSMS.CI

Ce guide explique comment intégrer l'envoi automatique de SMS dans une application PHP,
en utilisant l'API HSMS.CI. Il est écrit pour un débutant, étape par étape.

---

## C'est quoi le but ?

Quand un analyste marque une alerte d'inondation comme **"traitée"**, l'application doit
automatiquement envoyer un SMS à tous les contacts enregistrés pour les prévenir.

Pour faire ça, on utilise **HSMS.CI**, un service ivoirien d'envoi de SMS par API.
Une API, c'est simplement une adresse web à laquelle on envoie des données,
et qui fait une action (ici : envoyer un SMS) en retour.

---

## Étape 1 — Créer un compte HSMS.CI et récupérer ses credentials

**Ce qu'on fait :**
Aller sur le site de HSMS.CI et créer un compte professionnel.
Une fois le compte créé, le site vous donne 3 informations secrètes :

- **CLIENT ID** : c'est votre identifiant unique sur leur plateforme
- **CLIENT SECRET** : c'est comme un mot de passe supplémentaire
- **TOKEN** : c'est une clé d'accès que vous allez mettre dans chaque requête

**Pourquoi ces 3 informations ?**
C'est leur système de sécurité. Sans ces 3 valeurs, leur serveur refusera d'envoyer
un SMS pour vous. C'est comme une clé, un badge et un code PIN en même temps.

**Où les trouver ?**
Dans votre espace client sur le site hsms.ci, section "API" ou "Paramètres".

---

## Étape 2 — Stocker les credentials de façon sécurisée (config.php)

**Ce qu'on fait :**
On ne met jamais les informations secrètes directement dans le code PHP.
On les met dans un fichier séparé appelé `config.php`, qui est **exclu de Git**
(c'est-à-dire qu'il ne sera jamais envoyé sur GitHub).

```php
// config.php
define('SMS_TOKEN',         'votre_token_ici');
define('SMS_CLIENT_ID',     'votre_client_id_ici');
define('SMS_CLIENT_SECRET', 'votre_client_secret_ici');
```

`define()` crée une **constante** : une variable qui ne change jamais pendant
l'exécution du programme. On peut l'appeler n'importe où dans le code avec
`SMS_TOKEN`, `SMS_CLIENT_ID`, etc.

**Pourquoi un fichier séparé ?**
Si on met le mot de passe directement dans le code et qu'on l'envoie sur GitHub,
n'importe qui peut le lire. En le mettant dans `config.php` qui est dans `.gitignore`,
il reste uniquement sur votre machine.

**Ce qu'il faut faire :**
Copier `config.example.php` en `config.php` et remplacer les valeurs fictives
par vos vraies valeurs HSMS.CI.

---

## Étape 3 — Comprendre comment fonctionne l'API HSMS.CI

**Ce qu'on fait :**
Avant d'écrire le code, on comprend ce que l'API attend.

Pour envoyer un SMS, on doit envoyer une requête HTTP **POST** à cette adresse :
```
https://www.hsms.ci/api/envoi-sms/
```

> **Important :** Le slash `/` à la fin est obligatoire. Sans lui, le serveur
> redirige vers la bonne URL (code HTTP 301) et notre SMS n'est pas envoyé.

Le corps de la requête doit être en **JSON** (un format de données texte) :
```json
{
    "clientid":     "votre_client_id",
    "clientsecret": "votre_client_secret",
    "telephone":    "+2250707070707",
    "message":      "Votre texte de SMS ici"
}
```

On doit aussi ajouter dans l'en-tête de la requête :
```
Authorization: Bearer votre_token
Content-Type: application/json
```

Si tout est correct, le serveur répond :
```json
{"success": true, "message": "Message(s) envoyé(s)"}
```

---

## Étape 4 — Formater correctement le numéro de téléphone

**Ce qu'on fait :**
Les numéros en Côte d'Ivoire peuvent être enregistrés de plusieurs façons :
- `0707070707` (format local avec 0 devant)
- `225707070707` (avec l'indicatif pays, sans +)
- `00225707070707` (avec 00 devant l'indicatif)
- `+225707070707` (format international standard)

L'API HSMS.CI attend le format international avec `+` : **`+2250707070707`**

> **Attention :** Pour les numéros commençant par `07`, `05`, `01`, etc.
> (avec un `0` en tête), il faut garder ce `0` :
> `0707070707` devient `+2250707070707` (pas `+225707070707`)

**Le code PHP qui fait cette conversion :**
```php
$telephone = preg_replace('/\s+/', '', $telephone); // supprime les espaces

if (!str_starts_with($telephone, '+')) {
    if (str_starts_with($telephone, '00225')) {
        $telephone = '+' . substr($telephone, 2); // 00225... → +225...
    } elseif (str_starts_with($telephone, '225')) {
        $telephone = '+' . $telephone;             // 225... → +225...
    } else {
        $telephone = '+225' . $telephone;          // 07... → +22507...
    }
}
```

`preg_replace('/\s+/', '', ...)` : supprime tous les espaces dans le numéro.
`str_starts_with()` : vérifie par quoi commence la chaîne de texte.
`substr($telephone, 2)` : prend le texte à partir du 3ème caractère (coupe les 2 premiers).

---

## Étape 5 — Écrire la fonction d'envoi de SMS (cURL)

**Ce qu'on fait :**
En PHP, pour faire une requête vers un serveur externe (comme l'API HSMS.CI),
on utilise **cURL** — c'est une bibliothèque intégrée à PHP qui permet de
communiquer avec des adresses web.

Voici la fonction complète à ajouter dans `analyste/ajax.php` et `tableaubord/ajax.php` :

```php
function sendSmsAlert($telephone, $message) {

    // 1. Formater le numéro
    $telephone = preg_replace('/\s+/', '', $telephone);
    if (!str_starts_with($telephone, '+')) {
        if (str_starts_with($telephone, '00225')) {
            $telephone = '+' . substr($telephone, 2);
        } elseif (str_starts_with($telephone, '225')) {
            $telephone = '+' . $telephone;
        } else {
            $telephone = '+225' . $telephone;
        }
    }

    // 2. Préparer les données à envoyer (format JSON)
    $payload = json_encode([
        'clientid'     => SMS_CLIENT_ID,
        'clientsecret' => SMS_CLIENT_SECRET,
        'telephone'    => $telephone,
        'message'      => $message
    ]);

    // 3. Initialiser cURL avec l'URL de l'API
    $ch = curl_init('https://www.hsms.ci/api/envoi-sms/');

    // 4. Configurer les options cURL
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,        // méthode POST
        CURLOPT_POSTFIELDS     => $payload,    // corps de la requête (notre JSON)
        CURLOPT_RETURNTRANSFER => true,        // retourner la réponse au lieu de l'afficher
        CURLOPT_TIMEOUT        => 10,          // abandonner après 10 secondes
        CURLOPT_SSL_VERIFYPEER => false,       // désactiver la vérification SSL (voir note)
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,        // suivre les redirections automatiquement
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . SMS_TOKEN,
            'Content-Type: application/json'
        ]
    ]);

    // 5. Exécuter la requête et fermer
    $response = curl_exec($ch);
    curl_close($ch);

    // 6. Retourner la réponse décodée
    return json_decode($response, true);
}
```

**Note sur `CURLOPT_SSL_VERIFYPEER => false` :**
Normalement, cURL vérifie que le certificat SSL du serveur est valide (sécurité HTTPS).
Sur WAMP en local, PHP n'a pas la liste des certificats de confiance installée,
donc cette vérification échoue avec l'erreur :
`SSL certificate problem: unable to get local issuer certificate`
En désactivant cette vérification, on contourne le problème.
> En production sur un vrai serveur (OVH, AWS, etc.), cette option n'est pas nécessaire.

---

## Étape 6 — Déclencher l'envoi au bon moment

**Ce qu'on fait :**
Dans `analyste/ajax.php` et `tableaubord/ajax.php`, il y a déjà un bloc qui
gère le changement de statut d'une alerte (`update_risk_status`).
On ajoute notre logique SMS à l'intérieur, uniquement quand le statut devient `traite`.

```php
// Dans le bloc update_risk_status, après avoir mis à jour le statut en BDD :

if ($new_statut === 'traite') {

    // Étape A : récupérer les détails de l'incident
    $stmtI = $pdo->prepare("SELECT * FROM informations WHERE id = :id");
    $stmtI->execute([':id' => $risk_id]);
    $incident = $stmtI->fetch(PDO::FETCH_ASSOC);

    // Étape B : trouver le nom de la commune (la BDD stocke l'ID, pas le nom)
    $commune_nom = $incident['commune']; // valeur par défaut = l'ID
    $stmtC = $pdo->prepare("SELECT nom_commune FROM communes_abidjan WHERE id_commune = :id");
    $stmtC->execute([':id' => $incident['commune']]);
    $row = $stmtC->fetch(PDO::FETCH_ASSOC);
    if ($row) $commune_nom = $row['nom_commune'];

    // Étape C : construire le texte du SMS
    $message = "ALERTE INONDATION - Incident traité\n"
             . "Type : "     . $incident['type_risque'] . "\n"
             . "Commune : "  . $commune_nom . "\n"
             . "Quartier : " . $incident['quartier'] . "\n"
             . "Date : "     . $incident['date'];

    // Étape D : récupérer tous les contacts et envoyer le SMS à chacun
    $stmtContacts = $pdo->query(
        "SELECT contact1 FROM contacts WHERE contact1 IS NOT NULL AND contact1 != ''"
    );
    $contacts = $stmtContacts->fetchAll(PDO::FETCH_ASSOC);

    foreach ($contacts as $contact) {
        sendSmsAlert($contact['contact1'], $message);
    }
}
```

**Pourquoi deux requêtes SQL séparées pour la commune ?**
La table `informations` stocke l'ID de la commune (ex: `4`), pas son nom.
Pour afficher "Cocody" dans le SMS au lieu de "4", on fait une deuxième requête
sur la table `communes_abidjan` pour chercher le nom correspondant à cet ID.
On évite les JOIN SQL car ils causaient des erreurs 500 dans ce contexte.

---

## Étape 7 — Tester avant de mettre en production

**Ce qu'on fait :**
Avant de tester depuis l'interface, on crée un script de test simple
`database/test_sms.php` pour vérifier que tout fonctionne :

```php
<?php
require_once __DIR__ . '/../config.php';

$payload = json_encode([
    'clientid'     => SMS_CLIENT_ID,
    'clientsecret' => SMS_CLIENT_SECRET,
    'telephone'    => '+22507XXXXXXXX', // remplacer par votre numéro
    'message'      => 'Test - ' . date('H:i:s')
]);

$ch = curl_init('https://www.hsms.ci/api/envoi-sms/');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . SMS_TOKEN,
        'Content-Type: application/json'
    ]
]);
$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "Code HTTP : $httpCode\n";
if ($curlError) echo "Erreur : $curlError\n";
echo "Réponse : " . json_encode(json_decode($response), JSON_PRETTY_PRINT);
```

Ouvrir dans le navigateur :
```
http://localhost/application-d-alerte-l-inondation/database/test_sms.php
```

**Réponse attendue si ça marche :**
```
Code HTTP : 200
Réponse : {
    "success": true,
    "message": "Message(s) envoyé(s)"
}
```

**Erreurs fréquentes et leurs causes :**

| Erreur | Cause | Solution |
|---|---|---|
| `HTTP 0` + SSL error | WAMP n'a pas de certificats CA | Ajouter `CURLOPT_SSL_VERIFYPEER => false` |
| `HTTP 301` réponse vide | URL sans slash final | Utiliser `.../api/envoi-sms/` avec le `/` |
| `HTTP 401` | Token invalide ou expiré | Vérifier la valeur de `SMS_TOKEN` dans config.php |
| SMS reçu avec mauvais numéro | Format `+225707...` au lieu de `+2250707...` | Vérifier la logique de formatage — garder le `0` |
| Aucun SMS envoyé | Table `contacts` vide | Ajouter des contacts via `/ajout_contact/index.php` |

---

## Résumé des fichiers modifiés

| Fichier | Ce qui a été ajouté |
|---|---|
| `config.php` | Constantes `SMS_TOKEN`, `SMS_CLIENT_ID`, `SMS_CLIENT_SECRET` |
| `analyste/ajax.php` | Fonction `sendSmsAlert()` + déclenchement dans `update_risk_status` |
| `tableaubord/ajax.php` | Idem |
| `database/test_sms.php` | Script de test (ne pas committer, fichier gitignore) |

---

## Schéma du flux complet

```
Analyste clique "Traiter"
        │
        ▼
ajax.php reçoit la requête POST
        │
        ▼
UPDATE informations SET new_statut = 'traite'
        │
        ▼
SELECT * FROM informations WHERE id = ?    ← récupère l'incident
        │
        ▼
SELECT nom_commune FROM communes_abidjan   ← résout l'ID → nom
        │
        ▼
Construit le texte du SMS
        │
        ▼
SELECT contact1 FROM contacts              ← liste tous les destinataires
        │
        ▼
Pour chaque contact → sendSmsAlert()
        │
        ▼
cURL POST → https://www.hsms.ci/api/envoi-sms/
        │
        ▼
SMS reçu par le contact 📱
```
