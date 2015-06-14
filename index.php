<?php
/**
 * Created by PhpStorm.
 * User: Cyriaque
 * Date: 15/05/2015
 * Time: 17:34
 *
 * Back-End de l'application BeSFA
 *
 **/
?>
<!DOCTYPE html>
<html>
<head lang="fr">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Ajout des icones de favori/miniature -->
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="shortcut icon" type="image/png" href="img/favicon.png">

    <title>Be SFA</title>

    <!-- Feuilles de styles CSS -->
    <link rel="stylesheet" href="css/main.css">
    <!-- FontAwesome and Bootstrap -->
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Optionnal Boostrap theme -->
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">
    <!-- Calendrier des évènements -->
    <link rel='stylesheet' href='fullcalendar/fullcalendar.css' />

    <!-- We'll need jQuery later -->
    <script src="js/jquery-2.1.3.js" type="text/javascript"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.min.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

    <!-- Importation des polices de caractère -->
    <!--
    <link href='http://fonts.googleapis.com/css?family=Roboto:500,900,400' rel='stylesheet' type='text/css'>
    -->
    <link href='http://fonts.googleapis.com/css?family=Raleway:500,900,400' rel='stylesheet' type='text/css'>
    <!--
    <link href='http://fonts.googleapis.com/css?family=Comic Sans:500,900,400' rel='stylesheet' type='text/css'>
    -->

    <!-- Meta de contenu -->
    <meta name="author" content="Cyriaque" />
    <meta name="description" content="Bienvenue sur le site back-end de l'application du BDE SFA, de l'université Savoie Mont Blanc !
        Nous espérons pouvoir vous fournir toutes les informations que vous désirez au sujet des activités proposées par le BDE
        ou par l'Université en général !" />
    <meta name="keywords" lang="fr" content="bde sfa besfa usmb universite savoie mont blanc mont-blanc activites menu
        chautagne calendrier etudiant" />

</head>
<body>

<!-- Fixed navbar -->
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="home">
                <span class="sr-only">Be SFA</span>
                <span class="glyphicon glyphicon-home"></span>
            </a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li id="home"><a href="home">Accueil</a></li>
                <li id="mainApp"><a href="mainApp">Page principale</a></li>
                <li id="diary"><a href="diary">Calendrier</a></li>
                <li id="media"><a href="media">Multimedia</a></li>
                <li id="contact"><a href="contact">Contact</a></li>
                <li id="about"><a href="about">À propos</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li id="admin"><a href="admin/index.php">Administration</a></li>
				<li><a href="api">API</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<?php


$pages = array('home', 'mainApp', 'diary', 'media', 'contact', 'about', 'admin', 'api');


    $url = explode("/", $_SERVER['REQUEST_URI']);
    if(count($url) > 1) {
        $page = str_replace(".php", "", $url[count($url) - 1]);
        if($page === "index" or $page === "") {
            $page = "home";
        } else if($page === "admin") {
            $page = "admin/index";
        }
    } else {
        $page = $pages[0];
    }

include("$page.php"); ?>

<div class="container">
    <footer>
        <p class="text-muted">&copy; Cyriaque pour BeSFA <span class="label label-primary">Version bêta</span></p>
    </footer>
</div>

<!-- Scripts necessaires -->
<script src="js/bootstrap.min.js" type="text/javascript"></script>

<script src="js/rerouter.js" type="text/javascript"></script>
</body>
</html>