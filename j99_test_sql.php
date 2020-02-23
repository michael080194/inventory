<?php
require_once "php/header.php";

$result = "";
if (isset($_POST['submit'])) {
    display1();
}
?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Test SQL</title>
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
    $save_type = "addaa";
    $tbl       = "12846778_inv_user"; //die($sql);

    $sqlArr          = array();
    $sqlArr['user']  = "michael";
    $sqlArr['name']  = "WANG";
    $sqlArr['pass']  = password_hash("123", PASSWORD_DEFAULT);
    $sqlArr['email'] = "kyc168.com@msa.hinet.net";

    #取得預設值
    // $return_id = $db->insert($tbl, $sqlArr);

    if ($save_type == "add") {
        $return_id = $db->sqlReplace($tbl, $sqlArr, "ADD");
    } else {
        $sqlArr['id'] = 3;
        $db->sqlReplace($tbl, $sqlArr, "UPDATE");
    }

    return true;
}
########################################
# update
########################################
function update1()
{

    global $db;

    $tbl = "12846778_inv_user"; //die($sql);

    $sqlArr         = array();
    $sqlArr['user'] = "michaelYY";
    $sqlArr['name'] = "CHANGYY";

    $user            = "michael";
    $name            = "CHANG";
    $updateCondition = "user = '{$user}'  AND name= '{$name}'";
    $return_id       = $db->sqlUpdate($tbl, $sqlArr, $updateCondition);

    return true;
}
########################################
# delete
########################################
function delete1()
{

    global $db;

    $tbl = "12846778_inv_user"; //die($sql);

    $user            = "michael";
    $id              = 6;
    $deleteCondition = "user = '{$user}'  AND id= '{$id}'";
    $return_id       = $db->sqlDelete($tbl, $deleteCondition);

    return true;
}
########################################
# display
########################################
function display1()
{

    global $db;

    $tbl = "12846778_inv_user"; //die($sql);

    $id     = 3;
    $sql    = "SELECT * FROM `$tbl` WHERE `id` > '{$id}'";
    $result = $db->sqlFetch_assoc($sql);

    $all = array();
    foreach ($result as $item) {
        $arr1         = array();
        $arr1['id']   = $item['id'];
        $arr1['user'] = $item['user'];
        $arr1['name'] = $item['name'];
        $all[]        = $arr1;

        echo "id=" . $item['id'] . "<br>";
        echo "user=" . $item['user'] . "<br>";
        echo "name=" . $item['name'] . "<br>";
    }
    print_r($all);
    return true;
}
