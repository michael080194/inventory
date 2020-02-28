<?php
if (!function_exists('kyc_get_url')) {
  function kyc_get_url()
  {
      $http = 'http://';
      if (!empty($_SERVER['HTTPS'])) {
          $http = ($_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
      }

          //$u = parse_url($http . $_SERVER["HTTP_HOST"] . $port . $_SERVER['REQUEST_URI']);
          $u = parse_url($http . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI']);
          /*
          $_SERVER["HTTP_HOST"] 本身就會有 port 資料 ，如 localhost:8080
          如果是正常 port 80 443 等，網址本身就無須再多加 port 號

           */
          if (!empty($u['path']) and preg_match('/\/modules/', $u['path'])) {
              $XMUrl = explode("/modules", $u['path']);
          } elseif (!empty($u['path']) and preg_match('/\/themes/', $u['path'])) {
              $XMUrl = explode("/themes", $u['path']);
          } elseif (!empty($u['path']) and preg_match('/\/upgrade/', $u['path'])) {
              $XMUrl = explode("/upgrade", $u['path']);
          } elseif (!empty($u['path']) and preg_match('/\/include/', $u['path'])) {
              $XMUrl = explode("/include", $u['path']);
          } elseif (!empty($u['path']) and preg_match('/.php/', $u['path'])) {
              $XMUrl[0] = dirname($u['path']);
          } elseif (!empty($u['path'])) {
              $XMUrl[0] = $u['path'];
          } else {
              $XMUrl[0] = "";
          }

          $my_url = str_replace('\\', '/', $XMUrl['0']);
          if (substr($my_url, -1) == '/') {
              $my_url = substr($my_url, 0, -1);
          }

          $port = isset($u['port']) ? ":{$u['port']}" : '';
          /*
          如果有切出 port 號，在前面加入 : 號，傳出網址

           */

          $url = "{$u['scheme']}://{$u['host']}$port{$my_url}";

          return $url;
}
}
########################################################################
#  產生訊息檔,以供 debug
########################################################################
function genMsgFile($fileName="msg",$fileType="txt",$msgText="") {
 // genMsgFile("op_home","txt",print_r($homes, true)); array
 // genMsgFile("op_home","json",$json);
 // genMsgFile("op_static_sn","txt",$_POST['sn']);
 $file = "../uploads/".$fileName.strtotime("now").".".$fileType;
 $f = fopen($file, 'w'); //以寫入方式開啟文件
 fwrite($f, $msgText); //將新的資料寫入到原始的文件中
 fclose($f);
}