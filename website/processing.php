<?
	session_start();

// get current time
$start_processing = microtime(true);

require_once("includes/validator.include.php");

$valid = true;
for ( array(
		Validator::getValidator("UserName", "username" ),
		Validator::getValidator("Password", "password" ),
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
		) as $field, $validator ) {
	if ( $validator->valid() !== true )
		$valid = false;
}

$end_processing = microtime(true);
$processing_time = $end_processing - $start_processing;

$_SESSION['server_status'] = true;

if ( $valid ) {
	// TODO: sql registration
	$db = @new mysqli( 'p:localhost', MYSQL_USER, MYSQL_PASS, 'proj4rv');
	if ( $db->connect_errno !== 0 ) {
		// TODO: handle connect failure
	}
	if (!($stmt = $db->prepare('INSERT INTO `proj4rv`.`user` (`username`, `password`, `email`, `first_name`, `last_name`, `city`, `state`, `zip`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);')) {
		// TODO: handle prepare failure
	}
	$stmt->bind_param("ssssssss",
		$_REQUEST['username'],
		md5($_REQUEST['password'], true),
		$_REQUEST['email'],
		$_REQUEST['first_name'],
		$_REQUEST['last_name'],
		$_REQUEST['city'],
		$_REQUEST['state'],
		$_REQUEST['zip'], // CHAR(10)
	);
	$stmt->bind_param("s", $_REQUEST['password']);
	$stmt->bind_param("s", $_REQUEST['username']);
	$stmt->bind_param("s", $_REQUEST['username']);
	$stmt->bind_param("s", $_REQUEST['username']);


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
