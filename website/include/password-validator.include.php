<?
require_once('validator.include.php');

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
	 * Constructor for Validator derived class.
	 *
	 * @see Validator::__construct($field, $value)
	 */
	public function __construct( $field, $val = null ) {
		parent::__construct($field, $val);
	}

	/**
	 * Validates a password. Allows all characters.
	 *
	 * @example Derp de derp!1
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return ( $arg !== '' && preg_match('/^(?=[[:digit:]])(?=[[:upper:]])(?=[[:lower:]])(?=[[:punct:]]).{7,}$/', $arg) ) ? true :
			"Your password does not meet the security requirements.\n".
			"Please use at least 1 digit, 1 uppercase, 1 lowercase, and 1 punctuation characters.\n".
			"Your password must be at least 7 total characters in length.";
	}
}

PasswordValidator::init();

?>