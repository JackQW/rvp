<?
require_once('validator.include.php');

/**
 * Validates a username.
 * @see UserNameValidator::validate($arg)
 */
class UserNameValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	public static function init() {
		return "UserName";
	}

	/**
	 * Constructor for Validator derived class.
	 *
	 * @see Validator::__construct($field, $value)
	 */
	public function __constructor( $field, $val = null ) {
		parent::__construct($field, $val);
	}

	/**
	 * Validates a username. Allows up to 16 uppercase, lowercase, number, and underscore characters.
	 *
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		// 16 characters; letters numbers underscores
		return ($arg !== '' && preg_match('/^[[:word:]]{1,16}$/', $arg )) ||
			"Your username must be between 1 and 16 characters (inclusive).\n".
			"Allowed characters are letters (upper and lowercase) and numbers.";
	}
}

?>