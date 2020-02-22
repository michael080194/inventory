<?
header('Access-Control-Allow-Origin: *'); //
header("Content-Type:text/plain; charset=utf-8"); // text/html
include_once "../../kycgoldSqlConn.php"; // kycgoldSqlConn.php　放在網頁空間最上層
include_once "function_sql.php"; // kycgoldSqlConn.php　放在網頁空間最上層

$target = $_POST["target"]; // 操作對象
// die(var_dump($_POST));
// die("table1=" . $_POST["table1"]);

switch ($target) {
    case 'a00_user_login':
        echo login($company_id, $uname, $pass);
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
    $company_id          = $_POST["company_id"];
    $uname               = $_POST["uname"]; // 使用者帳號
    $pass                = $_POST["pass"]; // 使用者密碼
    $check_result        = check_user($company_id, $uname, $pass);
    $r                   = array();
    $r['responseStatus'] = $check_result;
    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 檢查帳號、密碼是否正確
# 正確返回 "OK"
# 不正確返回 "FAIL"
#################################
function check_user($company_id = "", $uname = "", $pass = "")
{
    global $xoopsDB;
    if (!$uname or !$pass) {
        return;
    }

    $searchCondition = "`company_id` = '{$company_id}' AND `user` = '{$uname}' ";
    $sql             = "SELECT * FROM `a00_user` WHERE " . $searchCondition;
    $result          = sqlExcuteForSelectData($sql);
    // $row             = mysqli_fetch_array($result);
    $passmd5 = "";
    while ($users = sqlFetch_assoc($result)) {
        $passmd5 = $users['pass'];
    }
    // die(password_hash("456", PASSWORD_DEFAULT));
    // die($passmd5);
    // $totalCount = $row["user"];
    if (password_verify($pass, $passmd5)) {
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
    $file = "uploads/" . $fileName . strtotime("now") . "." . $fileType;
    $f    = fopen($file, 'w'); //以寫入方式開啟文件
    fwrite($f, $msgText); //將新的資料寫入到原始的文件中
    fclose($f);
}
