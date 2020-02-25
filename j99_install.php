<?php
require_once "php/header.php";

$result = "";
if (isset($_POST['submit']) && isset($_POST['setup'])) {
    $setup   = $_POST['setup'];
    $company = $_POST['company'];
    for ($i = 0; $i < Count($setup); $i++) {
        switch ($setup[$i]) {
            case "01": //產生資料表
                gen_table($company);
                break;
            default:
                break;
        }
    }
}
?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>安裝設定程式</title>
  </head>
  <body>
    <h1>安裝設定程式</h1>
    <form name="form" method="post" action="">
      <input type="checkbox" value="01" name="setup[]" checked autofocus> 產生資料表<br>
      請輸入公司代碼：<input type="text" name="company">
      <p><input name="submit" type="submit" value="執行" /></p>
    </form>
    <p style="color:#FF0000;"><?php echo $result; ?></p>
    </div>
    </div>
  </body>
</html>
<?php
########################################
# 更新主程式
########################################
function gen_table($tablePrefix)
{
    $web_path = str_replace("\\", "/", dirname(__FILE__));
    global $db, $WEB;
    mk_dir($web_path . "/uploads");
    // $tablePrefix = $WEB['prefix'];
    $db->tableOperation("DROP TABLE IF EXISTS {$tablePrefix}_inv_user");
    $db->tableOperation("DROP TABLE IF EXISTS {$tablePrefix}_inv_stock");
    $db->tableOperation("DROP TABLE IF EXISTS {$tablePrefix}_inv_check");
    $db->tableOperation("DROP TABLE IF EXISTS {$tablePrefix}_inv_system");

    create_table($tablePrefix);

    return true;
}
#####################################################################################
#  建立目錄
#####################################################################################
function mk_dir($dir = "")
{
    #若無目錄名稱秀出警告訊息
    if (empty($dir)) {
        return;
    }
    #若目錄不存在的話建立目錄
    if (!is_dir($dir)) {
        umask(000);
        //若建立失敗秀出警告訊息
        mkdir($dir, 0777);
    }
}
########################################
# 建立資料表 inv_stock & inv_check
########################################
function create_table($tablePrefix = "")
{

    global $db;
    $sql = "CREATE TABLE `{$tablePrefix}_inv_user` (
      `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '建檔序號',
      `comp_id` char(20)      NOT NULL COMMENT '公司別',
      `user`    char(20)      NOT NULL COMMENT '使用者帳號',
      `pass`    varchar(255)  NOT NULL COMMENT '使用者密碼',
      `name`    varchar(255)  NOT NULL COMMENT '使用者姓名',
      `email`   varchar(255)           COMMENT '使用者Email',
      `group`   varchar(255)           COMMENT '群組',
      `big_serial`   varchar(255)      COMMENT '手機序號',
      `big_enable`  enum('0','1') default '0'  COMMENT '手機啟用',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    $db->tableOperation($sql);

    $sql = "CREATE TABLE `{$tablePrefix}_inv_stock` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '建檔序號',
    `comp_id`    char(20)      NOT NULL COMMENT '公司別',
    `c_house`    char(20)      NOT NULL COMMENT '倉庫別',
    `check_date` char(20)      NOT NULL COMMENT '盤點日期',
    `c_partno`   varchar(255)  NOT NULL COMMENT '產品編號',
    `barcode`    char(20)      NOT NULL COMMENT '絛碼編號',
    `c_descrp`   varchar(255)  NOT NULL COMMENT '產品名稱',
    `c_unit`     char(10)      NOT NULL COMMENT '單位',
    `c_qtyst`    mediumint(7)           COMMENT '現有庫存',
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    $db->tableOperation($sql);

    $sql = "CREATE TABLE `{$tablePrefix}_inv_check` (
    `id`         int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '建檔序號',
    `comp_id`    char(20)      NOT NULL COMMENT '公司別',
    `c_house`    char(20)      NOT NULL COMMENT '倉庫別',
    `check_date` char(20)      NOT NULL COMMENT '盤點日期',
    `check_user` char(20)      NOT NULL COMMENT '盤點人員',
    `c_partno`   varchar(255)  NOT NULL COMMENT '產品編號',
    `barcode`    char(20)      NOT NULL COMMENT '絛碼編號',
    `check_qty`  mediumint(7)           COMMENT '盤點數量',
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    $db->tableOperation($sql);

    $sql = "CREATE TABLE `{$tablePrefix}_inv_system` (
      `id` mediumint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '建檔序號',
      `comp_id`    char(20)      NOT NULL COMMENT '公司別',
      `c_type`     char(20)      NOT NULL COMMENT '資料類別',
      `c_desc1`    char(20)      NOT NULL COMMENT '說明一',
      `c_desc2`    char(20)      NOT NULL COMMENT '說明二',
      `c_note`     varchar(255)  NOT NULL COMMENT '備註',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
      $db->tableOperation($sql);

    return true;
}

########################################
# 檢查資料表是否存在(資料表)
########################################
function chk_isTable($tbl_name = "")
{
    global $db;
    if (!$tbl_name) {
        return;
    }

    $sql    = "SHOW TABLES LIKE '{$tbl_name}'"; //die($sql);
    $result = $db->tableOperation($sql);
    // $result = $db->query($sql) or die(printf("Error: %s <br>" . $sql, $db->sqlstate));

    if ($result->num_rows) {
        return true;
    }
    //欄位存在
    return false; //欄位不存在
}
