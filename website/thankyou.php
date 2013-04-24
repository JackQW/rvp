<?
	session_start();
	
	header("Content-Type: application/xhtml+xml; charset=utf-8");
	header("Vary: Accept");

	if ( !$_SESSION['registered'] ) {
		header("HTTP/1.0 404 Not Found");
		die();
	}

?>
<!DOCTYPE html>
<?xml-stylesheet href="style.css" ?>
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
				It took us <? printf('%.3f', $_SESSION['processing_time']); ?> to process your registration.<br/>
				Have a nice day!
			</p>
		</div>
	</body>
</html>