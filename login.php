<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
	header("location: index.php");
}

include_once("dist/layouts/_head.php");

?>

<link rel="stylesheet" href="dist/css/login.css">

<div class="loader">
	<div class="loader-border">
		<span class="loader-inner"></span>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="col min-vh-100 d-flex flex-column justify-content-center" style="margin: -60px 0; padding: 60px 0;">
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
							<form class="login-form needs-validation" method="POST" autocomplete="off" novalidate>
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
								<div class="alert-text text-danger" style="display: none;" role="alert"></div>
								<button type="submit" class="btn btn-main btn-lg w-100 mt-3">登入</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include_once("dist/layouts/js.php") ?>

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
					else {
						kyc_login_function();
						event.preventDefault();
					}
					form.classList.add('was-validated');
				}, false);
			});
		}, false);
	})();

	function kyc_login_function() {
		let loginForm = $('.login-form');
		var url1 = "http://localhost/inventory/api/inventoryApi.php";
		// var url1 = "http://michael1.cp35.secserverpros.com/inventory/api/inventoryApi.php";
		var pass0 = {};
		pass0.op = 'login';
		pass0.comp_id = loginForm.find('input[name="company_id"]').val();
		pass0.user = loginForm.find('input[name="user"]').val();
		pass0.pass = loginForm.find('input[name="pass"]').val();
		$.ajax({
			// url: 'http://localhost/php/inventory/api/inventoryApi.php',
			url: url1,
			method: 'POST',
			data: pass0,
			beforeSend: function (xhr) {
				$('.loader').css('display', 'flex');
			},
			complete: function (xhr, status) {
				$('.loader').css('display', 'none');
			},
			success: function (response, xhr, status) {
				response = JSON.parse(response);
				status = response['responseStatus']; // if SUCCESS return content array
				if (status == 'OK') { // login SUCCESS
					location.href = 'dist/php/index_1.php';
				} else {
					$('.alert-text').text('* 帳號或密碼錯誤。');
					$('.alert-text').show();
				}
			},
			error: function (xhr, status) {
				// `status` will return 'error'
				console.log('xhr: ', xhr);
				$('.alert-text').text('* 系統出現非預期錯誤，請聯絡負責人員。');
				$('.alert-text').show();
			}
		});

		return false;
	}
</script>

<?php include_once("dist/layouts/_foot.php") ?>
