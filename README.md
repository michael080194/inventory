# 行動化盤點系統
  *   本系統是展示用手機取代傳統盤點機來做盤點，客戶將現有庫存資料用 Excel 檔 上傳至雲端，
      再使用本系統開發之APP，使用手機將盤點資料上傳至雲端，
      由Web程式計算出盤盈盤虧，再匯出 Excel 檔
  *   產品資料須事先建立條碼(電器業可由機種直接代替條碼編號)    
  *   搭配無線藍芽讀碼機效果更佳
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
    *   [Javascript template 7](#JQUERY)
    *   [PHP](#PHP)
*   [APP程式]
    *   [Cordova](#app-1)
    *   [Framework7 Vue](#app-2)
*   [其他]
    *   [其他1](#other1)
    *   [其他2](#other2)


* * *
    1.  HTML + CSS
    2.  Jquery + Javascript template 7
    3.  PHP

<h2 id="web">開發工具 -- Web 端</h2>
    1.  HTML + CSS
    2.  Jquery + Javascript template 7
    3.  PHP

<h2 id="app">開發工具 -- app 端</h2>
    1.  Cordova
    2.  Framework7 Vue
     
<h2 id="cordova">開發工具 -- cordova 專案 </h2>
    批次產生 cordova 專案 : sh create_inventory_android.sh



