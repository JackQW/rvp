<?
require_once('name-validator.php');

/**
 * Validates a last name.
 *
 * @author Tyler B. Young
 * @see LastNameValidator::validate($arg)
 */
class LastNameValidator extends NameValidator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function __init() {
		return "LastName";
	}

	/**
	 * Validates a last name. Allows all printable characters.
	 *
	 * @example O'Farley VI
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return self::validate_name( $arg, 'Last name' );
	}
}

LastNameValidator::init();

?>