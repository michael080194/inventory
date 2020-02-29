<?php
require_once "index_check.php";
require_once "header.php";

$op = (isset($_REQUEST["op"])) ? $_REQUEST["op"] : "";

$tbl1 = $_SESSION["comp_id"] . "_inv_stock";
$theme = "j00_stock_export.tpl";
try
{
    switch ($op) {
        case "stockExport":
            return stock_export();
            $theme = "j00_stock_export.tpl";
            break;
        default:
            $theme = "j00_stock_export.tpl";
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
#
#################################
function stock_export() {
    global $_KYC_URL_ROOT, $_KYC_DOCUMENT_ROOT, $db, $smartyTpl;
    $comp_id   = $_SESSION["comp_id"];    // 公司別
    $c_house   = $_REQUEST["c_house"];    // 倉庫別

    $tbl2             = $comp_id . "_inv_check"; // 盤點異動檔
    $tbl1             = $comp_id . "_inv_stock"; // 現有庫存檔
    $searchCondition =  "a.comp_id = '{$comp_id}' AND a.c_house = '{$c_house}' AND ";

    $sql = "select a.* , b.c_partno AS w1partno , b.barcode AS w1barcode , b.check_total , b.c_note from `$tbl1` as a
    LEFT JOIN (select barcode,c_partno,c_note,sum(check_qty) as check_total
    from `$tbl2` group by barcode) AS b
    ON a.barcode = b.barcode
    UNION
    select a.* , b.c_partno AS w1partno , b.barcode AS w1barcode , b.check_total , b.c_note from `$tbl1` as a
    RIGHT JOIN (select barcode,c_partno,c_note,sum(check_qty) as check_total
    from `$tbl2` group by barcode) AS b
    ON a.barcode = b.barcode";

    $result          = $db->kyc_sqlFetch_assoc($sql);

    $r   = array();
    $all = array();
    $style = "<style>
    table {
      font-family: arial, sans-serif;
      border-collapse: collapse;
      width: 100%;
    }
    
    td, th {
      border: 1px solid #dddddd;
      text-align: left;
      padding: 8px;
    }
    
    tr:nth-child(even) {
      background-color: #dddddd;
    }
    </style>";
    echo $style;
    echo "<table>";
    $tableHead = "
        <tr>
            <th>機種編號</th>
            <th>絛碼編號</th>
            <th>產品名稱</th>
            <th>單位</th>
            <th>現有庫存</th>
            <th>盤點絛碼</th>
            <th>盤點數量</th>
            <th>盤盈盤虧</th>
            <th>盤點說明</th>
            <th>註記</th>
        </tr>
    ";
    echo $tableHead;
    $all = array();
    foreach ($result as $checks) {
        // "id": "3",
        // "comp_id": "1284",
        // "c_house": "01",
        // "check_date": "2020-02-24 00:00:00",
        $c_remark = "";
        if( $checks["w1barcode"] == NULL){
            $c_remark = "電腦有帳;但沒有盤點資料";
        }
        if( $checks["barcode"] == NULL){
            $c_remark = "電腦沒有帳;但有盤點資料";
        }
        $checks["c_remark"] = $c_remark;
        echo "<tr>";
        echo "<td>" . $checks["c_partno"] . "</td>";
        echo "<td>" . $checks["barcode"] . "</td>";
        echo "<td>" . $checks["c_descrp"] . "</td>";
        echo "<td>" . $checks["c_unit"] . "</td>";
        echo "<td>" . $checks["c_qtyst"] . "</td>";
        echo "<td>" . $checks["w1barcode"] . "</td>";
        echo "<td>" . $checks["check_total"] . "</td>";
        echo "<td>" . ($checks["check_total"]-$checks["c_qtyst"]) . "</td>";
        echo "<td>" . $checks["c_note"] . "</td>";
        echo "<td>" . $c_remark . "</td>";
        echo "</tr>";
        $all[] = $checks;
    }
    echo "</table>";
    // echo json_encode($all, JSON_UNESCAPED_UNICODE);
}
