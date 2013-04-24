<?
require_once('validator.include.php');

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
	public static function init() {
		return "State";
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
	 * Validates a 2-letter ANSI uppercase state code.
	 * Does not validate the state's formal name.
	 *
	 * @example SC
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return US_States::isState($arg) ||
			'Sorry, the state you specified is not a valid known state.';
	}
}


?>