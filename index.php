<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
require_once "dist/php/header.php";

if (!isset($_SESSION["user"])) {
	header("location: $_KYC_URL_ROOT/login.php");
} else {
    header("location: $_KYC_URL_ROOT/dist/php/index_1.php");
}
// header("location: " . KYC_URL ."/dist/php/index_1.php");
// header("location:dist/php/index_1.php");
