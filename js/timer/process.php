<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 28/09/2015
 * Time: 18:02
 */

session_start();
if (empty($_SESSION['startTime'])) {
    $_SESSION['startTime'] = date();
}

