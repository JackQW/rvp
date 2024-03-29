<?
session_start();

$_SESSION['server_status'] = '';
// get current time
$start_processing = microtime(true);

require_once("include/config.php");
require_once("include/validator.php");

$valid = true;
// could use lambdas to lazy instance validators; helpful to reduce spam during debug
// see SVN history for that variation, removed to reduce clutter
foreach ( array(
		Validator::getValidator("UserName", "username" ),
		Validator::getValidator("Password", "password" ),
		Validator::getValidator("FirstName", "firstname" ),
		Validator::getValidator("LastName", "lastname" ),
		Validator::getValidator("City", "city" ),
		Validator::getValidator("State", "state" ),
		Validator::getValidator("Zip", "zip" ),
		Validator::getValidator("SmartyStreet", "smartystreet", array(
				'city' => isset($_REQUEST['city']) ? $_REQUEST['city'] : '',
				'state' => isset($_REQUEST['state']) ? $_REQUEST['state'] : '',
				'zipcode' => isset($_REQUEST['zip']) ? $_REQUEST['zip'] : '',
			) ),
		) as $validator ) {
	if ( is_string( $validator ) ) // error message
		$_SESSION['server_status'] = $validator;
	if ( $validator->valid() !== true )
		$valid = false;
}

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
		if ($istmt = $db->prepare('INSERT INTO `user` (`username`, `password`, `email`, `first_name`, `last_name`, `city`, `state`, `zip`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);')) {
			$istmt->bind_param('ssssssss',
				$_REQUEST['username'], // CHAR(16)
				md5(PASSWORD_HASH_SALT.$_REQUEST['password'], true), // BINARY(16)
				$_REQUEST['email'], // VARCHAR(255)
				$_REQUEST['firstname'], // VARCHAR(255)
				$_REQUEST['lastname'], // VARCHAR(255)
				$_REQUEST['city'], // VARCHAR(255)
				$_REQUEST['state'], // CHAR(2)
				$_REQUEST['zip'] // CHAR(10)
			);

			// don't care if succeeded, relying on errno being set
			$istmt->execute();

			$errno = $istmt->errno;
			if ( $errno === 0 ) {
				$_SESSION['registered'] = true;
				$_SESSION['username'] = $_SESSION['username'];
				$_SESSION['email'] = $_REQUEST['email'];
				$_SESSION['firstname'] = $_SESSION['firstname'];
				$_SESSION['lastname'] = $_SESSION['lastname'];
				$_SESSION['city'] = $_SESSION['city'];
				$_SESSION['state'] = $_SESSION['state'];
				$_SESSION['zip'] = $_SESSION['zip'];
				$success = true; // woo hoo.
			} else if ( $errno === 1062 ) { // check which unique constraint got hit
				// Error: 1062 SQLSTATE: 23000 (ER_DUP_ENTRY)
				// Message: Duplicate entry '%s' for key %d
				// http://dev.mysql.com/doc/refman/5.5/en/error-messages-server.html#error_er_dup_unique
				// the unique constraint check statement
				$matches = null;
				if (!preg_match("/^Duplicate entry '.*?' for key '(.*?)_UNIQUE'$/", $istmt->error, $matches)) {
					// The error message could change, but it seems pretty fixed
					// Fallback code is documented in subversion history
					$_SESSION['server_status'] = "Encountered error during SQL transaction:\n\t$istmt->error\n\tPlease report this error.";
				} else {
					$uniquehit = $matches[1];
					if ( $uniquehit === 'username' ) {
						$_SESSION['server_status'] = "Sorry, that User Name was already taken.";
					} else if ( $uniquehit === 'email' ) {
						$_SESSION['server_status'] = "Sorry, that Email was already used.";
					} else {
						// standard 'wtfhow? wtfever' case, probs will never hit
						$_SESSION['server_status'] = "Sorry, that $uniquehit was already used.";
					}
				}
			} else {
				$_SESSION['server_status'] = "Encountered error during SQL transaction:\n\t$istmt->error\n\tPlease report this error.";
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

$ssl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '';
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
if ( $success === true ) {
	header("Location: http$ssl://$host$path/thankyou.php", true, 302);
} else {
	header("Location: http$ssl://$host$path/registration.php", true, 307);	
}
die();


?>
