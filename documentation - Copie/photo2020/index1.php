<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Résumé</title>
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
    section{
        display: flex;/*centre l'image*/
       justify-content: center;/*aligner l'image sur l'axe de x*/
        align-items: center;/*aligner l'image sur  la hauteur l'axe de y*/
        background-color: #EEEEEE ;/*couleur de fond*/
     
       
      }

     
      #caroussel{
        width: 700px;/*largeur de la boite*/
        height: 100%;
       /* margin-bottom: auto; /*centre la boite*/
       /* height:auto; hauteur la boite*/
        overflow: hidden;/*pour que l'image reste dans la boite avec un scrole en bas on utilise auto et hidden pour le cacher*/
        border: 2px solid black;/*epaisseur de la bordure et le couleur */
        
      } 
      
      .images { 
        display: flex;/*mettre les images sur la meme ligne*/
        /*width: 700px;/*largeur de la boite*/
       /* height:auto;/*hauteur la boite*/
        animation-duration: 80s; /*durer minimum de l'animation de l'image*/ 
        animation-name: mesImages;/*nom de l'animation*/
        animation-iteration-count: infinite;/*pour que l'animation tourne en boucle*/
      }
    .d1{
      width: 576px; /*largueur de l'image*/
			height:432px; /*hauteur de l'image*/
      box-shadow: 0px 15px 10px -5px #777;
      background-color: #EDEDED;/*couleur de fond*/
      background-size: contain; /*position du conten*/
      animation: fondu 15s ease-in-out infinite both;
    }

    .conteneur{
      max-width: 576px; /*largueur de l'image*/
			margin: 50px auto; /*hauteur de l'image*/
    
    }

    @keyframes mesImages{ /*nomination des bornes, il faut subdiver les 100% par le nombre d'image qu'on a*/
      0% {transform: translatex(0);}
     5%{transform: translatex(-700px);}/*au temps 5 il faut reculer de -700pixels*/
     10%{transform: translatex(-1400px);} /*au temps 10 il faut reculer de la largeur de l'image dans notre cas c'est -700pixels*2*/
     15%{transform: translatex(-2100px);} /*au temps 15 il faut reculer de -700pixels*3*/
     20%{transform: translatex(-2800px);} /*au temps 20 il faut reculer de -700pixels*4*/
     25%{transform: translatex(-3500px);} /*au temps 25 il faut reculer de 700pixels*5*/
     30%{transform: translatex(-4200px);}/*au temps 30 il faut reculer de -700pixels*6*/
     35%{transform: translatex(-4900px);} /*au temps 35 il faut reculer de -700pixels*7*/
     40%{transform: translatex(-5600px);} /*au temps 40 il faut reculer de -700pixels*8*/
     45%{transform: translatex(-6300px);} /*au temps 45 il faut reculer de -700pixels*9*/
     50%{transform: translatex(-7000px);} /*au temps 50 il faut reculer de -700pixels*10*/
     55%{transform: translatex(-7700px);}/*au temps 55 il faut reculer de -700pixels*11*/
     60%{transform: translatex(-8400px);} /*au temps 60 il faut reculer de -700pixels*12*/
     65%{transform: translatex(-9100px);} /*au temps 65 il faut reculer de -700pixels*13*/
     70%{transform: translatex(-9800px);} /*au temps 70 il faut reculer de -700pixels*14*/
     75%{transform: translatex(10500px);} /*au temps 75 il faut reculer de -700pixels*15*/
     80%{transform: translatex(-11200px);}/*au temps 80 il faut reculer de -700pixels*16*/
     85%{transform: translatex(-11900px);} /*au temps 85 il faut reculer de -700pixels*17*/
     90%{transform: translatex(-12600px);} /*au temps 90 il faut reculer de -700pixels*18*/
     95%{transform: translatex(-13300px);} /*au temps 80 il faut reculer de -700pixels*19*/
     100%{transform: translatex(0);} /*au temps 100 il faut reculer de 0pixels*/

    }


    .d2{
      width: 100%; /*largueur de l'image*/
			height:0px; /*hauteur de l'image*/
      box-shadow: 0px 0px 10px #777;
      background-color: #EDEDED;/*couleur de fond*/
      background-size: contain; /*position du conten*/
      animation: fondu 15s ease-in-out infinite both; /*mettre en pause l'animation*/
    }

    .d1:hover, .d2:hover{ /*mettre en pause l'animation*/
      animation-play-state:paused;
    }

    @keyframes fondu{ 
      0%{ background-image:url("9 kilo1 3  juillet 2020.jpg");}
      33.33%{ background-image:url("data/2019/RUE MINISTRE 25 JUIN 20203.jpg");}
      66.67%{ background-image:url("palmeraire 25 juin 2020_11.jpg");}
      100%{ background-image:url("palmeraire 25 juin 2020_4.jpg");}

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
                        <ul class="treeview-menu">
                          <li><a href="phototheque/photo2021"><i class="fa fa-circle-o"></i>2021</a></li>
                          <li><a href="#upgrade"><i class="fa fa-circle-o"></i>2020</a></li>
                          <li><a href="#faq"><i class="fa fa-circle-o"></i>2019</a></li>
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
      <!-- cadre des diapo -->

       <!--  <div class="d1">  </div>
          
        <div class="conteneur">
            <div class="d2">  </div>
        </div>-->
       <!-- cadre des diapo -->
      <section >

        <div id="caroussel"> 
          <div class="images"> 
            <img src="data/2020/faya 25 juin 2020_2.jpg"/>
            <img src="data/2020/RUE MINISTRE 25 JUIN 20201.jpg"/>
            <img src="data/2020/palmeraire 25 juin 2020_5.jpg"/>
            <img src="data/2020/palmeraire 25 juin 2020_11.jpg"/>
            <img src="data/2020/alabra 9 kilo_3_25102020.jpg"/>
            <img src="data/2020/1 9 kilos 25 juin 2020.jpg"/> 
            <img src="data/2020/RUE MINISTRE 25 JUIN 2020_3.jpg"/> 
            <img src="data/2020/palmeraire 25 juin 2020_6.jpg"/> 
            <img src="data/2020/cocody angre 13 juin 2020.png"/>
            <img src="data/2020/9 kilo1 3  juillet 2020.jpg"/> 
            <img src="data/2020/4 9 kilos 25 juin 2020.jpg"/> 
            <img src="data/2020/alabra 9 kilo_1.jpg"/> 
            <img src="data/2020/faya cite 25 juin 2020.jpg"/> 
            <img src="data/2020/palmeraie 25 juin 2020_7.jpg"/> 
            <img src="data/2020/centre de sante palmeraire 25 juin 2020_6.jpg"/> 
            <img src="data/2020/palmeraire 25 juin 2020_10.jpg"/> 
            <img src="data/2020/cocody angre chateau1_13062020.jpeg"/> 
            <img src="data/2020/cocody angre chateau2_13062020.jpeg"/> 
            <img src="data/2020/cocody rivera3_25062020.jpeg"/> 
            <img src="data/2020/riviera mpouto1_26062020.jpeg"/> 
          </div>
        </div>
  
      </section>


                

                                           

    <!--</div> /.content -->
    </div><!-- /.content-wrapper -->







     
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
