<?
require_once('validator.include.php');

/**
 * Validates a first name.
 *
 * @author Tyler B. Young
 * @see FirstNameValidator::validate($arg)
 */
class FirstNameValidator extends NameValidator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function init() {
		return "FirstName";
	}

	/**
	 * Constructor for Validator derived class.
	 *
	 * @see Validator::__construct($field, $value)
	 */
	public __constructor( $field, $val = null ) {
		parent::__construct($field, $val);
	}
	
	/**
	 * Validates a first name. Allows all printable characters.
	 *
	 * @example John-Chris
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return validate_name( $arg, 'Last name' );
	}
}

?>