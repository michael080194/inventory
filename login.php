<!DOCTYPE html>

<html lang="zh-tw">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>雲端盤點系統</title>

    <!-- Bootstrap 4 -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">

	<!-- FontAwesome -->
	<script src="https://kit.fontawesome.com/66d0a4dec1.js" crossorigin="anonymous"></script>

	<!-- Custom style -->
	<style>
		:root {
			--color: #00be68 !important;
			--color-hover-dark: #00b260 !important;
			--placeholder: #999 !important;
		}

		.login-form .form-control::-webkit-input-placeholder {  /* WebKit, Blink, Edge */
			color: var(--placeholder);
			letter-spacing: 1px;
		}
		.login-form .form-control:-moz-placeholder {  /* Mozilla Firefox 4 to 18 */
			color: var(--placeholder);
			letter-spacing: 1px;
		}
		.login-form .form-control::-moz-placeholder {  /* Mozilla Firefox 19+ */
			color: var(--placeholder);
			letter-spacing: 1px;
		}
		.login-form .form-control:-ms-input-placeholder {  /* Internet Explorer 10-11 */
			color: var(--placeholder);
			letter-spacing: 1px;
		}
		.login-form .form-control::-ms-input-placeholder {  /* Microsoft Edge */
			color: var(--placeholder);
			letter-spacing: 1px;
		}

		body {
			background-color: #f6f6f6;
			font-family: "STHeiti", sans-serif;
		}

		.login-form input {
			letter-spacing: 1px;
		}

		.login-form input:focus {
			border-color: var(--color);
			box-shadow: 0 0 0 .2rem rgba(40, 167, 69, .25);
		}

		.btn.btn-main {
			background-color: var(--color);
			color: #fff;
		}

		.btn.btn-main:hover {
			background-color: var(--color-hover-dark);
			color: #fff;
		}

		.login-form .form-group .form-row {
			align-items: center;
			flex-wrap: nowrap;
		}

		.login-form .icon-area {
			display: flex;
			justify-content: center;
			align-items: center;
			width: 2rem;
			height: 2rem;
			padding-left: .8rem;
			padding-right: 1.6rem;
			font-size: 1.4rem;
		}

		.card-top-style {
			width: 100%;
			height: 5px;
			background-color: var(--color);
		}

		.logo-area {
			display: flex;
			justify-content: center;
			align-items: center;
			width: 100px;
			height: 100px;
			margin:  1rem auto;
			background-color: var(--color);
			border-radius: 50%;
			color: #fff;
			font-size: 2rem;
		}
	</style>
</head>

<body>
	<!-- <nav class="navbar navbar-expand-md fixed-top navbar-dark bg-dark">
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
				</ul>
				<ul class="navbar-nav">
					<li class="nav-item">
						<a class="btn btn-outline-light" href="#"><i class="fas fa-sign-out-alt"></i> 登出</a>
					</li>
				</ul>
			</div>
		</div>
	</nav> -->

	<div class="container">
		<div class="row">
			<div class="col min-vh-100 d-flex flex-column justify-content-center">
				<div class="row px-3">
					<div class="w-100 mx-auto" style="max-width: 360px">
						<div class="card-top-style"></div>
						<div class="card p-4 border-0 rounded-0 shadow">
							<div class="logo-area">
								<i class="far fa-chart-bar"></i>
								<!-- <img src="./img/kyc_logo.png" width="300" height="75" alt="./img/kyc_logo.png   300px * 75px" class="border"> -->
							</div>
							<h3 class="my-4 text-center">
								雲端盤點系統登入
							</h3>
							<div class="card-body p-0">
								<form class="login-form needs-validation" method="POST" autocomplete="off" novalidate onsubmit="return kyc_login_function();">
									<div class="form-group">
										<div class="form-row align-items-center flex-nowrap mx-0">
											<div class="icon-area">
												<i class="fas fa-id-card"></i>
											</div>
											<div class="w-100">
												<input type="text" id="login-company-id" name="company_id" class="form-control form-control-lg" placeholder="公司別代號" title="公司別代號" autofocus required>
												<div class="invalid-feedback">請輸入公司別代號</div>
											</div>
										</div>
									</div>
									<div class="form-group">
										<div class="form-row align-items-center flex-nowrap mx-0">
											<div class="icon-area">
												<i class="fas fa-user-alt"></i>
											</div>
											<div class="w-100">
												<input type="text" id="login-user" name="user" class="form-control form-control-lg" placeholder="使用者名稱" title="使用者名稱" autofocus required>
												<div class="invalid-feedback">請輸入使用者名稱</div>
											</div>
										</div>
									</div>
									<div class="form-group">
										<div class="form-row align-items-center flex-nowrap mx-0">
											<div class="icon-area">
												<i class="fas fa-unlock-alt"></i>
											</div>
											<div class="w-100">
												<input type="password" id="login-pass" name="pass" class="form-control form-control-lg" placeholder="使用者密碼" title="使用者密碼" autofocus required>
												<div class="invalid-feedback">請輸入使用者密碼</div>
											</div>
										</div>
									</div>
									<button type="submit" class="btn btn-main btn-lg w-100 mt-3">登入</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

    <!-- jQuery && Bootstrap 4 -->
    <script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

	<!-- Custom form validations -->
	<script>
		(function () {
			'use strict';
			window.addEventListener('load', function () {
				// Fetch all forms we want to apply custom validation styles
				let forms = document.getElementsByClassName('needs-validation');
				// Loop over them and prevent submission
				let validation = Array.prototype.filter.call(forms, function (form) {
					form.addEventListener('submit', function (event) {
						if (form.checkValidity() === false) {
							event.preventDefault();
							event.stopPropagation();
						}
						form.classList.add('was-validated');
					}, false);
				});
			}, false);
		})();

		function kyc_login_function() {
			alert("aaaa");
		}
	</script>
</body>

</html>