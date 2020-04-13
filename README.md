# 行動化盤點系統
  *   本系統是展示用手機取代傳統盤點機來做盤點，客戶將現有庫存資料用 Excel 檔 上傳至雲端，
      再使用本系統開發之APP，使用手機將盤點資料上傳至雲端，
      由Web程式計算出盤盈盤虧，再匯出 Excel 檔
  *   產品資料須事先建立條碼(電器業可由機種編號直接代替條碼編號)
  *   使用本APP，須將現有excel庫存檔上傳至雲端
  *   盤點時可掃條碼或自行輸入產品編號；或查詢現有庫存資料之後，直接輸入盤點數量
  *   電腦會依現有庫存和盤點數量，計算盤盈或盤虧，並匯出 Excel檔
  *   掃描條碼方式可由手機照像機鏡頭，或搭配可攜式藍牙讀碼機效果更佳(例如漢印HS-M300)
  *   Android 手機可下載 apk ; iOS 直接執行 Web 端 程式
  *   支援多公司(分店)盤點

行動化盤點系統 文件
==================

![](http://michael1.cp35.secserverpros.com/uploads/tad_book3/book_28.png)

**備註:** 開發完成後將會有一個展示網站 [展示網址][eng-doc].

[eng-doc]:http://michael1.cp35.secserverpros.com/

程式架構說明
================

*   [開發工具]
    *   [Web 端](#web)
    *   [手機端](#app)
    *   [產生 cordova 專案](#cordova)
*   [PHP程式]
    *   [HTML+CSS](#HTML)
    *   [Javascript](#JQUERY)
    *   [PHP](#PHP)
    *   [Smarty](#Smarty)
*   [APP程式]
    *   [Cordova](#app-1)
    *   [Framework7 Vue](#app-2)
*   [其他]
    *   [其他1](#other1)
    *   [其他2](#other2)


* * *
<h2 id="web">開發工具--Web端</h2>

    1.  HTML + CSS
    2.  Jquery + Javascript
    3.  PHP
    4.  Smarty

<h2 id="app">開發工具--app端</h2>

    1.  Cordova
    2.  Framework7 + Vue

<h2 id="cordova">開發工具--cordova專案 </h2>

    1.  批次檔範本(MAC版本) 放在 cordova-tool 資料夾
    2.  請於 MAC 終端機執行 sudo sh create_inventory_android.sh ; 批次產生 cordova 專案
        (建立完成後，要將 inventory 資料夾 sharing & permmisions打開)

<h2 id="other1">其他1 </h2>

    1.  使用本系統先執行 j99_install.php 建立資料庫
    2.  執行 j99_test-sql.php 的 gen_data() function 可產生測試資料;並可測試sql語法
    3.  執行 j99_test-ajax.html 可測試用 jquery ajax 與後端API溝通




