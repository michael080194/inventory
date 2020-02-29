<?php
require_once "index_check.php";
require_once "header.php";
try
{
    switch ($op) {
        case 'logout':
            logout();
            header("location:../../index.php");
                        // logout();
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
require_once "footer.php";

// **********************************************************

function logout()
{
    session_destroy();
    $_SESSION = array();
}

