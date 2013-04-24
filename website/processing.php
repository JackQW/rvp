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


$_SESSION['server_status'] = true;
$success = false;

if ( $valid ) {
	// TODO: sql registration
	$db = @new mysqli( 'p:localhost', MYSQL_USER, MYSQL_PASS, 'proj4rv');

	if ( $db->connect_errno !== 0 ) {
		// TODO: handle connect failure
	}

	if (!($istmt = $db->prepare('INSERT INTO `user` (`username`, `password`, `email`, `first_name`, `last_name`, `city`, `state`, `zip`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);')) {
		// TODO: handle prepare failure
	}

	$istmt->bind_param("ssssssss",
		$_REQUEST['username'], // CHAR(16)
		md5($_REQUEST['password'], true), // BINARY(16)
		$_REQUEST['email'], // VARCHAR(255)
		$_REQUEST['first_name'], // VARCHAR(255)
		$_REQUEST['last_name'], // VARCHAR(255)
		$_REQUEST['city'], // VARCHAR(255)
		$_REQUEST['state'], // CHAR(2)
		$_REQUEST['zip'], // CHAR(10)
	);

	$istmt->execute();

	$errno = $istmt->errno;
	// Error: 1169 SQLSTATE: 23000 (ER_DUP_UNIQUE)
	// Message: Can't write, because of unique constraint, to table '%s'
	// http://dev.mysql.com/doc/refman/5.5/en/error-messages-server.html#error_er_dup_unique
	if ( $errno === 0 ) {
		$success = true;
	} else if ( $errno === 1169 ) {
		// check which unique constraint got hit
		if(!($db->prepare('SELECT COUNT(`username`) AS `uhits` FROM `user` WHERE `username` = ? UNION ALL SELECT COUNT(`email`) AS `uhits` FROM `user` WHERE `email` = ?')) {
			// TODO: handle prepare failure
		}
		
		$_SESSION['server_status'] = "Sorry, that User Name was already taken.";
		$_SESSION['server_status'] = "Sorry, that Email was already used.";
	} else {
		$_SESSION['server_status'] = "Encountered error during SQL transaction: $istmt->error";
	}


}

$end_processing = microtime(true);
$processing_time = $end_processing - $start_processing;
$_SESSION['processing_time'] = $processing_time;


$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
if ( $success === true ) {
	header("Location: /$path/thankyou.php", true, 303);
} else {
	header("Location: /$path/registration.php", true, 303);	
}
die();


?>
