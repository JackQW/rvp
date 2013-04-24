<?
require_once('validator.include.php');

/**
 * Validates a city name.
 *
 * @author Tyler B. Young
 * @see CityValidator::validate($arg)
 */
class CityValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	public static function init() {
		return "City";
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
	 * Validates a city. Allows letters and spaces.
	 *
	 * @example New York
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return ($arg !== '' && preg_match('/^[[:alpha:]][:alpha:] ]*$', $arg)) ||
			'City must be only letters and spaces, and atleast 1 non-space character long.';
	}
}

?>