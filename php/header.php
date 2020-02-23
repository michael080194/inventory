<?php

$_DOCUMENT_ROOT = dirname(__DIR__);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";
require_once "$_DOCUMENT_ROOT/include/kyc_db.php";

error_reporting(E_ALL);@ini_set('display_errors', true); //設定所有錯誤都顯示

#網站實體路徑(不含 /)  Users/michaelchang/Documents/michael/php/ele
define('ROOT_PATH', str_replace("\\", "/", dirname(__FILE__)));

#$_SERVER["DOCUMENT_ROOT"] ==> /Users/michaelchang/Documents/michael/php
#網站URL(不含 /) http://localhost/ele
// define('XOOPS_URL', $http . $_SERVER["HTTP_HOST"] . str_replace($_SERVER["DOCUMENT_ROOT"], "", XOOPS_ROOT_PATH));
// define('XOOPS_URL', kyc_get_url());
#--------- WEB -----
#程式檔名(含副檔名)
$WEB['file_name'] = basename($_SERVER['PHP_SELF']); //index.php
//basename(__FILE__)
$WEB['moduleName'] = basename(__DIR__); //ugm_p
$WEB['version']    = "1.0";
// echo __DIR__."<br>";
// echo __FILE__;die();
#除錯
$WEB['debug']  = 0;
$WEB['prefix'] = "001";
#--------- WEB END -----

//連線資料庫
$db = new db(_DB_HOST, _DB_USER, _DB_PASS, _DB_NAME);

#判斷是否登入
$_SESSION['isUser']  = isset($_SESSION['isUser']) ? $_SESSION['isUser'] : false;
$_SESSION['isAdmin'] = isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : false;
