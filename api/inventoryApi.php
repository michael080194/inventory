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
    case 'a00_mstock':
        echo a00_mstock();
        break;
    case 'phone_a01_sale_head':
        echo phone_a01_sale_head();
        break;
    case 'phone_a01_sale_detail':
        echo phone_a01_sale_detail();
        break;
    case "upload": // 接收從 手機 傳來的資料及圖片
        echo upload();
        exit;
    default:
        $r                   = array();
        $r['responseStatus'] = "FAIL";
        echo json_encode($r, JSON_UNESCAPED_UNICODE);
        break;
}
#####################################################################################
#  銷貨彙總資料維誰
#####################################################################################
function phone_a01_sale_head()
{
    global $xoopsDB;
    $company_id = $_POST["company_id"]; // 公司別代碼
    $op         = $_POST["op"]; //
    $uploaddata = $_POST["uploaddata"]; //
    $id         = $_POST["id"]; // 銷貨單號
    $table1     = "phone_a01_sale_head";
    $r          = array();

    switch ($op) {
        case 'get_all': // 抓取所有銷貨單資料
            $searchCondition = "`company_id` = '{$company_id}' ";
            $sql             = "SELECT * FROM `{$table1}` WHERE " . $searchCondition . "  ORDER BY id DESC";
            $result          = sqlExcuteForSelectData($sql);
            $r               = array();
            $all             = array();
            while ($datas = sqlFetch_assoc($result)) {
                $all[] = $datas;
            }
            $r['responseStatus']  = "SUCCESS";
            $r['responseMessage'] = "";
            $r['responseArray']   = $all;
            return json_encode($r, JSON_UNESCAPED_UNICODE);
        case 'get_single_id': // 抓取某一筆銷貨單號之資料
            $searchCondition = "company_id = '{$company_id}' AND id = '{$id}' ";
            $sql             = "SELECT * FROM `{$table1}` WHERE " . $searchCondition;

            $result = sqlExcuteForSelectData($sql);
            $r      = array();
            $all    = array();
            while ($datas = sqlFetch_assoc($result)) {
                $all[] = $datas;
            }
            $r['responseStatus']  = "SUCCESS";
            $r['responseMessage'] = "";
            $r['responseArray']   = $all;
            return json_encode($r, JSON_UNESCAPED_UNICODE);
        case 'del': // 刪除
            // 刪除彙總資料
            $searchCondition = "`company_id` = '{$company_id}' AND `id` = '{$id}'";
            sqlDelete($table1, $searchCondition);

            // 刪除明細 資料
            $searchCondition = "`company_id` = '{$company_id}' AND `up_id` = '{$id}'";
            sqlDelete("phone_a01_sale_detail", $searchCondition);

            $r['responseStatus']  = "SUCCESS delete";
            $r['responseMessage'] = "";
            $r['responseArray']   = [];
            return json_encode($r, JSON_UNESCAPED_UNICODE);
        case 'insert': // 新增
            $json                 = json_decode($uploaddata);
            $sqlArr               = array();
            $sqlArr["company_id"] = $company_id;
            $sqlArr["createdate"] = date_create()->format('Y-m-d H:i:s');

            foreach ($json as $key => $value) {
                $sqlArr[$key] = kyc_security($value);
            }
            sqlInsert($table1, $sqlArr);
            $return_id = $xoopsDB->insert_id;

            $r['responseStatus']  = "SUCCESS";
            $r['responseMessage'] = $return_id;
            $r['responseArray']   = [];
            return json_encode($r, JSON_UNESCAPED_UNICODE);
        case 'update': // 修改
            $searchCondition      = "`company_id` = '{$company_id}' AND `id` = '{$id}'";
            $json                 = json_decode($uploaddata);
            $sqlArr               = array();
            $sqlArr["company_id"] = $company_id;
            $sqlArr["createdate"] = date_create()->format('Y-m-d H:i:s');

            foreach ($json as $key => $value) {
                $sqlArr[$key] = kyc_security($value);
            }
            sqlUpdate($table1, $sqlArr, $searchCondition);

            $r['responseStatus']  = "SUCCESS";
            $r['responseMessage'] = "銷貨單號：" . $id . " 資料更新";
            $r['responseArray']   = [];
            return json_encode($r, JSON_UNESCAPED_UNICODE);
        default:
            $r                    = array();
            $r['responseStatus']  = "FAIL";
            $r['responseMessage'] = "銷貨彙總更新失敗";
            return json_encode($r, JSON_UNESCAPED_UNICODE);
            break;
    }

}
#####################################################################################
#  銷貨明細資料維誰
#####################################################################################
function phone_a01_sale_detail()
{
    global $xoopsDB;
    $company_id = $_POST["company_id"]; // 公司別代碼
    $op         = $_POST["op"]; //
    $uploaddata = $_POST["uploaddata"]; //
    $up_id      = $_POST["up_id"]; // 銷貨單號
    $id         = $_POST["id"]; // 銷貨明細序號
    $table1     = "phone_a01_sale_detail";
    $r          = array();
    $partno     = $_POST["partno"]; // 產品編號
    switch ($op) {
        case 'get_by_upid': // 抓取某一張銷貨單的所有銷貨明細
            $searchCondition = "a.company_id = '{$company_id}' AND a.up_id = '{$up_id}' ";
            $sql             = "SELECT a.* , b.descrp  FROM `phone_a01_sale_detail` AS a ";
            $sql .= "LEFT JOIN a00_mstock AS b ON a.partno = b.partno";
            $sql .= " WHERE " . $searchCondition . "  ORDER BY a.id DESC";

            $result = sqlExcuteForSelectData($sql);
            $r      = array();
            $all    = array();
            while ($datas = sqlFetch_assoc($result)) {
                $all[] = $datas;
            }

            $r['responseStatus']  = "SUCCESS";
            $r['responseMessage'] = "";
            $r['responseArray']   = $all;
            return json_encode($r, JSON_UNESCAPED_UNICODE);
        case 'get_single_id': // 抓取某一筆之銷貨明細
            $searchCondition = "a.company_id = '{$company_id}' AND a.id = '{$id}' ";
            $sql             = "SELECT a.* , b.descrp   FROM `{$table1}` AS a ";
            $sql .= "LEFT JOIN a00_mstock AS b ON a.partno = b.partno";
            $sql .= " WHERE " . $searchCondition;

            $result = sqlExcuteForSelectData($sql);
            $r      = array();
            $all    = array();
            while ($datas = sqlFetch_assoc($result)) {
                $all[] = $datas;
            }
            $r['responseStatus']  = "SUCCESS";
            $r['responseMessage'] = "";
            $r['responseArray']   = $all;
            return json_encode($r, JSON_UNESCAPED_UNICODE);
        case 'del': // 刪除
            // 更新 a00_mstock 之 c_qty_sale(已銷貨數量)
            $partno = "";
            $c_qty  = 0;        
            $searchCondition = "`company_id` = '{$company_id}' AND `id` = '{$id}'";  
            $sql             = "SELECT * FROM `phone_a01_sale_detail` WHERE " . $searchCondition;
            $result          = sqlExcuteForSelectData($sql);   
            while ($datas = sqlFetch_assoc($result)) {
                $c_qty = $datas['c_qty']; 
                $partno = $datas['partno'];
            }

            $searchCondition = "`company_id` = '{$company_id}' AND `partno` = '{$partno}'";
            $sql             = "SELECT * FROM `a00_mstock` WHERE " . $searchCondition;
            $result          = sqlExcuteForSelectData($sql);   
            while ($datas = sqlFetch_assoc($result)) {
                $c_qty = $datas['c_qty_sale'] - $c_qty ; 
            }

            $arr2      = array();
            $arr2["c_qty_sale"] = $c_qty;    
            sqlUpdate("a00_mstock", $arr2, $searchCondition);   

            // ***
            $searchCondition = "`company_id` = '{$company_id}' AND `id` = '{$id}'";
            sqlDelete($table1, $searchCondition);
            $arr1 = phone_a01_sale_detail_static($company_id, $up_id); // 統計銷貨明細合計資料 , 再更新銷貨彙總檔

            $searchCondition    = "`company_id` = '{$company_id}' AND `id` = '{$up_id}'";
            $arr1["company_id"] = $company_id;
            sqlUpdate("phone_a01_sale_head", $arr1, $searchCondition);

            $r['responseStatus']  = "SUCCESS";
            $r['responseMessage'] = "銷貨明細序號：" . $id . " 資料刪除";
            $r['responseArray']   = $arr1;
            return json_encode($r, JSON_UNESCAPED_UNICODE);
        case 'insert': // 新增
            $json                 = json_decode($uploaddata);
            $sqlArr               = array();
            $sqlArr["company_id"] = $company_id;
            $sqlArr["createdate"] = date_create()->format('Y-m-d H:i:s');

            $partno = "";
            $c_qty  = 0;
            foreach ($json as $key => $value) {
                $sqlArr[$key] = kyc_security($value);
                if($key == "partno") {
                   $partno = $value;
                }
                if($key == "c_qty") {
                   $c_qty = $value;
                }                
            }
            sqlInsert($table1, $sqlArr);
            $return_id = $xoopsDB->insert_id;

            $arr1               = phone_a01_sale_detail_static($company_id, $up_id); // 統計銷貨明細合計資料 , 再更新銷貨彙總檔
            $searchCondition    = "`company_id` = '{$company_id}' AND `id` = '{$up_id}'";
            $arr1["company_id"] = $company_id;
            sqlUpdate("phone_a01_sale_head", $arr1, $searchCondition);
            // genMsgFile("insertData1","txt","1111");
            // 更新 a00_mstock 之 c_qty_sale(已銷貨數量)
            $searchCondition = "`company_id` = '{$company_id}' AND `partno` = '{$partno}'";
            $sql             = "SELECT * FROM `a00_mstock` WHERE " . $searchCondition;
            $result          = sqlExcuteForSelectData($sql);   
            while ($datas = sqlFetch_assoc($result)) {
                $c_qty += $datas['c_qty_sale']; 
            }
            $arr2      = array();
            $arr2["c_qty_sale"] = $c_qty;    
            sqlUpdate("a00_mstock", $arr2, $searchCondition);                    
            // genMsgFile("insertData2","txt","2222");

            $r['responseStatus']  = "SUCCESS";
            $r['responseMessage'] = $return_id;
            $r['responseArray']   = $arr1;
            return json_encode($r, JSON_UNESCAPED_UNICODE);
        case 'update': // 修改
            $c_qty1  = 0;        
            $searchCondition = "`company_id` = '{$company_id}' AND `id` = '{$id}'";  
            $sql             = "SELECT * FROM `phone_a01_sale_detail` WHERE " . $searchCondition;
            $result          = sqlExcuteForSelectData($sql);   
            while ($datas = sqlFetch_assoc($result)) {
                $c_qty1 = $datas['c_qty']; 
            }

            $searchCondition      = "`company_id` = '{$company_id}' AND `id` = '{$id}'";
            $json                 = json_decode($uploaddata);
            $sqlArr               = array();
            $sqlArr["company_id"] = $company_id;
            $sqlArr["createdate"] = date_create()->format('Y-m-d H:i:s');

            $partno = "";
            $c_qty  = 0;
            foreach ($json as $key => $value) {
                $sqlArr[$key] = kyc_security($value);
                if($key == "partno") {
                   $partno = $value;
                }
                if($key == "c_qty") {
                   $c_qty = $value;
                }                          
            }
            sqlUpdate($table1, $sqlArr, $searchCondition);

            $arr1               = phone_a01_sale_detail_static($company_id, $up_id); // 統計銷貨明細合計資料 , 再更新銷貨彙總檔
            $searchCondition    = "`company_id` = '{$company_id}' AND `id` = '{$up_id}'";
            $arr1["company_id"] = $company_id;
            sqlUpdate("phone_a01_sale_head", $arr1, $searchCondition);
            // 更新 a00_mstock 之 c_qty_sale(已銷貨數量)
            $searchCondition = "`company_id` = '{$company_id}' AND `partno` = '{$partno}'";
            $sql             = "SELECT * FROM `a00_mstock` WHERE " . $searchCondition;
            $result          = sqlExcuteForSelectData($sql);   
            while ($datas = sqlFetch_assoc($result)) {
                $c_qty = $datas['c_qty_sale'] + $c_qty ; 
            }
            $arr2      = array();
            $arr2["c_qty_sale"] = $c_qty - $c_qty1;    
            sqlUpdate("a00_mstock", $arr2, $searchCondition);    

            $r['responseStatus']  = "SUCCESS";
            $r['responseMessage'] = "銷貨明細序號：" . $id . " 資料更新";
            $r['responseArray']   = $arr1;
            return json_encode($r, JSON_UNESCAPED_UNICODE);
        default:
            $r                    = array();
            $r['responseStatus']  = "FAIL";
            $r['responseMessage'] = "銷貨明細操作失敗";
            return json_encode($r, JSON_UNESCAPED_UNICODE);
            break;
    }

}
################################
# 單一產品詳細資料
#################################
function a00_mstock()
{
    $op         = $_POST["op"];
    $company_id = $_POST["company_id"];
    $partno     = $_POST["partno"];

    switch ($op) {
        case 'barcode': // 抓取某
            $searchCondition = "`company_id` = '{$company_id}' AND `barcode` = '{$partno}'";
            $sql             = "SELECT * FROM `a00_mstock` WHERE " . $searchCondition;
            $result          = sqlExcuteForSelectData($sql);

            $r   = array();
            $all = array();
            while ($prods = sqlFetch_assoc($result)) {
                // die($prods['descrp']);
                $all[] = $prods;
            }

            $r['responseStatus']  = "SUCCESS";
            $r['responseMessage'] = "";
            $r['responseArray']   = $all;
            return json_encode($r, JSON_UNESCAPED_UNICODE);
        case 'all_data': // 抓取所有資料
            $searchCondition = "`company_id` = '{$company_id}'";
            $sql             = "SELECT * FROM `a00_mstock` WHERE " . $searchCondition . "ORDER BY id DESC";
            $result          = sqlExcuteForSelectData($sql);

            $r   = array();
            $all = array();
            while ($prods = sqlFetch_assoc($result)) {
                $all[] = $prods;
            }

            $r['responseStatus']  = "SUCCESS";
            $r['responseMessage'] = "";
            $r['responseArray']   = $all;
            return json_encode($r, JSON_UNESCAPED_UNICODE);                      
    }
}
################################
# 加總某一銷貨單之銷貨明細資料
#################################
function phone_a01_sale_detail_static($company_id, $up_id)
{

    $searchCondition = "company_id = '{$company_id}' AND up_id = '{$up_id}' ";
    $sql             = "SELECT *  FROM `phone_a01_sale_detail` WHERE " . $searchCondition;

    $result   = sqlExcuteForSelectData($sql);
    $r        = array();
    $amount   = 0;
    $c_wage   = 0;
    $weight_1 = 0;
    $weight_2 = 0;
    $weight_3 = 0;
    $weight_4 = 0;
    $c_count  = 0;
    while ($datas = sqlFetch_assoc($result)) {
        $amount += ($datas['amount'] * $datas['c_qty']);
        $c_wage += ($datas['c_wage'] * $datas['c_qty']);
        $weight_1 += ($datas['weight_1'] * $datas['c_qty']);
        $weight_2 += ($datas['weight_2'] * $datas['c_qty']);
        $weight_3 += ($datas['weight_3'] * $datas['c_qty']);
        $weight_4 += ($datas['weight_4'] * $datas['c_qty']);
        $c_count++;
    }
    $wei_4    = intval(floor($weight_4 / 10)); // 取整數
    $weight_4 = $weight_4 % 10; // 取餘數

    $wei_3    = intval(floor(($weight_3 + $wei_4) / 10)); // 取整數
    $weight_3 = ($weight_3 + $wei_4) % 10; // 取餘數

    $wei_2    = intval(floor(($weight_2 + $wei_3) / 10)); // 取整數
    $weight_2 = ($weight_2 + $wei_3) % 10; // 取餘數

    $weight_1 = $weight_1 + $wei_2;

    $r['c_count']  = $c_count;
    $r['amount']   = $amount;
    $r['c_wage']   = $c_wage;
    $r['weight_1'] = $weight_1;
    $r['weight_2'] = $weight_2;
    $r['weight_3'] = $weight_3;
    $r['weight_4'] = $weight_4;

    return $r;
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

function upload()
{
    $remark         = $_POST['remark']; //圖檔備註說明
    $new_image_name = strtolower($_FILES['file']['name']);
    move_uploaded_file($_FILES["file"]["tmp_name"], "uploads/" . $new_image_name);
    $r                    = array();
    $r['responseStatus']  = "SUCCESS";
    $r['responseMessage'] = "上傳成功:" . $new_image_name;
    $r['responseArray']   = $remark;
    return json_encode($r, JSON_UNESCAPED_UNICODE);
}
########################################################################
#  產生訊息檔,以供 debug
########################################################################
function genMsgFile($fileName="msg",$fileType="txt",$msgText="") {
 global $xoopsDB;
 $file = "uploads/".$fileName.strtotime("now").".".$fileType;
 $f = fopen($file, 'w'); //以寫入方式開啟文件
 fwrite($f, $msgText); //將新的資料寫入到原始的文件中
 fclose($f);
}
