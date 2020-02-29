<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

require_once "header.php";


if (!isset($_SESSION["user"])) {
	header("location: $_KYC_URL_ROOT/login.php");
}

$op = (isset($_REQUEST["op"])) ? $_REQUEST["op"] : "";

$tbl1 = $_SESSION["comp_id"] . "_inv_stock";
$theme = "j00_stock_import.tpl";
try
{
    switch ($op) {
        case "stockImport":
            stock_import($_REQUEST, $_FILES);
            $theme = "j00_stock_import.tpl";
            break;
        default:
            $smartyTpl->assign("responseCss", "");
            $smartyTpl->assign("responseMessage", "");
            $theme = "j00_stock_import.tpl";
            break;
    }
} catch (exception $e) {
    $error = $e->getMessage();
}

//結果送至樣板
$page_title = "雲端行動盤點系統";
// $theme = "index_1.tpl";

require_once "footer.php";
#################################
#
# 使用者註冊表單
#################################
function stock_import($_post, $_files) {
    global $_KYC_URL_ROOT, $_KYC_DOCUMENT_ROOT, $db, $smartyTpl;

    if (!empty($_files["excel_file"])) {
        $comp_id = $_SESSION["comp_id"];
        $c_house = $_post["c_house"];
        $check_date = $_post["check_date"];

        echo "倉庫別：$c_house <br>";
        echo "盤點日期：$check_date <br>";

        // $connect = mysqli_connect("localhost", "root", "", "testing");
        $file_name_array = explode(".", $_files["excel_file"]["name"]);
        $file_extension = $file_name_array[1];

        if ($file_extension == "csv" ||
            $file_extension == "xls" ||
            $file_extension == "xlsx"
        ) {
            require("$_KYC_DOCUMENT_ROOT/plugin/PHPExcel/IOFactory.php");
            // require("/Applications/XAMPP/xamppfiles/htdocs/_kyc/inventory/plugin/PHPExcel/IOFactory.php");

            $file_tmp_name = $_files["excel_file"]["tmp_name"];

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

            $alertCss = "alert-success";
            $msg = "資料匯入成功！";
        }
        else {
            $alertCss  = "alert-danger";
            $msg = "檔案格式出錯，請上傳 csv, xls, xlsx 格式的檔案。";
        }
    }
    else {
        $alertCss  = "alert-danger";
        $msg = "沒有接收到檔案，請重新操作一次。";
    }

    $smartyTpl->assign("responseCss", "$alertCss d-block");
    $smartyTpl->assign("responseMessage", $msg);
}
