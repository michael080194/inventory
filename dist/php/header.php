<?php
// if (session_status() == PHP_SESSION_NONE) {
// 	session_start();
// }
$_DOCUMENT_ROOT = realpath($_SERVER["DOCUMENT_ROOT"]) . "/inventory";
// $_DOCUMENT_ROOT = str_replace("/dist","",$_DOCUMENT_ROOT);
// echo $_DOCUMENT_ROOT;
// die();
// $_DOCUMENT_ROOT = dirname(__DIR__);
// $_DOCUMENT_ROOT = realpath($_SERVER["DOCUMENT_ROOT"]);


require_once "config.php";
require_once "kyc_cm_fun.php";
require_once "$_DOCUMENT_ROOT/dist/include/kyc_db.php";
require_once  "$_DOCUMENT_ROOT/smarty/libs/Smarty.class.php";

error_reporting(E_ALL);@ini_set('display_errors', true); //設定所有錯誤都顯示
$_KYC_ROOT_PATH = "/inventory";
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
#--------- WEB END -----
//實體化 smarty
$smartyTpl = new Smarty;
$smartyTpl->left_delimiter = "<{"; //指定左標籤定義符
$smartyTpl->right_delimiter = "}>"; //指定右標籤定義符
$smartyTpl->template_dir = "$_DOCUMENT_ROOT/dist/smarty_templates/";
$smartyTpl->compile_dir = "$_DOCUMENT_ROOT/smarty/templates_c/";
$smartyTpl->config_dir = "$_DOCUMENT_ROOT/smarty/configs/";
$smartyTpl->cache_dir = "$_DOCUMENT_ROOT/smarty/cache/";
//連線資料庫
$db = new kyc_db(_DB_HOST, _DB_USER, _DB_PASS, _DB_NAME);

#判斷是否登入
$_SESSION['isUser']  = isset($_SESSION['isUser']) ? $_SESSION['isUser'] : false;
$_SESSION['isAdmin'] = isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : false;
$smartyTpl->assign("isUser", $_SESSION['isUser']);
$smartyTpl->assign("isAdmin", $_SESSION['isAdmin']);

define("_EVERY_PAGE", 10);
define("_EVERY_TOOLBAR", 20);

$op     = isset($_REQUEST['op']) ? htmlspecialchars($_REQUEST['op'], ENT_QUOTES) : '';
$g2p = isset($_REQUEST['g2p']) ? intval($_REQUEST['g2p']) : 1; // 查詢時頁次控制
$error = $content = '';
