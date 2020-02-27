<?php
// SQL class for kyc define
// https://johnmorrisonline.com/simple-php-class-prepared-statements-mysqli/
if (!class_exists('KYC_DB')) {
    class KYC_DB
    {
        public function __construct($host, $user, $password, $database)
        {
            $this->host     = $host;
            $this->user     = $user;
            $this->password = $password;
            $this->database = $database;
        }

        public function kyc_connect()
        {
            return new mysqli($this->host, $this->user, $this->password, $this->database);
        }

        public function kyc_tableOperation($query)
        {
            // 檢查資料表是否存在 新增或移除 資料表
            // $db     = $this->kyc_connect();
            // $result = $db->query($query) or die(printf("Error: %s <br>" . $query, $db->sqlstate));
            $result = $this->kyc_sqlExecute($query);
            return $result;
        }

        // 執行 資料庫異動的 Sql 命令
        protected function kyc_sqlExecute($query)
        {
            $db = $this->kyc_connect();
            return $db->query($query) or die(printf("Error: %s <br>" . $query, $db->sqlstate));
        }

        public function kyc_insert($table, $sqlArray)
        {
            // Check for $table or $data not set

            if (empty($table) || empty($sqlArray)) {
                return false;
            }

            $field = "";
            $value = "";
            foreach ($sqlArray as $key => $val) {
                $val = $this->kyc_security($val);
                $field .= ($field == "") ? "`" . $key . "`" : ",`" . $key . "`";
                $value .= ($value == "") ? "'" . $val . "'" : ",'" . $val . "'";
            }
            $sql = "INSERT INTO `$table` (" . $field . ") VALUES (" . $value . ")";

            $db     = $this->kyc_connect();
            $result = $db->query($sql) or die(printf("Error: %s <br>" . $sql, $db->sqlstate));
            $id     = $db->insert_id; //取得最後新增的編號

            return $id;
            // Check for successful insertion
            if ($id != 0) {
                return $id;
            }

            return false;
        }

        // 修改資料 by 條件
        public function kyc_sqlUpdate($table, $sqlArray, $updateCondition)
        {
            if ($table != "" and $updateCondition != "") {
                $field = "";
                foreach ($sqlArray as $key => $val) {
                    $val = $this->kyc_security($val);
                    $temStr = "`" . $key . "` = '" . $val . "'";
                    $field .= ($field == "") ? $temStr : "," . $temStr;
                }
                $sql    = "UPDATE `$table` SET " . $field . " WHERE " . $updateCondition;
                $db     = $this->kyc_connect();
                $result = $db->query($sql) or die(printf("Error: %s <br>" . $sql, $db->sqlstate));
            }
        }

        // 新增或修改 by id
        public function kyc_sqlReplace($table, $sqlArray, $type)
        {
            // Check for $table or $data not set
            if (empty($table) || empty($sqlArray)) {
                return false;
            }
            $field = "";
            $value = "";
            foreach ($sqlArray as $key => $val) {
                $val = $this->kyc_security($val);
                $field .= ($field == "") ? "`" . $key . "`" : ",`" . $key . "`";
                $value .= ($value == "") ? "'" . $val . "'" : ",'" . $val . "'";
            }
            $sql    = "REPLACE INTO  `$table` (" . $field . ") VALUES (" . $value . ")";
            $db     = $this->kyc_connect();
            $result = $db->query($sql) or die(printf("Error: %s <br>" . $sql, $db->sqlstate));
            if ($type == "ADD") {
                $id = $db->insert_id; //取得最後新增的編號
                return $id;
            } else {
                return "";
            }

        }

        public function kyc_sqlDelete($table, $delCondition)
        {
            // Check for $table or $data not set
            if (empty($table) || empty($delCondition)) {
                return false;
            }
            $sql    = "DELETE FROM `$table` WHERE " . $delCondition;
            $db     = $this->kyc_connect();
            $result = $db->query($sql) or die(printf("Error: %s <br>" . $sql, $db->sqlstate));
        }

        public function kyc_sqlFetch_assoc($query)
        {
            $db     = $this->kyc_connect();
            $result = $db->query($query);
            $results = array();
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }

            return $results;
        }

        public function kyc_sqlFetch_row($query)
        {
            $db     = $this->kyc_connect();
            $result = $db->query($query);

            while ($row = $result->fetch_row()) {
                $results[] = $row;
            }

            return $results;
        }

        public function kyc_real_escape_string($str1)
        {
            // 去除斜杠
            if (get_magic_quotes_gpc()){
               $str1 = stripslashes($str1);
            }
            // 如果不是数字则加引号
            if (!is_numeric($str1)){
                $db     = $this->kyc_connect();
                $str1 = "'" . $db->mysql_real_escape_string($str1) . "'";
            }
            return $str1;
        }

        public function kyc_security($str1)
        {
            /*
              PHP addslashes 函數的功能是替字串的特殊字符增加反斜線效果，會有這樣的需求主要在於部份的特殊符號會造成資料庫的錯誤或資料被竊取，所謂的 sql injection 這種攻擊手法就會用一些特殊符號來偷撈資料，所以一般會用 PHP addslashes 函數來協助避免，當字串中含有單引號、雙引號、反斜線或 NULL 字符的時候，addslashes 函數可以在這些特殊符號前面自動加上反斜線，讓特殊符號跳脫掉，等於是失效，這是非常基本也非常重要的 PHP 安全概念，接著我們就來看看這個 addslashes 函數該如何使用。

              PHP htmlspecialchars 函數的功能是用來轉換 HTML 特殊符號為僅能顯示用的編碼，舉例來說，HTML 的大於（>）小於（<）符號、單引號（'）或雙引號（""）都可以轉換為僅能閱讀的 HTML 符號，這是什麼意思呢？就是將 HTML 符號變成不可執行的符號，例如有人利用網站表單輸入一些清除資料庫的語法或塞入後門程式，通常都會用到一些特殊符號，為了安全起見，所有表單傳遞的資料都應該利用 PHP htmlspecialchars 函數做第一層的把關，這是網頁設計的安全基礎。
            */
            // return addslashes($str1);
            return htmlspecialchars($str1,ENT_QUOTES);// 雙引號與單引號都要轉換
        }

        public function kyc_valid($str1 , $valid_type)
        {
            /*
              filter_var() 函数通过指定的过滤器过滤变量。如果成功，则返回已过滤的数据，如果失败，则返回 false。
              https://www.w3school.com.cn/php/php_ref_filter.asp
              filter_var($str1, FILTER_VALIDATE_EMAIL)
              filter_var($str1, FILTER_VALIDATE_URL)
            */
            // if(!filter_var("someone@example....com", FILTER_VALIDATE_EMAIL)){
            //    echo("E-mail is not valid");
            // } else {
            //    echo("E-mail is valid");
            // }
        }
    }
}
