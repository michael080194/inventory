<!DOCTYPE html>

<html lang="zh-tw">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>雲端盤點系統</title>

    <!-- Bootstrap 4 -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">

	<!-- Custom style -->
	<link rel="stylesheet" href="/inventory/dist/css/style.css">
	<link rel="stylesheet" href="/inventory/dist/css/loader.css">
</head>

<body>
  <nav class="navbar navbar-expand-md fixed-top navbar-dark bg-dark">
      <div class="container">
          <a class="navbar-brand" href="#">雲端盤點系統 <i class="fas fa-chart-bar"></i></a>
          <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbar-main" aria-controls="navbar-main" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
          </button>

          <div class="navbar-collapse collapse" id="navbar-main">
              <ul class="navbar-nav mr-auto">
                  <li class="nav-item active">
                      <a class="nav-link" href="#">資料匯出 <span class="sr-only">(current)</span></a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="#">資料匯入</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="j00_user.php">使用者建立</a>
                  </li>
              </ul>
              <ul class="navbar-nav">
                  <li class="nav-item">
                      <a class="btn btn-outline-light" href="index_1.php?op=logout"><i class="fas fa-sign-out-alt"></i> 登出</a>
                  </li>
              </ul>
          </div>
      </div>
  </nav>