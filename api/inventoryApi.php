<?
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
        echo login();
        break;
    case 'logout':
        echo logout();
        break;
    case 'insertByBarcode':
        echo insertByBarcode();
        break;
    case 'updateCheckData':
        echo updateCheckData(); // 更新盤點數量 by 盤點異動檔 的序號
        break;
    case 'deleteCheckData':
        echo deleteCheckData(); // 刪除 盤點異動檔 資料
        break;
    case 'listCheckData':
        echo listCheckData(); // 顯示 盤點異動檔 資料
        break;
    default:
        $r                   = array();
        $r['responseStatus'] = "FAIL";
        echo json_encode($r, JSON_UNESCAPED_UNICODE);
        break;
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
    $tbl       = $comp_id . "_inv_check"; //盤點異動檔
    $sqlArr    = array();
    $sqlArr['comp_id']    = $comp_id;    // 公司別
    $sqlArr['c_house']    = $c_house ;   // 倉庫別
    $sqlArr['check_date'] = $check_date; // 盤點日期
    $sqlArr['check_user'] = $user;       // 盤點人員
    $sqlArr['c_partno']   = $c_partno;   // 產品編號
    $sqlArr['barcode']    = $barcode;    // 條碼
    $sqlArr['check_qty']  = $c_qty;      // 盤點人員
    $insert_id = $db->kyc_insert($tbl, $sqlArr); // 取得新增資料的 id 值

    $all["insert_id"] = $insert_id;
    $r['responseStatus']  = "OK";
    $r['responseMessage'] = "";
    $r['responseArray']   = $all;

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
    $check_id     = $_POST["check_id"];  // 盤點異動檔 的序號
    $check_qty    = $_POST["check_qty"];  // 更新後之 盤點數量

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

    $db->kyc_sqlUpdate($tbl, $sqlArr, $updateCondition);

    $r['responseStatus']  = "OK";
    $r['responseMessage'] = "";

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
################################
# 查詢盤點資料
#################################
function listCheckData()
{
    global $db;
    $comp_id      = $_POST["comp_id"];    // 公司別
    $c_house      = $_POST["c_house"];    // 倉庫別
    $user         = $_POST["user"];       // 盤點人員

    if (!$comp_id or !$user ) {
        return "FAIL";
    }

    $tbl1             = $comp_id . "_inv_check"; // 盤點異動檔
    $tbl2             = $comp_id . "_inv_stock"; // 現有庫存檔
    $searchCondition =  "a.comp_id = '{$comp_id}' AND a.c_house = '{$c_house}' AND ";
    $searchCondition .= "a.check_user = '{$user}' ";

    $sql =  "select a.* , b.barcode AS w1barcode , b.c_descrp , b.c_unit from `$tbl1` as a LEFT JOIN `$tbl2` AS b   ON a.barcode = b.barcode WHERE " . $searchCondition;

    $result          = $db->kyc_sqlFetch_assoc($sql);
    $r   = array();
    $all = array();
    foreach ($result as $prods) {
        $all[] = $prods;
    }
    $r['responseStatus']  = "OK";
    $r['responseMessage'] = "";
    $r['responseArray']   = $all;

    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
// ########################################################################
// #  產生訊息檔,以供 debug
// ########################################################################
// function genMsgFile($fileName = "msg", $fileType = "txt", $msgText = "")
// {
//     // genMsgFile("001_", "txt", "AAAA");
//     $file = "../dist/uploads/" . $fileName . "_" . strtotime("now") . "." . $fileType;
//     $f    = fopen($file, 'w'); //以寫入方式開啟文件
//     fwrite($f, $msgText); //將新的資料寫入到原始的文件中
//     fclose($f);
// }