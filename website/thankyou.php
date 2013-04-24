<?
	session_start();
	
	header("Content-Type: application/xhtml+xml; charset=utf-8");
	header("Vary: Accept");

	if ( !isset($_SESSION['registered']) || $_SESSION['registered'] !== true ) {
		$ssl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '';
		$host = $_SERVER['HTTP_HOST'];
		$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		header("Location: http$ssl://$host/$path/registration.php", true, 307);
		die();
	}

?>
<!DOCTYPE html>
<<??>?xml-stylesheet href="style.css" ?<??>>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta charset="UTF-8" />
		<title>Thank You</title>
	</head>
	<body>
		<div class="box thx">
			<h1>Thank you!</h1>
			<p>
				Thank you for registering, <?= $_SESSION['username']; ?>!<br/>
				It took us <? printf('%.3f', $_SESSION['processing_time']); ?> seconds to process your registration.<br/>
				Have a nice day!
			</p>
		</div>
	</body>
</html>