<?
	session_start();


	require_once('include/us-states.php');

	header("Content-Type: application/xhtml+xml; charset=utf-8");
	header("Vary: Accept");

/**
 * If the field is in the postback or a get var, output it as an input field value.
 *
 * @internal Helper function for repetative bits.
 * @param string $field
 */
function request_input_value( $field ) {
	if ( isset($_REQUEST[ $field ]) && !empty($_REQUEST[ $field ]) ) {
		?>value="<?= $_REQUEST[$field]; ?>"<?
	}
}

/**
 * If the field has a validator feedback session variable, output it as a span.
 *
 * @internal Helper function for repetative bits.
 * @param string $field
 */
function display_feedback( $field, $format = null ) {
	if ( isset($_SESSION[$field]) && !empty($_SESSION[$field]) && $_SESSION[$field] !== true ) {
		if ( is_string($format) && !empty($format) ) {
			?><pre data-debug="<?= $field; ?>"><? printf( $format, $_SESSION[$field] ); ?></pre><?
		} else {
			?><pre data-debug="<?= $field; ?>"><?= $_SESSION[$field]; ?></pre><?
		}
	}
}

?>
<!DOCTYPE html>
<<??>?xml-stylesheet href="style.css" ?<??>>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta charset="UTF-8" />
		<title>Registration</title>
	</head>
	<body>
		<div class="box reg">
			<form action="processing.php" method="post">
				<?
				// Development references:
				// https://developer.mozilla.org/en-US/docs/HTML/Forms_in_HTML
				// http://wiki.whatwg.org/wiki/Autocomplete_Types
				?>
				<div id="processing-feedback">
					<?
					//print_r( $_SESSION );
					display_feedback( 'server_status' );
					display_feedback( 'vfb_firstname' );
					display_feedback( 'vfb_lastname' );
					display_feedback( 'vfb_city' );
					display_feedback( 'vfb_state' );
					display_feedback( 'vfb_zip' );
					display_feedback( 'vfb_email' );
					display_feedback( 'vfb_username' );
					display_feedback( 'vfb_password' );
					display_feedback( 'vfb_smartystreet' );
					if ( isset($_SESSION['registered']) && $_SESSION['registered'] === true ) {
						?><span>You've already registered with us!<br/>Feel free to register again though.</span><?
					}
					//display_feedback( 'processing_time', 'It took %.3f seconds to process your previous attempt.' );
					?>
				</div>
				<label>First Name: <input type="text" name="firstname" autocomplete="given-name" placeholder="First Name" required="true" autofocus="true" <? request_input_value("firstname"); ?> /></label>
				<label>Last Name: <input type="text" name="lastname" autocomplete="family-name" placeholder="Last Name" required="true" <? request_input_value("lastname"); ?> /></label>
				<label>City: <input type="text" name="city" autocomplete="locality" placeholder="City" required="true" <? request_input_value("city"); ?> /></label>
				<label>State: <select name="state" autocomplete="region" required="true">
					<option></option><? // hardcode blank option

					$states = US_States::getStates();
					$rstate = '';
					if ( isset($_REQUEST['state']) )
						$rstate = $_REQUEST['state'];

					foreach ( $states as $state) {
						?><option value="<?= $state; ?>"<?
							if ( $rstate === $state ) {
								?> selected="true"<?
							}
						?>><?= $state; ?></option><?
					}
				?>
				</select></label>
				<label>Zip Code: <input type="text" name="zip" autocomplete="postal-code" placeholder="Zip Code" required="true" <? request_input_value("zip"); ?> /></label>
				<label>Email: <input type="email" name="email" autocomplete="email" placeholder="Email Address" required="true" <? request_input_value("email"); ?> /></label>
				<label>User Name: <input type="text" name="username" autocomplete="nickname" placeholder="User Name" required="true" <? request_input_value("username"); ?> /></label>
				<label>Password: <input type="password" name="password" autocomplete="off" required="true" <? request_input_value("password"); ?> /></label>
				<input type="submit" value="Register" />
			</form>
		</div>
	</body>
</html>