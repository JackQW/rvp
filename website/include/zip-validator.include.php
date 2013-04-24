<?
require_once('validator.include.php');

/**
 * Validates a zip code.
 *
 * @author Tyler B. Young
 * @see ZipValidator::validate($arg)
 */
class ZipValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function __init() {
		return "Zip";
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
	 * Validates a zip (or zip+4) code.
	 *
	 * @example 90210-1010
	 * @link http://stackoverflow.com/questions/160550/zip-code-us-postal-code-validation
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return ($arg !== '' &&preg_match('/(^\d{5}$)|(^\d{5}-\d{4}$)/', $arg)) ||
			'Zip codes must be a series of 5 numbers, optionally followed by a dash and 4 more numbers.';
	}
}

ZipValidator::init();

?>