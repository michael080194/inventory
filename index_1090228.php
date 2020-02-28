<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION["user"])) {
	header("location: login.php");
}

include_once("dist/layouts/_head.php");

?>

<link rel="stylesheet" href="dist/css/login.css">

<div class="loader">
	<div class="loader-border">
		<span class="loader-inner"></span>
	</div>
</div>
<?php include_once("dist/layouts/navbar.php"); ?>
<div class="container">

 <div class="alert-text text-danger" style="display: none;" role="alert"></div>
</div>

<?php include_once("dist/layouts/js.php") ?>

<!-- Custom form validations -->
<script>
	function kyc_logout() {
		var pass0 = {};
		pass0.op = 'logout';
		url1 = "http://localhost/inventory/api/inventoryApi.php";
			// url: "http://michael1.cp35.secserverpros.com/inventory/api/inventoryApi.php",
		$.ajax({
			url:url1,
			method: 'POST',
			data: pass0,
			beforeSend: function (xhr) {
				$('.loader').css('display', 'flex');
			},
			complete: function (xhr, status) {
				$('.loader').css('display', 'none');
			},
			success: function (response, xhr, status) {
				console.log(response);
				response = JSON.parse(response);
				status = response['responseStatus']; // if SUCCESS return content array
				console.log(response);
				if (status == 'OK') { // login SUCCESS
					location.href = 'login.php';
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
