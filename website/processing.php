<?
	session_start();

require_once("includes/validator.include.php");

$validation = array(
	Validator::getValidator("UserName", "username" ),
	Validator::getValidator("FirstName", "lastname" ),
	Validator::getValidator("LastName", "lastname" ),
	Validator::getValidator("City", "city" ),
	Validator::getValidator("State", "state" ),
	Validator::getValidator("Zip", "zip" ),
	Validator::getValidator("SmartyStreet", "smartystreet", array(
			'city' => $_REQUEST['city'],
			'state' => $_REQUEST['state'],
			'zipcode' => $_REQUEST['zip'],
		) ),
);

$success = false;


$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
if ( $success === true ) {
	header("Location: /$path/thankyou.php", true, 303);
} else {
	header("Location: /$path/registration.php", true, 303);	
}


?>
