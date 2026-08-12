<?php


if (!isset($_SESSION['svmobiadmin'])) {
    header("location:login.php");
}