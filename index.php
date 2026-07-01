<?php
session_start();
require_once __DIR__ . '/config.php';

try {
    $conn = new PDO(DB_DSN, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $ex) {
    $conn = null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Alerte Inondation</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">

  <style>
    /* Override AdminLTE content-wrapper background for the hero */
    .content-wrapper {
      background: transparent !important;
      padding: 0 !important;
      min-height: 100vh;
    }

    .home-page {
      position: relative;
      width: 100%;
      height: 100vh;
      background-image: url('images/hotel ivoire octobre 2024.jpg');
      background-size: cover;
      background-position: center;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: #fff;
      text-align: center;
    }

    .overlay-text {
      background-color: rgba(0, 0, 0, 0.5);
      padding: 20px 30px;
      border-radius: 10px;
      position: absolute;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      white-space: nowrap;
    }

    .overlay-text h1 { font-size: 2.2em; margin-bottom: 8px; }
    .overlay-text p  { font-size: 1.1em; margin: 0; }

    .login-container {
      position: absolute;
      top: 15px;
      right: 20px;
    }

    .login-button {
      padding: 10px 18px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      font-size: 14px;
    }
    .login-button:hover { background-color: #0056b3; color: #fff; text-decoration: none; }

    /* Custom login modal (NOT Bootstrap modal — keep z-index above AdminLTE) */
    .accueil-modal {
      display: none;
      position: fixed;
      z-index: 9000;
      left: 0; top: 0;
      width: 100%; height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5);
    }

    .accueil-modal-content {
      background-color: #fefefe;
      margin: 8% auto;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.3);
      width: 90%;
      max-width: 400px;
    }

    .close-button {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    .close-button:hover { color: #333; }

    .accueil-modal .form-group { margin-bottom: 15px; }
    .accueil-modal .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .accueil-modal .form-group input {
      width: 100%; padding: 10px;
      border: 1px solid #ccc; border-radius: 4px;
      box-sizing: border-box;
    }
    .accueil-modal .form-actions button {
      background-color: #007bff; color: white;
      padding: 10px 15px; border: none; border-radius: 5px;
      cursor: pointer; width: 100%;
    }
    .accueil-modal .form-actions a {
      display: block; margin-top: 10px;
      color: #007bff; text-decoration: none;
    }

    /* FAQ */
    .faq-section { padding: 40px 50px; background-color: #f4f4f4; }
    .faq-section h2 { margin-bottom: 25px; color: #333; }

    .faq-item { margin-bottom: 10px; }

    .faq-toggle {
      display: flex; justify-content: space-between; align-items: center;
      cursor: pointer; padding: 15px;
      background-color: #e9e9e9; border-radius: 5px;
      transition: background-color 0.3s ease;
    }
    .faq-toggle:hover { background-color: #ddd; }
    .faq-toggle h3 { margin: 0; font-size: 1.1em; color: #333; }
    .faq-toggle span { transition: transform 0.3s ease; }
    .faq-toggle.active span { transform: rotate(90deg); }

    .faq-answer {
      max-height: 0; overflow: hidden;
      transition: max-height 0.3s ease, padding 0.3s ease;
      padding: 0 15px;
      background-color: #f9f9f9;
      border-bottom-left-radius: 5px;
      border-bottom-right-radius: 5px;
    }
    .faq-answer.show { max-height: 200px; padding: 15px; }
    .faq-answer p { color: #555; line-height: 1.6; margin: 0; }
  </style>
</head>

<body class="skin-blue sidebar-mini">
<div class="wrapper">

  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <div class="content-wrapper">

    <!-- Hero section -->
    <div class="home-page">
      <div class="login-container">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
          <a href="logout.php" class="login-button">Se déconnecter</a>
        <?php else: ?>
          <a href="#" class="login-button" onclick="document.getElementById('login-modal').style.display='block'">Se connecter</a>
        <?php endif; ?>
      </div>
      <div class="overlay-text">
        <h1>Alertes Inondation</h1>
        <p>Protection active contre les inondations, votre sécurité avant tout.</p>
      </div>
    </div>

    <!-- FAQ -->
    <div class="faq-section">
      <h2>Foire aux questions (FAQ)</h2>

      <div class="faq-item">
        <div class="faq-toggle" onclick="toggleAnswer(this)">
          <h3>Qu'est-ce qu'une application "Alerte Inondation" ?</h3><span>▶</span>
        </div>
        <div class="faq-answer">
          <p>C'est une application web cartographique conçue pour renforcer la sécurité des citoyens et accompagner les autorités dans la gestion des inondations.</p>
        </div>
      </div>

      <div class="faq-item">
        <div class="faq-toggle" onclick="toggleAnswer(this)">
          <h3>Comment l'application fonctionne-t-elle ?</h3><span>▶</span>
        </div>
        <div class="faq-answer">
          <p>Grâce à des données recueillies en temps réel, une alerte est envoyée aux citoyens par e-mail et SMS pour les informer de la manifestation des inondations.</p>
        </div>
      </div>

      <div class="faq-item">
        <div class="faq-toggle" onclick="toggleAnswer(this)">
          <h3>Où trouver les alertes ?</h3><span>▶</span>
        </div>
        <div class="faq-answer">
          <p>Les alertes sont diffusées via les médias locaux, les réseaux sociaux, les systèmes d'alerte communautaires et notre site web. Vérifiez régulièrement les mises à jour.</p>
        </div>
      </div>

      <div class="faq-item">
        <div class="faq-toggle" onclick="toggleAnswer(this)">
          <h3>Comment se protéger des inondations ?</h3><span>▶</span>
        </div>
        <div class="faq-answer">
          <p>Préparez un kit d'urgence, connaissez vos voies d'évacuation et suivez les instructions des autorités locales. Éloignez-vous des zones inondées.</p>
        </div>
      </div>

      <div class="faq-item">
        <div class="faq-toggle" onclick="toggleAnswer(this)">
          <h3>Quels sont les zones d'inondations ?</h3><span>▶</span>
        </div>
        <div class="faq-answer">
          <p>Les zones à risque sont celles situées dans les vallées et les cuvettes.</p>
        </div>
      </div>

      <div class="faq-item">
        <div class="faq-toggle" onclick="toggleAnswer(this)">
          <h3>Que faire en cas d'inondations ?</h3><span>▶</span>
        </div>
        <div class="faq-answer">
          <p>Montez aux étages supérieurs. Coupez l'électricité si possible. Évacuez immédiatement et contactez les secours.</p>
        </div>
      </div>
    </div>

  </div><!-- /.content-wrapper -->

</div><!-- /.wrapper -->

<!-- Login modal (outside wrapper, high z-index) -->
<div id="login-modal" class="accueil-modal">
  <div class="accueil-modal-content">
    <span class="close-button" onclick="document.getElementById('login-modal').style.display='none'">&times;</span>
    <h2>Connexion</h2>

    <div id="login-form">
      <form action="process_login.php" method="post">
        <div class="form-group">
          <label>Nom d'utilisateur :</label>
          <input type="text" name="username" required>
        </div>
        <div class="form-group">
          <label>Mot de passe :</label>
          <input type="password" name="password" required>
        </div>
        <div class="form-actions">
          <button type="submit">Se connecter</button>
          <a href="#" onclick="showForgotPasswordForm()">Mot de passe oublié ?</a>
        </div>
      </form>
    </div>

    <div id="forgot-password-form" style="display:none;">
      <h2>Mot de passe oublié</h2>
      <form action="process_forgot_password.php" method="post">
        <div class="form-group">
          <label>Nom d'utilisateur :</label>
          <input type="text" name="reset_username" required>
        </div>
        <div class="form-actions">
          <button type="submit">Réinitialiser le mot de passe</button>
          <a href="#" onclick="showLoginForm()">Retour à la connexion</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="dist/js/app.min.js"></script>
<script>
  function showLoginForm() {
    document.getElementById('login-form').style.display = 'block';
    document.getElementById('forgot-password-form').style.display = 'none';
  }
  function showForgotPasswordForm() {
    document.getElementById('login-form').style.display = 'none';
    document.getElementById('forgot-password-form').style.display = 'block';
  }
  window.onclick = function(e) {
    var m = document.getElementById('login-modal');
    if (e.target === m) m.style.display = 'none';
  };
  function toggleAnswer(el) {
    var item   = el.closest('.faq-item');
    var answer = item.querySelector('.faq-answer');
    answer.classList.toggle('show');
    el.classList.toggle('active');
  }
</script>
</body>
</html>
