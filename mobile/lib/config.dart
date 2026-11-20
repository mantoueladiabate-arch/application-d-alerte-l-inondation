// Une seule constante à changer une fois le nom de domaine réel acheté et le
// site déployé. Exemple en production : 'https://ton-domaine.ci/contact'
// (ou 'https://ton-domaine.ci/application-d-alerte-l-inondation/contact' si
// le site reste dans un sous-dossier). Rien d'autre à reconfigurer dans l'appli.
const String apiBaseUrl = 'http://localhost:8888/application-d-alerte-l-inondation/contact';

// Doit correspondre à MOBILE_API_KEY dans config.php côté serveur.
const String mobileApiKey = 'd145eb36cd83db8b33748bd1341e2a303f5ffe140a94482e';
