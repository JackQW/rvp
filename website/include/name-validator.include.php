<?
require_once('validator.include.php');

/**
 * Validates a name of some kind.
 *
 * @author Tyler B. Young
 * @see NameValidator::validate_name($arg)
 */
class NameValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function __init() {
		return "Name";
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
	 * Validates a typed name. Allows all printable characters.
	 *
	 * @example Sir Pablo
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	protected static function validate_name( $arg, $type ) {
		if ( strlen($arg) > 255 )
			return "Your $type name must be less than 255 characters (for storage reasons).";
		return ($arg !== '' && preg_match('/^[[:graph:]][[:print:]]*$/', $arg)) ||
			"$type must be only printable characters, and atleast 1 printable character long.\n".
			"It may not start with a space.";
	}

	/**
	 * Validates a name. Allows all printable characters.
	 *
	 * @example O'Farley VI
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return validate_name( $arg, 'Name' );
	}
}

NameValidator::init();

?>