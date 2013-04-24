<?
define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', '');
define('PASSWORD_HASH_SALT','aXK0)@@4z$*1');

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
	// ok, so we've got valid input
	// time to run it past MySQL
	// if the insert succeeds, we're done
	// if not, we've got to report the detailed error

	// using persistent connection
	$db = @new mysqli( 'p:'.MYSQL_HOST, MYSQL_USER, MYSQL_PASS, 'proj4rv');

	if ( !isset($db) || mysqli_connect_errno() !== 0 ) {
		$_SESSION['server_status'] = 'Connection to MySQL server failed with error:\n\t'.
			mysqli_connect_error();
	} else {
		// the user insert statement
		$istmt = null;
		if ($istmt = $db->prepare('INSERT INTO `user` (`username`, `password`, `email`, `first_name`, `last_name`, `city`, `state`, `zip`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);') {
			$istmt->bind_param('ssssssss',
				$_REQUEST['username'], // CHAR(16)
				md5(PASSWORD_HASH_SALT.$_REQUEST['password'], true), // BINARY(16)
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
				$success = true; // woo hoo.
			} else if ( $errno === 1169 ) { // check which unique constraint got hit
				// the unique constraint check statement
				$cstmt = null;
				if(!($cstmt = $db->prepare('SELECT COUNT(`username`) AS `uhits` FROM `user` WHERE `username` = ? UNION ALL SELECT COUNT(`email`) AS `uhits` FROM `user` WHERE `email` = ?')) {
					if ( isset($cstmt) ) {
						$_SESSION['server_status'] = "An error occurred while trying to prepare the unique constraint check statement:\n\t$cstmt->error";
					} else {
						$_SESSION['server_status'] = "Unable to instance a prepared SQL statement. Please report this error.";
					}
				} else {
					$cstmt->bind_param('ss',
						$_REQUEST['username'],
						$_REQUEST['email'] );

					$cstmt->bind_result($cresult);
					$stmt->fetch();
					$username_hits = $cresult;
					$stmt->fetch();
					$email_hits = $cresult;
					if ( $username_hits > 0 )
						$_SESSION['server_status'] = "Sorry, that User Name was already taken.";
					else if ( $email_hits > 0 )
						$_SESSION['server_status'] = "Sorry, that Email was already used.";
					else
						$_SESSION['server_status'] = "Sorry, an unhandled unique constraint collision occurred."
				}
			} else {
				$_SESSION['server_status'] = "Encountered error during SQL transaction:\n\t$istmt->error";
			}

		} else {
			if ( isset($istmt) ) {
				$_SESSION['server_status'] = "An error occurred while trying to prepare the insert statement:\n\t$istmt->error\n\tPlease report this error.";
			} else {
				$_SESSION['server_status'] = "Unable to instance a prepared SQL statement. Please report this error.";
			}
		}
	}

}

// report processing time
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
