<?
	session_start();

// get current time
$start_processing = microtime(true);

require_once("includes/validator.include.php");

$valid = true;
for ( array(
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
		) as $validator ) {
	if ( $validator->valid() !== true )
		$valid = false;
}

$end_processing = microtime(true);
$processing_time = $end_processing - $start_processing;

$_SESSION['server_status'] = true;

if ( $valid ) {
	// TODO: sql registration
}

$_SESSION['processing_time'] = $processing_time;

$success = false;


$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
if ( $success === true ) {
	header("Location: /$path/thankyou.php", true, 303);
} else {
	header("Location: /$path/registration.php", true, 303);	
}
die();


?>
