<?
require_once('name-validator.php');

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
	protected static function __init() {
		return "FirstName";
	}
	
	/**
	 * Validates a first name. Allows all printable characters.
	 *
	 * @example John-Chris
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return self::validate_name( $arg, 'Last name' );
	}
}

FirstNameValidator::init();

?>