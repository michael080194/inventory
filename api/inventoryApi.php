<?
header('Access-Control-Allow-Origin: *'); //
header("Content-Type:text/plain; charset=utf-8"); // text/html
require_once "../php/config.php";
require_once '../include/kyc_db.php';

$db = new db(_DB_HOST, _DB_USER, _DB_PASS, _DB_NAME);

$target = $_POST["target"]; // 操作對象
switch ($target) {
    case 'login':
        echo login();
        break;
    default:
        $r                   = array();
        $r['responseStatus'] = "FAIL";
        echo json_encode($r, JSON_UNESCAPED_UNICODE);
        break;
}
#####################################################################################
function kyc_security($str1)
{
    return addslashes($str1);
}
################################
# 使用者登錄檢查
#################################
function login()
{
    $comp_id      = $_POST["comp_id"];
    $user         = $_POST["user"]; // 使用者帳號
    $pass         = $_POST["pass"]; // 使用者密碼
    $check_result = check_user($comp_id, $user, $pass);
    $r            = array();
    $r['msg']     = $check_result;
    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 檢查帳號、密碼是否正確
# 正確返回 "OK"
# 不正確返回 "FAIL"
#################################
function check_user($comp_id = "", $user = "", $pass = "")
{
    global $db;
    if (!$comp_id or !$user or !$pass) {
        return "FAIL";
    }
    $tbl             = $comp_id . "_inv_user";
    $searchCondition = "`comp_id` = '{$comp_id}' AND `user` = '{$user}' ";
    $sql             = "SELECT * FROM `$tbl` WHERE " . $searchCondition;
    $result          = $db->sqlFetch_assoc($sql);
    $passHash        = "";
    foreach ($result as $item) {
        $passHash = $item['pass'];
    }

    if (password_verify($pass, $passHash)) {
        return "SUCCESS";
    } else {
        return "FAIL";
    }
}
########################################################################
#  產生訊息檔,以供 debug
########################################################################
function genMsgFile($fileName = "msg", $fileType = "txt", $msgText = "")
{
    global $xoopsDB;
    $file = "../uploads/" . $fileName . "_" . strtotime("now") . "." . $fileType;
    $f    = fopen($file, 'w'); //以寫入方式開啟文件
    fwrite($f, $msgText); //將新的資料寫入到原始的文件中
    fclose($f);
}
