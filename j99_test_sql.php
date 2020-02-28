<?php
require_once "dist/php/header.php";

$result = "";
if (isset($_POST['submit'])) {
  //  insert1();  // 新增一筆資料
  //  update1();  // update data
  //  delete1();  // delete data
   gen_data(); //產生測試資料
}
?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>盤點系統 Test SQL 語法</title>
  </head>
  <body>
    <h1>Test SQL</h1>
    <form name="form" method="post" action="">
      <p><input name="submit" type="submit" value="執行" /></p>
    </form>
    <p style="color:#FF0000;"><?php echo $result; ?></p>
    </div>
    </div>
  </body>
</html>
<?php
########################################
# insert
########################################
function insert1()
{

    global $db;
    $save_type = "ADD";
    $tbl       = "1284_inv_user"; //die($sql);

    $sqlArr          = array();
    $sqlArr['comp_id']  = "1284";
    $sqlArr['user']  = "1";
    $sqlArr['name']  = "michael";
    $sqlArr['pass']  = password_hash("1", PASSWORD_DEFAULT);
    $sqlArr['email'] = "michael080194@gmail.com";

    #取得預設值
    // $return_id = $db->insert($tbl, $sqlArr);

    if ($save_type == "ADD") {
        $return_id = $db->kyc_sqlReplace($tbl, $sqlArr, "ADD");
    } else {
        $sqlArr['id'] = 3;
        $db->kyc_sqlReplace($tbl, $sqlArr, "UPDATE");
    }

    return true;
}
########################################
# update
########################################
function update1()
{

    global $db;

    $tbl = "1284_inv_user"; //die($sql);

    $sqlArr         = array();
    $sqlArr['user'] = "michaelYY";
    $sqlArr['name'] = "CHANGYY";

    $user            = "1";
    $name            = "michael";
    $updateCondition = "user = '{$user}'  AND name= '{$name}'";
    $return_id       = $db->kyc_sqlUpdate($tbl, $sqlArr, $updateCondition);

    return true;
}
########################################
# delete
########################################
function delete1()
{

    global $db;

    $tbl = "1284_inv_user"; //die($sql);

    $user            = "michaelYY";
    $id              = 3;
    $deleteCondition = "user = '{$user}'  AND id= '{$id}'";
    $return_id       = $db->kyc_sqlDelete($tbl, $deleteCondition);

    return true;
}
########################################
# 產生測試資料
########################################
function gen_data()
{
    global $db;
    $tbl       = "1284_inv_user";
    $sqlArr          = array();
    $sqlArr['comp_id']  = "1284";
    $sqlArr['user']  = "1";
    $sqlArr['name']  = "michael";
    $sqlArr['pass']  = password_hash("1", PASSWORD_DEFAULT);
    $sqlArr['email'] = "michael@gmail.com";
    $sqlArr['isAdmin'] = 1;
    $return_id = $db->kyc_insert($tbl, $sqlArr);

    $sqlArr          = array();
    $sqlArr['comp_id']  = "1284";
    $sqlArr['user']  = "2";
    $sqlArr['name']  = "may";
    $sqlArr['pass']  = password_hash("2", PASSWORD_DEFAULT);
    $sqlArr['email'] = "may@gmail.com";
    $sqlArr['isAdmin'] = 0;
    $return_id = $db->kyc_insert($tbl, $sqlArr);

    $tbl       = "1284_inv_stock";
    $sqlArr          = array();
    $sqlArr['comp_id']  = "1284";
    $sqlArr['c_house']  = "01";
    $sqlArr['check_date']  = "2020-02-24";
    $sqlArr['c_partno']  = "NR001";
    $sqlArr['barcode'] = "471001";
    $sqlArr['c_descrp']  = "國際牌電視機";
    $sqlArr['c_unit'] = "台";
    $sqlArr['c_qtyst'] = 10.5;
    $return_id = $db->kyc_insert($tbl, $sqlArr);

    $sqlArr          = array();
    $sqlArr['comp_id']  = "1284";
    $sqlArr['c_house']  = "01";
    $sqlArr['check_date']  = "2020-02-24";
    $sqlArr['c_partno']  = "SW777";
    $sqlArr['barcode'] = "471002";
    $sqlArr['c_descrp']  = "日立牌冷氣機";
    $sqlArr['c_unit'] = "台";
    $sqlArr['c_qtyst'] = 5123456;
    $return_id = $db->kyc_insert($tbl, $sqlArr);

    return true;
}