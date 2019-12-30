<?php
session_start();

require_once('lib/config.php');

// LOGIN
$username = $config['login_username'];
$hashed_password = $config['login_password'];
$error = false;

if (isset($_POST['submit'])) {
    if (isset($_POST['username']) && $_POST['username'] == $username && isset($_POST['password']) && password_verify($_POST["password"], $hashed_password)) {
        //IF USERNAME AND PASSWORD ARE CORRECT SET THE LOG-IN SESSION
        $_SESSION['auth'] = true;
    } else {
        // DISPLAY FORM WITH ERROR
        $error = true;
    }
}
// END LOGIN


?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=no">
	<meta name="msapplication-TileColor" content="<?=$config['colors']['primary']?>">
	<meta name="theme-color" content="<?=$config['colors']['primary']?>">

	<title>Photobooth Login</title>

	<!-- Favicon + Android/iPhone Icons -->
	<link rel="apple-touch-icon" sizes="180x180" href="resources/img/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="resources/img/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="resources/img/favicon-16x16.png">
	<link rel="manifest" href="resources/img/site.webmanifest">
	<link rel="mask-icon" href="resources/img/safari-pinned-tab.svg" color="#5bbad5">

	<!-- Fullscreen Mode on old iOS-Devices when starting photobooth from homescreen -->
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />

	<link rel="stylesheet" href="node_modules/normalize.css/normalize.css" />
	<link rel="stylesheet" href="node_modules/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="node_modules/photoswipe/dist/photoswipe.css" />
	<link rel="stylesheet" href="node_modules/photoswipe/dist/default-skin/default-skin.css" />
	<link rel="stylesheet" href="resources/css/login.css" />
	<?php if ($config['rounded_corners']): ?>
	<link rel="stylesheet" href="resources/css/rounded.css" />
	<?php endif; ?>
</head>

<body class="deselect">
	<div id="wrapper">
		<div class="login-panel">
			<h2>Photobooth Login</h2>
			<hr>
			<?php if( !$config['login_enabled'] || (isset($_SESSION['auth']) && $_SESSION['auth'] === true)):
			header("location: ../admin/index.php");
			exit;
			else: ?>
			<form method='post' class="login">
				<label for="username"><span data-l10n="login_username"></span></label>
				<input type="text" name="username" id="username" autocomplete="on" required>
				<label for="password"><span data-l10n="login_password"></span></label>
				</br>
				<input type="password" name="password" id="password" autocomplete="on" required>
				</br></br>
				<input type="submit" name="submit" value="Login" class="btn btn--tiny btn--flex">
				<?php if ($error !== false) {
					echo '<p style="color: red;"><span data-l10n="login_invalid"></span></p>';
				} ?>
			</form>
			<?php endif; ?>
			</br>
			<a class="btn btn--tiny btn--flex" href="login.php"><i class="fa fa-refresh"></i> <span data-l10n="reload"></span></a>
			<a class="btn btn--tiny btn--flex" id="close-btn" href="./"><i class="fa fa-times"></i> <span data-l10n="close"></span></a>
		</div>


		<div id="adminsettings">
			<div style="position:absolute; bottom:0; right:0;">
				<img src="resources/img/spacer.png" alt="adminsettings" ondblclick="adminsettings()" />
			</div>
		</div>

	</div>

	<script type="text/javascript" src="api/config.php"></script>
	<script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
	<script type="text/javascript" src="resources/js/adminshortcut.js"></script>
	<script type="text/javascript" src="resources/js/l10n.js"></script>
	<script type="text/javascript" src="resources/js/theme.js"></script>
	<script type="text/javascript" src="resources/lang/<?php echo $config['language']; ?>.js"></script>
</body>
</html>