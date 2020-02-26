<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION["user"])) {
	header("location: login.php");
}

include_once("layouts/_head.php");

?>

<link rel="stylesheet" href="css/login.css">

<div class="loader">
	<div class="loader-border">
		<span class="loader-inner"></span>
	</div>
</div>
<?php include_once("layouts/navbar.php"); ?>
<div class="container">

</div>

<?php include_once("layouts/js.php") ?>

<!-- Custom form validations -->
<script>

</script>

<?php include_once("layouts/_foot.php") ?>
