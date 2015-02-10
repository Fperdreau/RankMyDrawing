
<?php
/*
Copyright © 2014, F. Perdreau, Radboud University Nijmegen
=======
This file is part of RankMyDrawings.

RankMyDrawings is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

RankMyDrawings is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with RankMyDrawings.  If not, see <http://www.gnu.org/licenses/>.

*/
session_set_cookie_params(3600);
session_start();
$_SESSION['app_name'] = basename(__DIR__);
$_SESSION['path_to_app'] = dirname(__FILE__)."/";
$_SESSION['path_to_img'] = $_SESSION['path_to_app'].'images/';
$_SESSION['path_to_includes'] = $_SESSION['path_to_app']."includes/";
$_SESSION['path_to_html'] = $_SESSION['path_to_app']."php/";
$_SESSION['path_to_pages'] = $_SESSION['path_to_app']."pages/";
date_default_timezone_set('Europe/Paris');

// Includes required files (classes)
require_once($_SESSION['path_to_includes'].'includes.php');

// Get application settings
$config = new site_config('get');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
        <META http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <META NAME="description" CONTENT="Journal Club Manager. Organization. Submit or suggest a presentation. Archives.">
        <META NAME="keywords" CONTENT="Journal Club">
        <link href='http://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link type='text/css' rel='stylesheet' href="css/stylesheet.css"/>
        <link type='text/css' rel='stylesheet' href="css/jquery-ui.css"/>
        <link type='text/css' rel='stylesheet' href="css/jquery-ui.theme.css"/>
        <link type="text/css" rel="stylesheet" href="css/modal_style.css" />

        <!-- JQuery -->
        <script type="text/javascript" src="js/jquery-1.11.1.js"></script>
        <script type="text/javascript" src="js/jquery.leanModal.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/spin.js"></script>

        <title><?php $config->sitetitle ?></title>
    </head>

    <body class="mainbody">
        <div class='warningmsg' style='position: fixed; display: none; top: 0; left: 0; width: 100%; height: 50px; z-index: 20; background-color: #550000; color: #EEEEEE;'><input type='button' id='submit'></div>

        <?php require($_SESSION['path_to_pages'].'modal.php'); ?>

        <div id="mainheader">
            <!-- Header section -->
            <?php require($_SESSION['path_to_pages'].'page_header.php'); ?>
        </div>

        <!-- Core section -->
        <div class="core">
        	<div id="loading"></div>
        	<div id="pagecontent">
        	</div>
        </div>

        <!-- Footer section -->
        <div id="footer">
            <span id="sign"><?php echo "<a href='$config->repository' target='_blank'>$config->app_name $config->version</a>
             | <a href='http://www.gnu.org/licenses/agpl-3.0.html' target='_blank'>GNU AGPL v3 </a>
             | <a href='http://www.florianperdreau.fr' target='_blank'>&copy2014 $config->author</a>" ?></span>
        </div>

        <!-- Bunch of jQuery functions -->
        <script type="text/javascript" src="js/index.js"></script>

        <!-- TinyMce (Rich-text textarea) -->
        <script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
        <!-- mini upload form plugin -->
        <link type="text/css" href="js/mini-upload-form/assets/css/style.css" rel="stylesheet" />
        <script  type="text/javascript" src="js/mini-upload-form/assets/js/jquery.knob.js"></script>
        <script  type="text/javascript" src="js/mini-upload-form/assets/js/jquery.ui.widget.js"></script>
        <script  type="text/javascript" src="js/mini-upload-form/assets/js/jquery.iframe-transport.js"></script>
        <script  type="text/javascript" src="js/mini-upload-form/assets/js/jquery.fileupload.js"></script>
        <script  type="text/javascript" src="js/mini-upload-form/assets/js/script.js"></script>

    </body>
</html>