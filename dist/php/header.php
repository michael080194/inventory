<?php

// For `require` and `include`
$_KYC_DOCUMENT_ROOT = realpath($_SERVER["DOCUMENT_ROOT"]) . "/inventory";
$_KYC_URL_ROOT = "/inventory";
$_KYC_VERSION = "1.0.0";
$_KYC_DEBUG = 0; // 1 = true 開啟除錯模式

require_once "config.php";
require_once "kyc_cm_fun.php";
require_once "$_KYC_DOCUMENT_ROOT/dist/include/kyc_db.php";
require_once  "$_KYC_DOCUMENT_ROOT/smarty/libs/Smarty.class.php";

//設定所有錯誤都顯示
error_reporting(E_ALL);@ini_set('display_errors', true);

//實體化 smarty
$smartyTpl = new Smarty;
$smartyTpl->left_delimiter = "<{"; //指定左標籤定義符
$smartyTpl->right_delimiter = "}>"; //指定右標籤定義符
$smartyTpl->template_dir = "$_KYC_DOCUMENT_ROOT/dist/smarty_templates/";
$smartyTpl->compile_dir = "$_KYC_DOCUMENT_ROOT/smarty/templates_c/";
$smartyTpl->config_dir = "$_KYC_DOCUMENT_ROOT/smarty/configs/";
$smartyTpl->cache_dir = "$_KYC_DOCUMENT_ROOT/smarty/cache/";

// 連線資料庫
$db = new kyc_db(_DB_HOST, _DB_USER, _DB_PASS, _DB_NAME);

// 判斷是否為 admin
$_SESSION['is_admin'] = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : false;
$smartyTpl->assign("is_admin", $_SESSION['is_admin']);

define("_EVERY_PAGE", 10);
define("_EVERY_TOOLBAR", 20);

$op = isset($_REQUEST['op']) ? htmlspecialchars($_REQUEST['op'], ENT_QUOTES) : '';
$g2p = isset($_REQUEST['g2p']) ? intval($_REQUEST['g2p']) : 1; // 查詢時頁次控制
$error = $content = '';
