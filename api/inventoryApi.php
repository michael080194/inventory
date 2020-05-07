<?php
// 盤點系統 與手機溝通之 API
header('Access-Control-Allow-Origin: *'); //
header("Content-Type:text/plain; charset=utf-8"); // text/html
session_start();
require_once "../dist/php/config.php";
require_once "../dist/php/kyc_cm_fun.php";
require_once '../dist/include/kyc_db.php';

$db = new kyc_db(_DB_HOST, _DB_USER, _DB_PASS, _DB_NAME);

$op = $_POST["op"]; // 操作對象
switch ($op) {
    case 'login':
        // app 手機程式用
        // 檢查使用者之公司別，帳號，密碼
        echo login();
        break;
    case 'logout':
        // 網頁版程式用
        // 登出會清除 session
        echo logout();
        break;
    case 'deleteCheckData':
        // app 手機程式用
        // 刪除 盤點異動檔單筆(inv_check) 資料 by 公司別+倉庫別+盤點人員+id序號
        echo deleteCheckData();
        break;
    case 'deleteStockData':
        // 網頁版程式用
        // 刪除 盤點庫存檔(inv_stock)整批資料 資料 by 公司別+倉庫別+盤點檔上傳日期
        echo deleteStockData();
        break;
    case 'checkStockDataExist':
        // 網頁版程式用
        // 檢查 盤點庫存檔(inv_stock)是否重複 by 公司別+倉庫別+盤點檔上傳日期
        echo checkStockDataExist();
        break;
    case 'listStockDataSummary':
        // 網頁版程式用
        // 回傳 盤點庫存檔(inv_stock) 公司別+倉庫別+盤點檔上傳日期 的摘要給使用者挑選
        echo listStockDataSummary();
        break;
    case 'insertByBarcode':
        // app 手機程式用
        // 盤點人員掃條碼後;insert 一筆資料至盤點檔(inv_check);並回傳 新增 id 及 產品基本資料
        echo insertByBarcode();
        break;
    case 'insertBySearchStock':
        // app 手機程式用
        // 盤點人員 查詢盤點庫存檔(inv_stock)；利用所選取到之資料；
        // insert 一筆資料至盤點檔(inv_check);並回傳 新增 id 及 產品基本資料
        echo insertBySearchStock();
        break;
    case 'insertByInputPartno':
        // app 手機程式用
        // 儲存使用者 直接輸入產品編號及備註之資料
        // 並回傳 新增 id 及 產品基本資料
        echo insertByInputPartno();
        break;
    case 'listCheckData':
         // app 手機程式用
         // 顯示 公司別+倉庫別+盤點人員+盤點日期 之盤點資料(inv_check)
        echo listCheckData();
        break;
    case 'searchStock':
        // app 手機程式用
        // 在無法掃描時;查詢 盤點現有庫存檔(inv_stock);讓盤點人員挑選資料來輸入盤點資料
        // 顯示 公司別+倉庫別+盤點日期 之現有盤點庫存資料(可用產品編號及品名查詢)
        echo searchStock(); // 查詢 現有庫存檔
        break;
    case 'updateCheckData':
         // app 手機程式用
         // 更新盤點檔(inv_check)數量 by 盤點異動檔 by 公司別+倉庫別+盤點人員+序號
        echo updateCheckData();
        break;
    case 'stockExport':
        // 網頁版程式用
        // 匯出 Excel 檔
        echo stockExport();
        break;
    case 'stockImport':
        // 網頁版程式用
        // 匯入 Excel 檔
        echo stockImport();
        break;
    case 'gatherBarcode': // 收集條碼 暫時不使用 2020-05-01
        // app 手機程式用
        // 儲存使用者  輸入 產品編號+產品說明+條碼編號
        // 並回傳 新增 id
        echo gatherBarcode();
        break;
    default:
        $r                   = array();
        $r['responseStatus'] = "FAIL";
        echo json_encode($r, JSON_UNESCAPED_UNICODE);
        break;
}
################################
# 儲存使用者  輸入 產品編號+產品說明+條碼編號
# 並回傳 id 及 產品基本資料
#################################
function gatherBarcode()
{
    $comp_id      = $_POST["comp_id"];    // 公司別
    $c_partno     = $_POST["c_partno"];   // 產品編號
    $c_descrp     = $_POST["c_descrp"];   // 產品名稱
    $barcode      = $_POST["barcode"];    // 條碼編號

    global $db;
    if (!$comp_id or !$c_partno or !$barcode) {
        $r   = array();
        $r['responseStatus']  = "FAIL";
        return json_encode($r, JSON_UNESCAPED_UNICODE);
    }

    $type = "";
    $id=0;
    if(isset($_POST["id"])){
       $type = "ADD";
       $id=$_POST["id"];
    }
    // 將盤點資料 新增至 盤點異動檔
    $tbl       = $comp_id . "_inv_gathe_barcode"; // 條碼收集檔
    $sqlArr    = array();
    if($type != "ADD"){
       $sqlArr['id']      = $id;        // 修改的 id
    }
    $sqlArr['c_type']     = $type;       // 新增或修改
    $sqlArr['comp_id']    = $comp_id;    // 公司別
    $sqlArr['c_partno']   = $c_partno;   // 產品編號
    $sqlArr['c_descrp']   = $c_descrp;   // 產品名稱
    $sqlArr['barcode']    = $barcode;    // 條碼編號

    $insert_id = $db->kyc_sqlReplace($tbl, $sqlArr); // 取得新增資料的 id 值

    $all = array();

    if($type == "ADD"){
       $all["insert_id"]     = $insert_id;
    } else {
       $all["insert_id"]     = $id;
    }

    $r['responseStatus']  = "OK";
    $r['responseMessage'] = "";
    $r['responseArray']   = $all;

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 檢查 盤點庫存檔(inv_stock)是否重複 by 公司別+倉庫別+盤點檔上傳日期
#################################
function checkStockDataExist()
{
    global $db;
    $r   = array();
    $comp_id      = $_SESSION["comp_id"];    // 公司別
    $c_house      = $_POST["c_house"];    // 倉庫別
    $check_date   = new DateTime($_POST["check_date"]); // 盤點日期
    $check_date   = $check_date->format('Y-m-d H:i:s');



    if (!$comp_id or !$c_house or !$check_date) {
        $r['responseStatus']  = "FAIL";
        $r['responseMessage'] = "";
        return json_encode($r, JSON_UNESCAPED_UNICODE);
    }

    $tbl1             = $comp_id . "_inv_stock"; // 現有庫存檔
    $searchCondition =  "comp_id = '{$comp_id}' AND c_house = '{$c_house}' AND check_date = '{$check_date}'";

    $sql =  "select * from `$tbl1`  WHERE " . $searchCondition;
    $result          = $db->kyc_sqlFetch_assoc($sql);
    $all = array();
    $count = 0;
    foreach ($result as $prods) {
        $all[] = $prods;
        $count ++;
    }
    if($count == 0){
        $r['responseStatus']  = "notExist";
        $r['responseMessage'] = "資料不重複";
    } else {
        $r['responseStatus']  = "exist";
        $r['responseMessage'] = "資料重複";
        $r['responseArray']   = $all;
    }

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
###############################
# 刪除 盤點異動檔 資料
#################################
function deleteCheckData()
{
    $comp_id      = $_POST["comp_id"];    // 公司別
    $c_house      = $_POST["c_house"];    // 倉庫別
    $user         = $_POST["user"];       // 盤點人員
    $check_id     = $_POST["check_id"];  // 盤點異動檔 的序號

    global $db;
    $r   = array();
    if (!$comp_id or !$check_id) {
        $r['responseStatus']  = "FAIL";
        return json_encode($r, JSON_UNESCAPED_UNICODE);
    }

    $tbl             = $comp_id . "_inv_check"; // 盤點異動檔
    $deleteCondition =  "comp_id = '{$comp_id}' AND c_house = '{$c_house}' AND ";
    $deleteCondition .= "check_user = '{$user}' AND id= '{$check_id}' ";

    $db->kyc_sqlDelete($tbl, $deleteCondition);

    $r['responseStatus']  = "OK";
    $r['responseMessage'] = "";

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
###############################
# 刪除 盤點庫存檔(inv_stock)整批資料 資料 by 公司別+倉庫別+盤點檔上傳日期
#################################
function deleteStockData()
{
    // $comp_id      = $_POST["comp_id"];    // 公司別
    $comp_id = $_SESSION["comp_id"];
    $c_house      = $_POST["c_house"];    // 倉庫別
    $check_date   = new DateTime($_POST["check_date"]); // 盤點檔上傳日期
    $check_date   = $check_date->format('Y-m-d H:i:s');

    global $db;
    $r   = array();
    if (!$comp_id or !$c_house) {
        $r['responseStatus']  = "FAIL";
        return json_encode($r, JSON_UNESCAPED_UNICODE);
    }

    $tbl             = $comp_id . "_inv_stock"; // 現有庫存檔
    $deleteCondition =  "comp_id = '{$comp_id}' AND c_house = '{$c_house}' AND ";
    $deleteCondition .= "check_date = '{$check_date}' ";

    $db->kyc_sqlDelete($tbl, $deleteCondition);

    $tbl             = $comp_id . "_inv_check"; // 盤點明細檔
    $deleteCondition =  "comp_id = '{$comp_id}' AND c_house = '{$c_house}' AND ";
    $deleteCondition .= "check_date = '{$check_date}' ";
    $db->kyc_sqlDelete($tbl, $deleteCondition);

    $r['responseStatus']  = "OK";
    $r['responseMessage'] = "";

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 盤點人員掃條碼後將資料送雲端
# 此 function 會 insert 一筆資料至盤點檔
# 並回傳 新增 id 及 產品基本資料
#################################
function insertByBarcode()
{
    $comp_id      = $_POST["comp_id"];    // 公司別
    $c_house      = $_POST["c_house"];    // 倉庫別
    $user         = $_POST["user"];       // 盤點人員
    $check_date   = $_POST["check_date"]; // 盤點日期
    $barcode      = $_POST["barcode"];    // 條碼
    $c_qty        = $_POST["c_qty"];      // 盤點數量

    global $db;
    if (!$comp_id or !$barcode) {
        $r   = array();
        $r['responseStatus']  = "FAIL";
        return json_encode($r, JSON_UNESCAPED_UNICODE);
    }

    // 用 barcode 抓取產品產品基本資料
    $tbl             = $comp_id . "_inv_stock"; // 現有庫存檔
    $searchCondition = "`comp_id` = '{$comp_id}' AND `c_house` = '{$c_house}' AND `barcode` = '{$barcode}' ";
    $sql             = "SELECT * FROM `$tbl` WHERE " . $searchCondition;
    $result          = $db->kyc_sqlFetch_assoc($sql);

    $r   = array();
    $all = array();
    $c_partno = ""; // 產品編號
    $wkcount = 0;
    foreach ($result as $prods) {
        $wkcount++;
        $c_partno   = $prods['c_partno'];
        $all[] = $prods;
    }
    // 條碼不存在於 現有庫存檔
    if($wkcount == 0){
        $prods = array();
        $prods['c_partno']="";
        $prods['c_descrp']="";
        $prods['c_unit']="";
        $all[] = $prods;
    }
    // 將盤點資料 新增至 盤點異動檔
    $dt = new DateTime();

    $tbl       = $comp_id . "_inv_check"; //盤點異動檔
    $sqlArr    = array();
    $sqlArr['comp_id']    = $comp_id;    // 公司別
    $sqlArr['c_house']    = $c_house ;   // 倉庫別
    $sqlArr['check_date'] = $check_date; // 盤點日期
    $sqlArr['check_user'] = $user;       // 盤點人員
    $sqlArr['c_partno']   = $c_partno;   // 產品編號
    $sqlArr['barcode']    = $barcode;    // 條碼
    $sqlArr['check_qty']  = $c_qty;      // 盤點人員
    $sqlArr['create_date']  = $dt->format('Y-m-d H:i:s');  // 建檔時間
    $insert_id = $db->kyc_insert($tbl, $sqlArr); // 取得新增資料的 id 值

    $all["insert_id"] = $insert_id;
    $r['responseStatus']  = "OK";
    $r['responseMessage'] = "";
    $r['responseArray']   = $all;

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 查詢到資料後
# 將所選取到之資料儲存
# 並回傳 新增 id 及 產品基本資料
#################################
function insertBySearchStock()
{
    $comp_id      = $_POST["comp_id"];    // 公司別
    $c_house      = $_POST["c_house"];    // 倉庫別
    $user         = $_POST["user"];       // 盤點人員
    $check_date   = $_POST["check_date"]; // 盤點日期
    $c_partno     = $_POST["c_partno"];   // 產品編號
    $c_descrp     = $_POST["c_descrp"];   // 產品名稱
    $c_unit       = $_POST["c_unit"];      // 單位
    $barcode      = $_POST["barcode"];    // 條碼
    $c_qty        = $_POST["c_qty"];      // 盤點數量

    global $db;
    if (!$comp_id or !$c_house or !$user) {
        $r   = array();
        $r['responseStatus']  = "FAIL";
        return json_encode($r, JSON_UNESCAPED_UNICODE);
    }


    // 將盤點資料 新增至 盤點異動檔
    $dt = new DateTime();
    $tbl       = $comp_id . "_inv_check"; //盤點異動檔
    $sqlArr    = array();
    $sqlArr['comp_id']    = $comp_id;    // 公司別
    $sqlArr['c_house']    = $c_house ;   // 倉庫別
    $sqlArr['check_date'] = $check_date; // 盤點日期
    $sqlArr['check_user'] = $user;       // 盤點人員
    $sqlArr['c_partno']   = $c_partno;   // 產品編號
    $sqlArr['barcode']    = $barcode;    // 條碼
    $sqlArr['check_qty']  = $c_qty;      // 盤點數量
    $sqlArr['create_date']  = $dt->format('Y-m-d H:i:s');  // 建檔時間
    $insert_id = $db->kyc_insert($tbl, $sqlArr); // 取得新增資料的 id 值

    $all = array();
    $prods = array();
    $prods['c_partno']=$c_partno;
    $prods['barcode']=$barcode;
    $prods['c_descrp']=$c_descrp;
    $prods['c_unit']=$c_unit;
    $all[] = $prods;

    $all["insert_id"] = $insert_id;
    $r['responseStatus']  = "OK";
    $r['responseMessage'] = "";
    $r['responseArray']   = $all;

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 儲存使用者 直接輸入產品編號及備註之資料
# 並回傳 新增 id 及 產品基本資料
#################################
function insertByInputPartno()
{
    $comp_id      = $_POST["comp_id"];    // 公司別
    $c_house      = $_POST["c_house"];    // 倉庫別
    $user         = $_POST["user"];       // 盤點人員
    $check_date   = $_POST["check_date"]; // 盤點日期
    $c_partno     = $_POST["c_partno"];   // 產品編號
    $c_descrp     = $_POST["c_descrp"];   // 產品名稱
    $c_qty        = $_POST["c_qty"];      // 盤點數量

    global $db;
    if (!$comp_id or !$c_house or !$user) {
        $r   = array();
        $r['responseStatus']  = "FAIL";
        return json_encode($r, JSON_UNESCAPED_UNICODE);
    }


    // 將盤點資料 新增至 盤點異動檔
    $dt = new DateTime();
    $tbl       = $comp_id . "_inv_check"; //盤點異動檔
    $sqlArr    = array();
    $sqlArr['comp_id']    = $comp_id;    // 公司別
    $sqlArr['c_house']    = $c_house ;   // 倉庫別
    $sqlArr['check_date'] = $check_date; // 盤點日期
    $sqlArr['check_user'] = $user;       // 盤點人員
    $sqlArr['c_partno']   = $c_partno;   // 產品編號
    $sqlArr['check_qty']  = $c_qty;      // 盤點數量
    $sqlArr['c_note']     = $c_descrp;   // 備註說明
    $sqlArr['create_date']  = $dt->format('Y-m-d H:i:s');  // 建檔時間
    $insert_id = $db->kyc_insert($tbl, $sqlArr); // 取得新增資料的 id 值

    $all = array();
    $prods = array();
    $prods['c_partno']=$c_partno;
    $prods['c_note']=$c_descrp;
    $all[] = $prods;

    $all["insert_id"] = $insert_id;
    $r['responseStatus']  = "OK";
    $r['responseMessage'] = "";
    $r['responseArray']   = $all;

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 顯示 公司別+倉庫別+盤點人員+盤點日期 之盤點資料(inv_check)
#################################
function listCheckData()
{
    global $db;
    $comp_id      = $_POST["comp_id"];    // 公司別
    $c_house      = $_POST["c_house"];    // 倉庫別
    $user         = $_POST["user"];       // 盤點人員
    $check_date   = new DateTime($_POST["check_date"]); // 盤點日期
    $check_date   = $check_date->format('Y-m-d H:i:s');

    if (!$comp_id or !$user ) {
        return "FAIL";
    }

    $tbl1             = $comp_id . "_inv_check"; // 盤點異動檔
    $tbl2             = $comp_id . "_inv_stock"; // 現有庫存檔
    $searchCondition =  "a.comp_id = '{$comp_id}' AND a.c_house = '{$c_house}' AND ";
    $searchCondition .= "a.check_user = '{$user}' AND a.check_date = '{$check_date}'";

    // $sql =  "select a.* , b.barcode AS w1barcode , b.c_descrp , b.c_unit from `$tbl1` as a LEFT JOIN `$tbl2` AS b   ON a.barcode = b.barcode WHERE " . $searchCondition . " order by a.create_date DESC";

    $sql =  "select a.* , b.barcode AS w1barcode , b.c_descrp , b.c_unit from `$tbl1` as a LEFT JOIN `$tbl2` AS b  ON a.c_partno = b.c_partno WHERE " . $searchCondition . " order by a.create_date DESC";

    $result          = $db->kyc_sqlFetch_assoc($sql);
    $r   = array();
    $all = array();
    foreach ($result as $prods) {
        if($prods["barcode"] == NULL) $prods["barcode"] = "";
        $all[] = $prods;
    }
    $r['responseStatus']  = "OK";
    $r['responseMessage'] = "";
    $r['responseArray']   = $all;

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 回傳 盤點庫存檔(inv_stock) 公司別+倉庫別+盤點檔上傳日期 的摘要給使用者挑選
#################################
function listStockDataSummary()
{
    global $db;
    $r   = array();
    $comp_id = (isset($_SESSION["comp_id"])) ? $_SESSION["comp_id"] : $_POST["comp_id"];    // 公司別

    if (!$comp_id) {
        $r['responseStatus']  = "FAIL";
        $r['responseMessage'] = "";
        return json_encode($r, JSON_UNESCAPED_UNICODE);
    }

    $tbl1             = $comp_id . "_inv_stock"; // 現有庫存檔
    $searchCondition =  "comp_id = '{$comp_id}' ";

    $sql =  "select distinct comp_id , c_house , check_date from `$tbl1`  WHERE " . $searchCondition;
    // $sql =  "select distinct  comp_id , c_house , check_date from `$tbl1`  " ;
    $result          = $db->kyc_sqlFetch_assoc($sql);
    $all = array();
    $count = 0;
    foreach ($result as $datas) {
        $all[] = $datas;
        $count ++;
    }
    if($count == 0){
        $r['responseStatus']  = "FAIL";
        $r['responseMessage'] = "查不到資料";
    } else {
        $r['responseStatus']  = "OK";
        $r['responseMessage'] = "";
        $r['responseArray']   = $all;
    }

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 使用者登錄檢查
#################################
function login()
{
    $comp_id      = $_POST["comp_id"];
    $user         = $_POST["user"]; // 使用者帳號
    $pass         = $_POST["pass"]; // 使用者密碼
    $check_result = login_check_user($comp_id, $user, $pass);
    $r            = array();
    $r['responseStatus']     = $check_result;
    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 檢查帳號、密碼是否正確
# 正確返回 "SUCCESS"
# 不正確返回 "FAIL"
#################################
function login_check_user($comp_id = "", $user = "", $pass = "")
{
    global $db;
    if (!$comp_id or !$user or !$pass) {
        return "FAIL";
    }

    $tbl             = $comp_id . "_inv_user";
    $searchCondition = "`comp_id` = '{$comp_id}' AND `user` = '{$user}' ";
    $sql             = "SELECT * FROM `$tbl` WHERE " . $searchCondition;

    $result          = $db->kyc_sqlFetch_assoc($sql);
    $passHash        = "";
    $is_admin        = false;

    // if no $comp_id || $user, $result == false, then return false
    if ($result) {
        foreach ($result as $item) {
            $passHash = $item['pass'];
            $is_admin = ($item['isAdmin'] == 1) ? true : false;
        }

        if (password_verify($pass, $passHash)) {
            $_SESSION["user"]     = $user;
            $_SESSION["pass"]     = $pass;
            $_SESSION["comp_id"]  = $comp_id;
            $_SESSION["is_admin"]  = $is_admin;
            return "OK";
        }
        else {
            return "FAIL";
        }
    }
    else {
        return "FAIL";
    }
}
################################
# 使用者登出
#################################
function logout()
{
    session_destroy();
    $_SESSION = array();

    $r            = array();
    $r['responseStatus']     = "OK";
    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 查詢 現有庫存檔
#################################
function searchStock()
{
    global $db;
    $r   = array();
    $comp_id      = $_POST["comp_id"];    // 公司別
    $c_house      = $_POST["c_house"];    // 倉庫別
    $check_date   = new DateTime($_POST["check_date"]); // 盤點日期
    $check_date   = $check_date->format('Y-m-d H:i:s');
    $c_partno     = $_POST["c_partno"];   // 產品編號
    $c_descrp     = $_POST["c_descrp"];   // 產品名稱


    if (!$comp_id) {
        $r['responseStatus']  = "FAIL";
        $r['responseMessage'] = "公司別空白";
        return json_encode($r, JSON_UNESCAPED_UNICODE);
    }

    $tbl1             = $comp_id . "_inv_stock"; // 現有庫存檔
    $searchCondition =  "comp_id = '{$comp_id}' AND c_house = '{$c_house}' AND check_date = '{$check_date}'";

    $searchCondition .= ($c_partno != "") ? "AND  c_partno Like '%{$c_partno}%'" : "";
    $searchCondition .= ($c_descrp != "") ? "AND  c_descrp Like '%{$c_descrp}%'" : "";
    $sql =  "select * from `$tbl1`  WHERE " . $searchCondition;
    $result          = $db->kyc_sqlFetch_assoc($sql);
    $all = array();
    $count = 0;
    foreach ($result as $prods) {
        $all[] = $prods;
        $count ++;
    }
    if($count == 0){
        $r['responseStatus']  = "FAIL";
        $r['responseMessage'] = "查不到資料";
    } else {
        $r['responseStatus']  = "OK";
        $r['responseMessage'] = "";
        $r['responseArray']   = $all;
    }

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 匯出盤點明細表
#################################
function stockExport()
{
    global $db;

    $r = array();

    $comp_id = $_SESSION["comp_id"];    // 公司別
    $c_house = $_POST["c_house"];       // 倉庫別
    $check_date = $_POST["check_date"]; // 盤點日期

    $stockData = [];

    // 抓資料
    $tbl1 = "{$comp_id}_inv_stock";  // 現有庫存檔
    $tbl2 = "{$comp_id}_inv_check";  // 盤點異動檔
    $searchCondition =  "a.comp_id = '$comp_id' AND a.c_house = '$c_house' AND a.check_date = '$check_date'";
    $searchCondition1 =  "comp_id = '$comp_id' AND c_house = '$c_house' AND check_date = '$check_date'";

    $sql = "select a.* , b.c_partno AS w1partno , b.barcode AS w1barcode , b.check_total , b.c_note from `$tbl1` as a
    LEFT JOIN (select barcode,c_partno,c_note,sum(check_qty) as check_total
    from `$tbl2` group by c_partno) AS b
    ON a.c_partno = b.c_partno  WHERE " . $searchCondition;
    $sql .= " UNION
    select a.* , b.c_partno AS w1partno , b.barcode AS w1barcode , b.check_total , b.c_note from `$tbl1` as a
    RIGHT JOIN (select barcode,c_partno,c_note,sum(check_qty) as check_total
    from `$tbl2` WHERE {$searchCondition1} group by c_partno) AS b
    ON a.c_partno = b.c_partno ";

    $result = $db->kyc_sqlFetch_assoc($sql);

    // 寫入excel
    $wkseq = 1;
    foreach ($result as $checks) {
        $remark = "";
        if ($checks["w1barcode"] == NULL) {
            $remark = "電腦有帳；但沒有盤點資料。";
        }
        if ($checks["c_partno"] == NULL) {
            $remark = "電腦沒有帳；但有盤點資料。";
        }
        if ($checks["c_partno"] == NULL) $checks["c_partno"] = "";
        if ($checks["c_descrp"] == NULL) $checks["c_descrp"] = "";
        if ($checks["barcode"] == NULL) $checks["barcode"] = "";
        if ($checks["c_unit"] == NULL) $checks["c_unit"] = "";
        if ($checks["c_qtyst"] == NULL) $checks["c_qtyst"] = "";

        if ($checks["w1barcode"] == NULL) $checks["w1barcode"] = "";
        if ($checks["check_total"] == NULL) $checks["check_total"] = "";
        if ($checks["c_note"] == NULL) $checks["c_note"] = "";
        if ($checks["w1partno"] == NULL) $checks["w1partno"] = "";
        $checks["c_remark"] = $remark;
        // $checks["check_user"] = "user000";

        $c_seq        = $wkseq ;                 // 序號
        $c_partno     = $checks["c_partno"];     // 機種編號
        $barcode      = $checks["barcode"];      // 條碼編號
        $c_descrp     = $checks["c_descrp"];     // 產品名稱
        $c_unit       = $checks["c_unit"];       // 單位
        $c_qtyst      = $checks["c_qtyst"];      // 現有庫存
        $w1barcode    = $checks["w1barcode"];    // 盤點條碼
        $w1partno     = $checks["w1partno"];     // 盤點產品
        $check_total  = $checks["check_total"];  // 盤點數量
        $c_qtyst_diff = $check_total - $c_qtyst; // 差額
        $c_note       = $checks["c_note"];       // 盤點說明
        $c_remark     = $checks["c_remark"];     // 註記

        $tmp = [$c_seq, $c_partno, $barcode, $c_descrp, $c_unit, $c_qtyst, $w1barcode,  $w1partno,$check_total, $c_qtyst_diff, $c_note];
        array_push($stockData, $tmp);
        $wkseq++;
    }

    $r["responseResult"] = $stockData;

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 匯入現有庫存
#################################
function stockImport()
{
    global $db;

    $r = array();

    if (!empty($_FILES["excel_file"])) {
        $comp_id = $_SESSION["comp_id"];
        $c_house = $_POST["c_house"];
        $check_date = $_POST["check_date"];

        // echo "倉庫別：$c_house <br>";
        // echo "盤點日期：$check_date <br>";

        $file_name_array = explode(".", $_FILES["excel_file"]["name"]);
        $file_extension = $file_name_array[1];

        if ($file_extension == "csv" ||
            $file_extension == "xls" ||
            $file_extension == "xlsx"
        ) {
            require("../plugin/PHPExcel/IOFactory.php");

            $file_tmp_name = $_FILES["excel_file"]["tmp_name"];

            $file_type = PHPExcel_IOFactory::identify($file_tmp_name);

            // Check uploaded file encoding
            if (mb_check_encoding(file_get_contents($file_tmp_name), "BIG-5")) {
                $excel_reader = PHPExcel_IOFactory::createReader($file_type)
                    ->setInputEncoding("BIG5");
                    // ->setDelimiter(",")
                    // ->setEnclosure('"')
                    // ->setSheetIndex(0)
            } else {
                // Default using UTF-8 encoding
                $excel_reader = PHPExcel_IOFactory::createReader($file_type);
            }

            $excel_data_loaded = $excel_reader->load($file_tmp_name);
            $data_stock_work_sheet = $excel_data_loaded->getActiveSheet();
            $data_stock = $data_stock_work_sheet->toArray();

            $data_stock_length = count($data_stock);
            // Insert into `{$comp_id}_inv_stock` row by row
            for ($i = 1; $i < $data_stock_length; $i++) {
                $c_partno = $data_stock[$i][0]; // 產品編號 (機型)
                $barcode  = $data_stock[$i][1]; // 條碼編號
                $c_descrp = $data_stock[$i][2]; // 產品名稱
                $c_qtyst  = $data_stock[$i][3]; // 現有庫存
                $c_unit   = $data_stock[$i][4]; // 單位
                $c_brand  = $data_stock[$i][5]; // 廠牌
                $c_type   = $data_stock[$i][6]; // 類別

                // 將庫存資料 新增至 庫存檔
                $tbl       = $comp_id . "_inv_stock";    // 庫存檔
                $sqlArr    = array();
                $sqlArr["comp_id"]    = $comp_id;        // 公司別
                $sqlArr["c_house"]    = $c_house;        // 倉庫別
                $sqlArr["check_date"] = $check_date;     // 盤點日期
                $sqlArr["c_partno"]   = $c_partno;       // 產品編號
                $sqlArr["barcode"]    = $barcode;        // 條碼編號
                $sqlArr["c_descrp"]   = $c_descrp;       // 產品名稱
                $sqlArr["c_unit"]     = $c_unit;         // 單位
                $sqlArr["c_type"]     = $c_type;         // 類別
                $sqlArr["c_brand"]    = $c_brand;        // 廠牌
                $sqlArr["c_qtyst"]    = $c_qtyst;        // 現有庫存
                $insert_id = $db->kyc_insert($tbl, $sqlArr); // 取得新增資料的 id 值
            }

            $r['responseStatus']  = "OK";
            $r['responseMessage'] = "資料匯入成功！";
        }
        else {
            $r['responseStatus']  = "FAIL";
            $r['responseMessage'] = "檔案格式出錯，請上傳 csv, xls, xlsx 格式的檔案。";
        }
    }
    else {
        $r['responseStatus']  = "FAIL";
        $r['responseMessage'] = "沒有接收到檔案，請重新操作一次。";
    }

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
###############################
# 更新盤點數量 by 盤點異動檔 的序號
#################################
function updateCheckData()
{
    $comp_id      = $_POST["comp_id"];    // 公司別
    $c_house      = $_POST["c_house"];    // 倉庫別
    $user         = $_POST["user"];       // 盤點人員
    $check_id     = $_POST["check_id"];   // 盤點異動檔 的序號
    $check_qty    = $_POST["check_qty"];  // 更新後之 盤點數量 
    $c_note       = $_POST["c_note"];     // 更新後之 備註說明

    global $db;
    $r   = array();
    if (!$comp_id or !$check_id) {
        $r['responseStatus']  = "FAIL";
        return json_encode($r, JSON_UNESCAPED_UNICODE);
    }

    $tbl             = $comp_id . "_inv_check"; // 盤點異動檔
    $updateCondition =  "comp_id = '{$comp_id}' AND c_house = '{$c_house}' AND ";
    $updateCondition .= "check_user = '{$user}'  AND id= '{$check_id}' ";

    $sqlArr         = array();
    $sqlArr['check_qty'] = $check_qty;
    $sqlArr['c_note'] = $c_note;

    $db->kyc_sqlUpdate($tbl, $sqlArr, $updateCondition);

    $r['responseStatus']  = "OK";
    $r['responseMessage'] = "";

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}