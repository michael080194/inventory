<?php
// 請自行設定 自己的資料庫 資訊 並將檔名更新為 `config.php`

if ($_SERVER["SERVER_NAME"] == "localhost") {
    //資料庫位址
    define('_DB_HOST', 'localhost');
    //資料庫帳號
    define('_DB_USER', 'db user');
    //資料庫密碼
    define('_DB_PASS', 'db password');
    //資料庫名稱
    define('_DB_NAME', 'db name');
} else {
    //資料庫位址
    define('_DB_HOST', 'localhost');
    //資料庫帳號
    define('_DB_USER', 'db user');
    //資料庫密碼
    define('_DB_PASS', 'db password');
    //資料庫名稱
    define('_DB_NAME', 'db name');
}
