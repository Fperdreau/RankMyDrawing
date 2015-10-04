
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
require('includes/boot.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <META http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <META NAME="description" CONTENT="RankMyDrawings: assessing drawing accuracy using an ELO ranking system">
        <META NAME="keywords" CONTENT="RankMyDrawings">

        <link href='http://fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link type='text/css' rel='stylesheet' href="css/essentials.css"/>
        <link type='text/css' rel='stylesheet' href="css/experiment.css"/>
        <link type="text/css" rel="stylesheet" href="css/modal_style.css" />
        <link type="text/css" rel="stylesheet" href="css/form.css" />

        <!-- JQuery -->
        <script type="text/javascript" src="js/jquery-1.11.1.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/loading.js"></script>

        <title>RankMyDrawings</title>
    </head>

    <body class="mainbody">
        <div class='warningmsg' style='position: fixed; display: none; top: 0; left: 0; width: 100%; height: 50px; z-index: 20; background-color: #550000; color: #EEEEEE;'><input type='button' id='submit'></div>

        <header>
            <div id='sitetitle'>
                <span style="color: rgba(68, 68, 68, 1);">Rank</span><!--
                --><span style="color: rgba(255, 255, 255, 1);">My</span><!--
                --><span style="color: rgba(68, 68, 68, 1);">Drawings</span>
            </div>
            <div class='leanModal headerbox' data-section='contact'>Contact</div>
        </header>

        <!-- Core section -->
        <div id="core">
            <?php require(PATH_TO_PAGES.'modal.php'); ?>
            <div id="pagecontent"></div>
            <div class='progress' style="display: none;">
                <div class='Timer'></div>
                <div id='progressbar'>Progression: 0%</div></div>
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
        <script type="text/javascript" src="js/index.js"></script>
        <script type="text/javascript" src="js/form.js"></script>
        <script type="text/javascript" src="js/plugins.js"></script>
        <script type="text/javascript" src="js/jquery.leanModal.js"></script>
        <script type="text/javascript" src="js/timer/timer.js"></script>
    </body>
</html>
