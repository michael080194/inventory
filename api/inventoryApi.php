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
    case 'deleteCheckData':
        echo deleteCheckData(); // 刪除 盤點異動檔 資料
        break;
    case 'insertByBarcode':
        echo insertByBarcode();
        break;
    case 'insertBySearchStock':
        echo insertBySearchStock();
        break;
    case 'login':
        echo login();
        break;
    case 'logout':
        echo logout();
        break;
    case 'listCheckData':
        echo listCheckData(); // 顯示 盤點異動檔 資料
        break;
    case 'searchStock':
        echo searchStock(); // 查詢 現有庫存檔
        break;
    case 'updateCheckData':
        echo updateCheckData(); // 更新盤點數量 by 盤點異動檔 的序號
        break;
    case 'stockExport':
        echo stockExport();
        break;
    case 'stockExportFileDelete':
        echo stockExportFileDelete();
        break;
    case 'stockImport':
        echo stockImport();
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
    if (!$comp_id or !$barcode) {
        $r   = array();
        $r['responseStatus']  = "FAIL";
        return json_encode($r, JSON_UNESCAPED_UNICODE);
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


    if (!$comp_id ) {
        $r['responseStatus']  = "FAIL";
        $r['responseMessage'] = "";
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
    $tbl2 = "{$comp_id}_inv_check";  // 盤點異動檔
    $tbl1 = "{$comp_id}_inv_stock";  // 現有庫存檔
    $searchCondition =  "a.comp_id = '$comp_id' AND a.c_house = '$c_house' AND ";
    $sql = "select a.* , b.c_partno AS w1partno , b.barcode AS w1barcode , b.check_total , b.c_note from `$tbl1` as a
    LEFT JOIN (select barcode,c_partno,c_note,sum(check_qty) as check_total
    from `$tbl2` group by barcode) AS b
    ON a.barcode = b.barcode
    UNION
    select a.* , b.c_partno AS w1partno , b.barcode AS w1barcode , b.check_total , b.c_note from `$tbl1` as a
    RIGHT JOIN (select barcode,c_partno,c_note,sum(check_qty) as check_total
    from `$tbl2` group by barcode) AS b
    ON a.barcode = b.barcode";
    $result = $db->kyc_sqlFetch_assoc($sql);

    // 寫入excel
    foreach ($result as $checks) {
        $remark = "";
        if ($checks["w1barcode"] == NULL) {
            $remark = "電腦有帳；但沒有盤點資料。";
        }
        if ($checks["barcode"] == NULL) {
            $remark = "電腦沒有帳；但有盤點資料。";
        }
        $checks["c_remark"] = $remark;
        $checks["check_user"] = "user000";

        $c_partno     = $checks["c_partno"];     // 機種編號
        $barcode      = $checks["barcode"];      // 條碼編號
        $c_descrp     = $checks["c_descrp"];     // 產品名稱
        $c_unit       = $checks["c_unit"];       // 單位
        $c_qtyst      = $checks["c_qtyst"];      // 現有庫存
        $w1barcode    = $checks["w1barcode"];    // 盤點條碼
        $check_total  = $checks["check_total"];  // 盤點數量
        $c_qtyst_diff = $check_total - $c_qtyst; // 差額
        $c_note       = $checks["c_note"];       // 盤點說明
        $check_user   = $checks["check_user"];   // 盤點人員
        $c_remark     = $checks["c_remark"];     // 註記

        $tmp = [$c_partno, $barcode, $c_descrp, $c_unit, $c_qtyst, $w1barcode, $check_total, $c_qtyst_diff, $c_note, $check_user, $c_remark];
        array_push($stockData, $tmp);
    }

    $r["responseResult"] = $stockData;

// 匯出並下載
/*
    require("../plugin/PHPExcel/IOFactory.php");
    $objPHPExcel = new PHPExcel();

    // 設定excel
    $excelRow = 1;
    $objPHPExcel->getActiveSheet()
        ->setCellValue("A$excelRow", "公司別：$comp_id")
        ->setCellValue("B$excelRow", "倉庫別：$c_house")
        ->setCellValue("C$excelRow", "盤點日期：$check_date");

    $excelRow++;
    $objPHPExcel->getActiveSheet()
        ->setCellValue("A$excelRow", "機種編號")
        ->setCellValue("B$excelRow", "條碼編號")
        ->setCellValue("C$excelRow", "產品名稱")
        ->setCellValue("D$excelRow", "單位")
        ->setCellValue("E$excelRow", "現有庫存")
        ->setCellValue("F$excelRow", "盤點條碼")
        ->setCellValue("G$excelRow", "盤點數量")
        ->setCellValue("H$excelRow", "差額")
        ->setCellValue("I$excelRow", "盤點說明")
        ->setCellValue("J$excelRow", "註記");

    // 抓資料
    $tbl2 = "{$comp_id}_inv_check";  // 盤點異動檔
    $tbl1 = "{$comp_id}_inv_stock";  // 現有庫存檔
    $searchCondition =  "a.comp_id = '$comp_id' AND a.c_house = '$c_house' AND ";
    $sql = "select a.* , b.c_partno AS w1partno , b.barcode AS w1barcode , b.check_total , b.c_note from `$tbl1` as a
    LEFT JOIN (select barcode,c_partno,c_note,sum(check_qty) as check_total
    from `$tbl2` group by barcode) AS b
    ON a.barcode = b.barcode
    UNION
    select a.* , b.c_partno AS w1partno , b.barcode AS w1barcode , b.check_total , b.c_note from `$tbl1` as a
    RIGHT JOIN (select barcode,c_partno,c_note,sum(check_qty) as check_total
    from `$tbl2` group by barcode) AS b
    ON a.barcode = b.barcode";
    $result = $db->kyc_sqlFetch_assoc($sql);

    // 寫入excel
    foreach ($result as $checks) {
        $remark = "";
        if ($checks["w1barcode"] == NULL) {
            $remark = "電腦有帳；但沒有盤點資料。";
        }
        if ($checks["barcode"] == NULL) {
            $remark = "電腦沒有帳；但有盤點資料。";
        }
        $checks["c_remark"] = $remark;
        $checks["check_user"] = "user000";

        $c_partno     = $checks["c_partno"];     // 機種編號
        $barcode      = $checks["barcode"];      // 條碼編號
        $c_descrp     = $checks["c_descrp"];     // 產品名稱
        $c_unit       = $checks["c_unit"];       // 單位
        $c_qtyst      = $checks["c_qtyst"];      // 現有庫存
        $w1barcode    = $checks["w1barcode"];    // 盤點條碼
        $check_total  = $checks["check_total"];  // 盤點數量
        $c_qtyst_diff = $check_total - $c_qtyst; // 差額
        $c_note       = $checks["c_note"];       // 盤點說明
        $check_user   = $checks["check_user"];   // 盤點人員
        $c_remark     = $checks["c_remark"];     // 註記

        $excelRow++;
        $objPHPExcel->getActiveSheet()
            ->setCellValue("A$excelRow", $c_partno)
            ->setCellValue("B$excelRow", $barcode)
            ->setCellValue("C$excelRow", $c_descrp)
            ->setCellValue("D$excelRow", $c_unit)
            ->setCellValue("E$excelRow", $c_qtyst)
            ->setCellValue("F$excelRow", $w1barcode)
            ->setCellValue("G$excelRow", $check_total)
            ->setCellValue("H$excelRow", $c_qtyst_diff)
            ->setCellValue("I$excelRow", $c_note)
            ->setCellValue("J$excelRow", $check_user)
            ->setCellValue("K$excelRow", $c_remark);
        //     ->setCellValue("D$excelRow", PHPExcel_Shared_Date::stringToExcel($rec["date_of_birth"]))
        //     ->setCellValue("E$excelRow", $rec["salary"]);
        // $objPHPExcel->getActiveSheet()->getStyle("D" . $excelRow)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX15);
        // $objPHPExcel->getActiveSheet()->getStyle("E" . $excelRow)->getNumberFormat()->setFormatCode("£#,##0.00");
    }

    // 輸出excel
    // 前提：允許 extensions `php_xmlrpc.dll`, `php_xsl.dll`, `php_zip.dll`
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "CSV");
    $objWriter->save("../dist/php/{$check_date}_{$c_house}_盤點結果明細表.csv");
*/
    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
################################
# 刪除匯出的盤點明細表(省空間)
#################################
function stockExportFileDelete()
{
    $r = array();
    $filename = $_POST["filename"];

    if (!unlink("../dist/php/$filename")) {
        $r["responseStatus"] = "FAIL";
    }
    else {
        $r["responseStatus"] = "OK";
    }

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