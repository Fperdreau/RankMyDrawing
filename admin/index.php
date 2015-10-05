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
require('../includes/boot.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
        <META NAME="viewport" CONTENT="width=device-width, target-densitydpi=device-dpi, initial-scale=1.0, user-scalable=yes">

        <META http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <META NAME="description" CONTENT="Journal Club Manager. Organization. Submit or suggest a presentation. Archives.">
        <META NAME="keywords" CONTENT="Journal Club">
        <link href='http://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link type='text/css' rel='stylesheet' href="../css/essentials.css"/>
        <link type='text/css' rel='stylesheet' href="../css/admin.css"/>
        <link type="text/css" rel="stylesheet" href="../css/modal_style.css" />
        <link type="text/css" rel="stylesheet" href="../css/form.css" />
        <link type="text/css" rel="stylesheet" href="js/myuploads/uploader.css" />

        <!-- JQuery -->
        <script type="text/javascript" src="../js/jquery-1.11.1.js"></script>
        <script type="text/javascript" src="../js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/loading.js"></script>

        <title><?php $AppConfig->sitetitle ?></title>
    </head>

    <body class="mainbody">

        <!-- Menu section -->
        <div class='sideMenu' id='off'>
            <nav>
                <ul>
                    <li><a href='index.php?page=admininfo' class='menu-section' id='admininfo'>Admin Info</a></li>
                    <li><a href='index.php?page=tools' class='menu-section' id='Tools'>Tools</a></li>
                    <li><a href='index.php?page=management' class='menu-section' id='management'>Drawing Management</a></li>
                    <li><a href='index.php?page=application' class='menu-section' id='application'>Settings</a></li>
                </ul>
            </nav>
        </div>

        <header>
            <div class='menu_btn'></div>
            <div id='sitetitle'>
                <span style="color: rgba(68, 68, 68, 1);">Rank</span><!--
                    --><span style="color: rgba(255, 255, 255, 1);">My</span><!--
                    --><span style="color: rgba(68, 68, 68, 1);">Drawings</span>
            </div>

            <?php
            if (!isset($_SESSION['logok']) || !$_SESSION['logok']) {
                $showlogin = "<div class='leanModal headerbox' id='user_login' data-section='user_login'>LogIn</div>";
            } else {
                $showlogin = "<div class='headerbox' id='logout'>LogOut</div>";
            }
            echo $showlogin;
            ?>
        </header>

        <!-- Core section -->
        <div id="core">
            <?php require('pages/modal.php'); ?>
            <div id="pagecontent"></div>
        </div>

        <!-- Footer section -->
        <footer id="footer">
            <div id="colBar"></div>
            <div id="appTitle"><?php echo $AppConfig->app_name; ?></div>
            <div id="appVersion">Version <?php echo $AppConfig->version; ?></div>
            <div id="sign">
                <div><?php echo "<a href='$AppConfig->repository' target='_blank'>Sources</a></div>
                    <div><a href='http://www.gnu.org/licenses/agpl-3.0.html' target='_blank'>GNU AGPL v3</a></div>
                    <div><a href='http://www.florianperdreau.fr' target='_blank'>&copy2014 $AppConfig->author</a>" ?></div>
            </div>
        </footer>

        <!-- Bunch of jQuery functions -->
        <script type="text/javascript" src="js/admin.js"></script>
        <script type="text/javascript" src="../js/form.js"></script>
        <script type="text/javascript" src="../js/jquery.leanModal.js"></script>
        <script type="text/javascript" src="js/plugins.js"></script>

        <!-- TinyMce (Rich-text textarea) -->
        <script type="text/javascript" src="../js/tinymce/tinymce.min.js"></script>

        <!-- MyUploads -->
        <script type="text/javascript" src="js/myuploads/Myupload.js"></script>

    </body>
</html>
