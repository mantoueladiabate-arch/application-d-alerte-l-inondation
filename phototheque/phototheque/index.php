
<?php
  include('config.php'); // connection à la page config
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>photothèque</title>
  <!--liens de leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
		<script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-groupedlayercontrol/0.6.1/leaflet.groupedlayercontrol.min.js"></script>
    
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="style.css">

   

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <style>
    

    body{
            
            height:100vh;
            display:flex;
            /*centrer la boite*/
            align-items: center;
            justify-content: center;
             /*mettre l'image en fond*/
            background-image:url('data/eau.jpeg');
             /*adapter l'image à notre ecran*/
            background-size:cover;
            text-align:justify;
             
              }
    
              .container {
            max-width:1000px;	/*largueur maximal de boite*/
            box-shadow: 0 0 15px /*(0,0,0,3) /*opatiter de la boite*/ ;
            align-items: center; /*centrage horizontal entre le texte et la carte*/
            font-size: 18px; /*taille*/  
            padding:10px 40px;/*espace entre le texte et la boite*/ 
    
          }
     
        
          h1 {
           text-align: center; /* centrer le titre*/
    
          }

    
      </style>




  <body class="skin-blue fixed" data-spy="scroll" data-target="#scrollspy">
    <div class="wrapper">

      <header class="main-header">
        <!-- Logo -->
        <!-- Logo -->
        <a href="../index.php" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b>A</b>LT</span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>Acceui</b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          
        </nav>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <div class="sidebar" id="scrollspy">

          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="nav sidebar-menu">
          <li class="treeview">
              <a href="../documents/index.php"><i class="fa fa-book"></i> <span>Documentation </span></a>
            </li>
           

            <li class="treeview">
              <a href="#"> <i class="fa fa-share"></i> <span>Photothèques</span><i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Photos <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                  
                    <li>
                      <a href="#"><i class="fa fa-circle-o"></i>Années<i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview">
                          <li><a href="<?php echo $url; ?>photo2021"><i class="fa fa-circle-o"></i>2021</a></li><!-- afficher la page -->
                          <li><a href="<?php echo $url; ?>photo2020"><i class="fa fa-camera"></i>2020</i></a></li> <!-- afficher la page -->
                          <li><a href="<?php echo $url; ?>photo2019"><i class="fa fa-circle-o"></i>2019</a></li> <!-- afficher la page -->
                        </ul>
                    </li>
                  </ul>
                </li>

                <li>
                  <a href="#"><i class="fa fa-circle-o"></i>Videos <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                    <li>
                      <a href="#"><i class="fa fa-circle-o"></i>Années <i class="fa fa-angle-left pull-right"></i></a>
                      <ul class="treeview-menu">
                        <li><a href="#implementations"><i class="fa fa-circle-o"></i>2021</a></li>
                        <li><a href="#faq"><i class="fa fa-circle-o"></i> 2020</a></li>
                        <li><a href="#collecte"><i class="fa fa-circle-o"></i> 2019</a></li>
                      </ul>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>
         
        </div>
        <!-- /.sidebar -->
      </aside>


    




      </div><!-- /.content-wrapper -->


      <!-- cadre des diapo -->


<!-- =============================================================-->

<div class="container">


  <h1>PHOTOTHEQUE</h1>
  
  <p class="lead"> Abidjan, la capitale économique de la Côte d'Ivoire, avec ses communes huppées enregistre pendant les saisons pluvieuses cinq (5) types d'inondations. 
    Ce sont l'inondation par remontée de la nappe phréatique, dans les vallées, dans les cuvettes, par submersion marine et par débordement des cours d'eau.</p>
  <p class="lead"> L'une de ces communes huppées Cocody est le theatre de Deux (2) types d'inondations à a savoir l'inondation dans les vallées et celles dans les cuvettes.</p>
  <p class="lead">  En effet, plusieurs facteurs naturels (le relief de plateau, la densité pluviométrie) et humaines sont les causes de la survenue de ces risques naturels. </p>
    <p class="lead">Les facteurs humains sont du fait de l'incivisme de la population qui construisent sur les canalisations, dans les zones de passages naturels des eaux pluviales 
       et enfin de l'urbanisation galopante de la commune.</p>
    <p class="lead">Tous ces facteurs entreinent des conséquences domagageable sur la population notamment des pertes en vies humaines et matériels.</p>
    <p class="lead">Pour illustrer nos propos, nous joingnons à notre document quelques photographie et vidéos issues des réseaux sociaux et de nous , 
      et des prises de vue opérés par nous mêmes..</p>

   
  





      </div>
      


     
      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- Create the tabs -->
        <div class="pad">
          This is an example of the control sidebar.
        </div>
      </aside><!-- /.control-sidebar -->
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>

    </div><!-- ./wrapper -->

    <!-- jQuery 2.1.4 -->
    <script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="../bootstrap/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="../plugins/fastclick/fastclick.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../dist/js/app.min.js"></script>
    <!-- SlimScroll 1.3.0 -->
    <script src="../plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js"></script>
    <script src="docs.js"></script>
  </body>
</html>


