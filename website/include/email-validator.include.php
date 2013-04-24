<?
require_once('validator.include.php');

/**
 * Validates an email address.
 *
 * @author Tyler B. Young
 * @see EmailValidator::validate($arg)
 */
class EmailValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	public static function init() {
		return "Email";
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
	 * Validates an email address based on RFC 5321.
	 * Intentionally vague with the error message, as email validation can get complicated.
	 * Requires PHP 5.3.
	 * <snide>Like I want to copy that huge freakin' RegEx or talk to an SMTP server.</snide>
	 *
	 * @example derp@john.doe.name
	 * @link http://svn.php.net/viewvc/php/php-src/trunk/ext/filter/logical_filters.c?revision=321634&view=markup PHP 5.3's implementation at time of writing (references RFC 5321)
	 * e.g.; O'Farley XIV, John-Chris.
	 * @link https://tools.ietf.org/html/rfc5321 RFC 5321
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		if ( strlen($arg) > 255 )
			return 'Your email address must be less than 255 characters (for storage reasons).';
		return ($arg !== '' && filter_var( $arg, FILTER_VALIDATE_EMAIL )) ||
			'Your email address does not appear to be valid.';
	}
}

?>