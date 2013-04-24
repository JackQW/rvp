<?
require_once('validator.php');
require_once('us-states.php');

/**
 * Validates a state.
 *
 * @author Tyler B. Young
 * @see StateValidator::validate($arg)
 */
class StateValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function __init() {
		return "State";
	}

	/**
	 * Validates a 2-letter ANSI uppercase state code.
	 * Does not validate the state's formal name.
	 *
	 * @example SC
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return US_States::isState($arg) ? true :
			'Sorry, the state you specified is not a valid known state.';
	}
}

StateValidator::init();


?>