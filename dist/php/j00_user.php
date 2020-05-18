<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION["user"])) {
	header("location: login.php");
}
require_once "header.php";
$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
// echo '<script type="text/javascript">alert("op=' . $op . '")</script>';
// var_dump($_FILES,$_POST);

$tbl1 = $_SESSION["comp_id"] . "_inv_user"; //die($sql);
$theme = "index_1.tpl";
try
{
    switch ($op) {
        case 'user_form':
            user_form($id);
            $theme = "j00_user_form.tpl";
            break;
        case 'save_user':
            save_user($id);
            header("location: ../../index.php");
            exit;
        case 'delete_user':
            echo delete_user($id);
            exit;
        default:
            list1();
            $theme = "j00_user_list.tpl";
            $op = 'list';
            break;
    }
} catch (exception $e) {
    $error = $e->getMessage();
}

//結果送至樣板
$page_title = "雲端行動盤點系統";
// $theme = "index_1.tpl";

$smartyTpl->assign('op', $op);
$smartyTpl->assign('g2p', $g2p);
$smartyTpl->assign('error', $error);
$smartyTpl->assign('page_title', $page_title);
$smartyTpl->assign('content', $content);
$smartyTpl->display($theme);

#################################
#
# 使用者註冊表單
#################################
function user_form($id = "") {
  global $db, $smartyTpl , $tbl1;

  #取得預設值
  if ($id) {
    #編輯
    $searchCondition =  "`id`='{$id}'";
    $sql = "select * from `$tbl1` where ". $searchCondition;
    $result  = $db->kyc_sqlFetch_assoc($sql);
    $row  = array();
    foreach ($result as $datas) {
        $datas['form_title']  = "修改";
        $row = $datas;
    }
  } else {
    #新增
    $row = array();
    #預設值設定
    $row['id'] =  "";
    $row['comp_id'] =  "";
    $row['user'] = "";
    $row['pass'] =  "";
    $row['name'] =  "";
    $row['email'] = "";
    $row['isAdmin'] =  0;
    $row['form_title'] = "新增";
  }

  #把變數送至樣板
  $smartyTpl->assign('row', $row);
}
#################################
# 表單
# 儲存註冊資料
#################################
function save_user($id = "")
{
    global  $db , $tbl1;
    $pass  = password_hash($_POST["pass"], PASSWORD_DEFAULT);
    $id=$_POST["id"];
    
    $sqlArr=array();
    $sqlArr['user']       = $_POST["user"];
    $sqlArr['name']       = $_POST["name"];
    $sqlArr['pass']       = $pass;
    $sqlArr['email']      = $_POST["email"];
    $sqlArr['isAdmin']      = $_POST["isAdmin"];
    $sqlArr['comp_id']    = $_SESSION["comp_id"];

    #取得預設值
    if ($id) {
    //   $db->kyc_sqlReplace($tbl1, $sqlArr, "UPDATE");
      $db->kyc_sqlUpdate($tbl1, $sqlArr, " id = $id");
      $return_id = $id;
    } else {
      $return_id = $db->kyc_sqlReplace($tbl1, $sqlArr, "ADD");
    }

    return $return_id;

}
#################################
# 表單
# 顯示使用者註冊資料
#################################
function list1()
{
    global $smartyTpl, $db,  $tbl1;
    $comp_id = $_SESSION["comp_id"];
    $Condition=" `comp_id`='{$comp_id}' ";
    $sql="SELECT * FROM `{$tbl1}`  WHERE {$Condition} ORDER BY `user` ";

    include_once "../../plugin/PageBar.php";
    $mysqli = new mysqli(_DB_HOST, _DB_USER, _DB_PASS, _DB_NAME);

    $PageBar = getPageBar($mysqli, $sql, _EVERY_PAGE, _EVERY_TOOLBAR);

    $bar = $PageBar['bar'];
    $sql = $PageBar['sql'];
    $total = $PageBar['total'];


    //送至資料庫
    $result   = $db->kyc_sqlFetch_assoc($sql);

    //取回資料
    $users    = array();
    foreach ($result as $datas) {
        $users[] = $datas;
        // $users["isAdmin"] = 1;
    }

    $smartyTpl->assign('every_page', _EVERY_PAGE);
    $smartyTpl->assign('bar', $bar);
    $smartyTpl->assign('total', $total);
    $smartyTpl->assign('users', $users);

}
//刪除 USER
function delete_user($id)
{
    global $smartyTpl, $db,  $tbl1;
    $deleteCondition = "id= '{$id}'";
    $return_id       = $db->kyc_sqlDelete($tbl1, $deleteCondition);
    return "OK";

}
