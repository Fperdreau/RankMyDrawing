<?php
/**
 * File for js and jquery functions
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2014 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of RankMyDrawings.
 *
 * RankMyDrawings is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * RankMyDrawings is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Journal Club Manager.  If not, see <http://www.gnu.org/licenses/>.
 */


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <META http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- JQuery -->
    <script type="text/javascript" src="../../js/jquery-1.11.1.js"></script>
    <script type="text/javascript" src="../../js/jquery-ui.js"></script>
    <script type="text/javascript" src="timer.js"></script>

    <title>Test</title>
</head>

<body class="mainbody">

    Timer
    <div class="timer"></div>

    <script type="text/javascript">
        $(document).ready(function() {
            var options = {maxtime: 10};
            $('.timer').experimenttimer(options);
            var myplugin = $('.timer').data('experimenttimer');
            myplugin.start(); // prints "publicMethod() called!" to console

        });
    </script>
</body>
</html>