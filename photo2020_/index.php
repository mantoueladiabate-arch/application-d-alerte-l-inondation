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
    
    *{
            margin: 0; /*centre la boite*/
            padding: 0; /*hauteur la boite*/
            box-sizing: border-box;/*epaisseur de la bordure et le couleur */
            
          } 
    body{
            background:linear-gradient(to right,#2c5364,#0f2027); /*le fond  */
            height:100vh; /*hauteur la boite*/
            display: flex;/*centre l'image*/
            align-items: center;/*aligner l'image sur  la hauteur l'axe de y*/
            justify-content: center;/*aligner l'image sur l'axe de x*/
            overflow: hidden;/*pour que l'image reste dans la boite avec un scrole en bas on utilise auto et hidden pour le cacher*/
           
          }
    
          .slider{ 
            position: relative;
            width: 250px;/*largeur de la boite*/
            height:250px;/*hauteur la boite*/
            transform-style :preserve-3d;
            animation: rotate 30s linear infinite;/*nom de l'animation et temps de rotation des images */
           
          }
    
          @keyframes rotate { /*nomination des bornes, il faut subdiver les 100% par le nombre d'image qu'on a*/
            0% {
            transform: perspective(1000px) rotateY(0deg);
            }
            100% {
                transform: perspective(1000px) rotateY(360deg);
            }
    
        }
    
        .slider span{
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;/*la largueur des images*/
            height: 100%;/*la hauteur des images*/
            transform-origin: center;/*centrer les images*/
            transform-style: preserve-3d;
            transform: rotateY(calc(var(--i)*45deg)) translateZ(350px);
    
        }
        
    
        .slider span img{
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;/*la largueur des images*/
            height: 100%;/*la hauteur des images*/
            border-radius: 10px;/*la forme des bordure des images*/
            object-fit: cover;
            transform: 2s;
    
        }
    
        .slider span:hover img{
           transform: translateY(-50px) scale(1.2);/*faire tourné les images*/
    
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
          <span class="logo-lg"><b>Acceuil</b></span>
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
              <a href="#"> <i class="fa fa-share"></i> <span>Photothèques</span><i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Photos <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                  
                    <li>
                      <a href="#"><i class="fa fa-circle-o"></i>Années<i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview">
                          <li ><a href="photo2021/index.php"><i class="fa fa-circle-o"></i> 2021</a></li>
                          <li><a href="photo2020/index.php"> <i class="fa fa-camera"></i> 2020</i></a></li>
                          <li><a href="photo2019/index.php"><i class="fa fa-circle-o"></i> 2019</a></li>
                        </ul>
                    </li>
                  </ul>
                </li>

                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Videos <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                    <li>
                      <a href="#"><i class="fa fa-circle-o"></i> Années <i class="fa fa-angle-left pull-right"></i></a>
                      <ul class="treeview-menu">
                        <li><a href="#implementations"><i class="fa fa-circle-o"></i> 2021</a></li>
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


<!-- =============================================================

<section id="dependencies">
  <h1 class="page-header"><a href="#dependencies"><b>PHOTOTHEQUE</b></a></h1>
  <h2 class="page-header"><a href="#dependencies"><b>CHAPITRE I- APERÇUS GEOGRAPHIQUES DE LA COMMUNE DE COCODY </b></a></h2>
  <p class="lead"> Le risque d'inondation dans la commune de Cocody a eu ces dernières années des impactes importants sur la vie des riverains. </p>

   
  


</section>




<section id="browsers">
    <h1 class="page-header"><a href="#browsers">2021</a></h1>
      
    ============================================================= -->
      <div class="slider"> 
  
  <span style="--i:1;"><img src="data/2020/centre de sante palmeraire 25 juin 2020_6.jpg" alt=""/></span> 
  <span style="--i:2;"><img src="data/2020/RUE MINISTRE 25 JUIN 20201.jpg" alt=""/></span> 
  <span style="--i:3;"><img src="data/2020/palmeraire 25 juin 2020_5.jpg" alt=""/></span> 
  <span style="--i:4;"><img src="data/2020/palmeraire 25 juin 2020_11.jpg"alt=""/></span> 
  <span style="--i:5;"><img src="data/2020/alabra 9 kilo_3_25102020.jpg" alt=""/></span> 
  <span style="--i:6;"><img src="data/2020/RUE MINISTRE 25 JUIN 2020_3.jpg" alt=""/></span> 
  <span style="--i:7;"><img src="data/2020/1 9 kilos 25 juin 2020.jpg" alt=""/></span> 
  <span style="--i:8;"><img src="data/2020/cocody angre 13 juin 2020.png" alt=""/></span> 
  <span style="--i:9;"><img src="data/2020/9 kilo1 3  juillet 2020.jpg" alt=""/></span> 
  <span style="--i:10;"><img src="data/2020/faya 25 juin 2020_2.jpg" alt=""/></span> 
  <span style="--i:11;"><img src="data/2020/4 9 kilos 25 juin 2020.jpg" alt=""/></span> 
  <span style="--i:12;"><img src="data/2020/faya cite 25 juin 2020.jpg" alt=""/></span> 
  <span style="--i:13;"><img src="data/2020/alabra 9 kilo_1.jpg" alt=""/></span> 
  <span style="--i:14;"><img src="data/2020/cocody rivera3_25062020.jepg" alt=""/></span> 
  <span style="--i:15;"><img src="data/2020/palmeraie 25 juin 2020_7.jpg" alt=""/> </span> 
  <span style="--i:16;"><img src="data/2020/palmeraire 25 juin 2020_10.jpg" alt=""/></span> 
  <span style="--i:17;"><img src="data/2020/cocody angre chateau1_13062020.jpeg" alt=""/></span> 
  <span style="--i:18;"><img src="data/2020/cite allabra 25 juin 2020.png" alt=""/></span>  
  <span style="--i:19;"><img src="data/2020/cocody angre chateau2_13062020.jpeg" alt=""/></span> 
  <span style="--i:20;"><img src="data/2020/riviera mpouto1_26062020.jpeg" alt=""/></span> 
  </div>

  <!-- </section> -->


<!-- ============================================================= -->

     
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


