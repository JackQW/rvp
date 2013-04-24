<?
	session_start();

	require_once("includes/us-states.include.php");

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
function display_feedback( $field ) {
	if ( isset($_SESSION[$field]) && !empty($_SESSION[$field]) ) {
		?><span><?= $_SESSION[$field]; ?></span><?
	}
}

?>
<!DOCTYPE html>
<?xml-stylesheet href="style.css" ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta charset="UTF-8" />
		<title>Registration</title>
	</head>
	<body>
		<div class="box reg">
			<form action="processing.php" method="post"><?
				// Development references:
				// https://developer.mozilla.org/en-US/docs/HTML/Forms_in_HTML
				// http://wiki.whatwg.org/wiki/Autocomplete_Types
				?>
				<div id="processing-feedback">
					<? display_feedback( "server_status" ); ?>
					<? display_feedback( "vfb_server" ); ?>
					<? display_feedback( "vfb_firstname" ); ?>
					<? display_feedback( "vfb_lastname" ); ?>
					<? display_feedback( "vfb_city" ); ?>
					<? display_feedback( "vfb_state" ); ?>
					<? display_feedback( "vfb_zip" ); ?>
					<? display_feedback( "vfb_email" ); ?>
					<? display_feedback( "vfb_username" ); ?>
					<? display_feedback( "vfb_smartystreet" ); ?>
				</div>
				<input type="text" name="firstname" autocomplete="given-name" placeholder="First Name" required="true" autofocus="true" <? request_input_value("firstname"); ?> />
				<input type="text" name="lastname" autocomplete="family-name" placeholder="Last Name" required="true" <? request_input_value("lastname"); ?> />
				<input type="text" name="city" autocomplete="city" placeholder="City" required="true" <? request_input_value("city"); ?> />
				<select name="state" autocomplete="state" required="true">
				<?
					foreach ( $state as US_States::getStates() ) {
						?><option value="<?= $state; ?>" <?
							if ( isset($_REQUEST['state']) && $_REQUEST['state'] == $state )
								?>selected="true"<?
						?>><?= $state; ?></option><?
					}
				?>
				</select>
				<input type="text" name="zip" autocomplete="postal-code" placeholder="Zip Code" required="true" <? request_input_value("zip"); ?> />
				<input type="email" name="email" autocomplete="email" placeholder="Email Address" required="true" <? request_input_value("email"); ?> />
				<input type="text" name="username" autocomplete="nickname" placeholder="User Name" required="true" <? request_input_value("username"); ?> />
			</form>
		</div>
	</body>
</html>