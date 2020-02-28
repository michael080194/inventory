<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION["user"])) {
	header("location: login.php");
}

require_once "dist/php/header.php";

try
{
    switch ($op) {
        case 'logout':
            logout();
            header("location:index.php");
            // redirect_header("index.php", 3000, '登出成功！！');
            exit;
    }
} catch (exception $e) {
    $op    = "";
    $error = $e->getMessage();
}

//結果送至樣板
$page_title = "雲端盤點管理系統";
$theme = "index_1.tpl";
require_once "dist/php/footer.php";

// **********************************************************

function logout()
{
    session_destroy();
    $_SESSION = array();
}

