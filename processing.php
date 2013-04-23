<?
	session_start();

require_once("includes/validator.include.php");

$validation = array(
	new UserNameValidator("username"),
	new EmailValidator("email"),
	new FirstNameValidator("firstname"),
	new LastNameValidator("lastname"),
	new CityValidator("city"),
	new StateValidator("state"),
	new ZipValidator("zip"),
);



$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
if ( $success === true ) {
	header("Location: /$path/thankyou.php", true, 303);
} else {
	header("Location: /$path/registration.php", true, 303);	
}


?>
