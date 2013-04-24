<?
/**
 * Base class from which all other validators derive basic functionality
 *
 * @author Tyler B. Young
 */
class Validator {

	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to call init on referenced subclass.
	 * Referenced subclass returns field type.
	 */
	static function init() {
		$className = get_called_class();
		if ( method_exists($className,'init') )
			$validatorClasses[$className] = static::init();
	}

	static $validatorClasses = array();

	/**
	 * The field name suffix in $_SESSION variable to provide feedback to.
	 * Used as such; $_SESSION[ 'vfb_$fieldName' ]
	 *
	 * @see validateValue()
	 */
	protected $fieldName;

	/**
	 * The value to be validated.
	 *
	 * @see validateValue()
	 */
	protected $value = null;


	/**
	 * Instances a registered validator.
	 * Can easily be extended for class auto-loading.
	 *
	 * @param string $field A field name to use for feedback.
	 * @param mixed $val A value to be validated.
	 */
	static function getValidator( $type, $field, $val = null ) {
		$class = array_search( $validatorClasses, $type, true );
		if ( $class === false ) return false;
		return new $class( $field, $val );
	}

	/**
	 * Although the Validator class is not abstract, it is not intended
	 * to be constructed outside of sub-classes except via factory.
	 * Default value is fetched from $_REQUEST[$fieldName]
	 * Feedback from validation is stored in $_SESSION[ 'vfb_$fieldName' ].
	 *
	 * @param string $field A field name to use for feedback.
	 * @param mixed $val A value to be validated.
	 */
	protected __constructor( $field, $val = null ) {
		if ( !is_string($fieldName) || $fieldName === '' )
			return; // possibly a special validator
		$fieldName = $field;
		if ( $val === null )
			if ( isset( $_REQUEST[$fieldName] ) && !empty($_REQUEST[$fieldName]) )
				$value = $_REQUEST[$fieldName];
		$value = $val;
		if ( $value !== null )
			validateValue();
	}
	
	/**
	 * Validates a value.
	 *
	 * @param mixed $arg The value to validate. Likely a string or array.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return static::validate( $arg ); // Late static binding!
	}

	/**
	 * Validates the stored value.
	 * Stores feedback in $_SESSION[ 'vfb_$fieldName' ]
	 *
	 * @return true|string The value associated with the validator.
	 */
	public function validateValue() {
		$result = validate( $value );
		if ( $fieldName !== '' && $result === true ) {
			$_SESSION[ "vfb_$fieldName" ] = $result;
		} else {
			unset( $_SESSION[ "vfb_$fieldName" ] );
		}
		return $result;
	}
}

// default includes
// generated: dir /a /b *-validator.include.php
// TODO: autoload
require_once('city-validator.include.php');
require_once('email-validator.include.php');
require_once('firstname-validator.include.php');
require_once('lastname-validator.include.php');
require_once('name-validator.include.php');
require_once('password-validator.include.php');
require_once('smartystreet-validator.include.php');
require_once('state-validator.include.php');
require_once('username-validator.include.php');
require_once('zip-validator.include.php');

?>