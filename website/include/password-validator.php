<?
require_once('validator.php');

/**
 * Validates a password.
 * @see PasswordValidator::validate($arg)
 */
class PasswordValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function __init() {
		return "Password";
	}

	/**
	 * Validates a password. Allows all characters.
	 *
	 * @example Derp de derp!1
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return ( $arg !== '' && preg_match('/^(?=.*?[[:digit:]])(?=.*?[[:upper:]])(?=.*?[[:lower:]])(?=.*?[[:punct:]]).{7,}$/', $arg) ) ? true :
			"Your password does not meet the security requirements.\n".
			"Please use at least 1 character of each:\n".
			"\ta number,\n\ta punctuation mark or symbol,\n\tan uppercase letter,\n\tand lowercase letter.\n".
			"Your password must be at least 7 total characters in length.";
	}
}

PasswordValidator::init();

?>