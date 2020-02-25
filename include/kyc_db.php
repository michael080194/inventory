<?php
// SQL class for kyc define
// https://johnmorrisonline.com/simple-php-class-prepared-statements-mysqli/
if (!class_exists('DB')) {
    class DB
    {
        public function __construct($host, $user, $password, $database)
        {
            $this->host     = $host;
            $this->user     = $user;
            $this->password = $password;
            $this->database = $database;
        }

        public function connect()
        {
            return new mysqli($this->host, $this->user, $this->password, $this->database);
        }

        public function tableOperation($query)
        {
            // 檢查資料表是否存在 新增或移除 資料表
            // $db     = $this->connect();
            // $result = $db->query($query) or die(printf("Error: %s <br>" . $query, $db->sqlstate));
            $result = $this->sqlExecute($query);
            return $result;
        }

        // 執行 資料庫異動的 Sql 命令
        protected function sqlExecute($query)
        {
            $db = $this->connect();
            return $db->query($query) or die(printf("Error: %s <br>" . $query, $db->sqlstate));
        }

        public function insert($table, $sqlArray)
        {
            // Check for $table or $data not set

            if (empty($table) || empty($sqlArray)) {
                return false;
            }

            $field = "";
            $value = "";
            foreach ($sqlArray as $key => $val) {
                $field .= ($field == "") ? "`" . $key . "`" : ",`" . $key . "`";
                $value .= ($value == "") ? "'" . $val . "'" : ",'" . $val . "'";
            }
            $sql = "INSERT INTO `$table` (" . $field . ") VALUES (" . $value . ")";

            $db     = $this->connect();
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
        public function sqlUpdate($table, $sqlArray, $updateCondition)
        {
            if ($table != "" and $updateCondition != "") {
                $field = "";
                foreach ($sqlArray as $key => $val) {
                    $temStr = "`" . $key . "` = '" . $val . "'";
                    $field .= ($field == "") ? $temStr : "," . $temStr;
                }
                $sql    = "UPDATE `$table` SET " . $field . " WHERE " . $updateCondition;
                $db     = $this->connect();
                $result = $db->query($sql) or die(printf("Error: %s <br>" . $sql, $db->sqlstate));
            }
        }

        // 新增或修改 by id
        public function sqlReplace($table, $sqlArray, $type)
        {
            // Check for $table or $data not set
            if (empty($table) || empty($sqlArray)) {
                return false;
            }
            $field = "";
            $value = "";
            foreach ($sqlArray as $key => $val) {
                $field .= ($field == "") ? "`" . $key . "`" : ",`" . $key . "`";
                $value .= ($value == "") ? "'" . $val . "'" : ",'" . $val . "'";
            }
            $sql    = "REPLACE INTO  `$table` (" . $field . ") VALUES (" . $value . ")";
            $db     = $this->connect();
            $result = $db->query($sql) or die(printf("Error: %s <br>" . $sql, $db->sqlstate));
            if ($type == "ADD") {
                $id = $db->insert_id; //取得最後新增的編號
                return $id;
            } else {
                return "";
            }

        }

        public function sqlDelete($table, $delCondition)
        {
            // Check for $table or $data not set
            if (empty($table) || empty($delCondition)) {
                return false;
            }
            $sql    = "DELETE FROM `$table` WHERE " . $delCondition;
            $db     = $this->connect();
            $result = $db->query($sql) or die(printf("Error: %s <br>" . $sql, $db->sqlstate));
        }

        public function sqlFetch_assoc($query)
        {
            $db     = $this->connect();
            $result = $db->query($query);
            $results = array();
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }

            return $results;
        }

        public function sqlFetch_row($query)
        {
            $db     = $this->connect();
            $result = $db->query($query);

            while ($row = $result->fetch_row()) {
                $results[] = $row;
            }

            return $results;
        }
    }
}
