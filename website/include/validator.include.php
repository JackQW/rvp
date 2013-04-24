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
	protected static function __init() {
		$className = get_called_class();
		if ( $className !== get_class() )
			self::$validatorClasses[$className] = static::__init();
	}

	public static function init() {
		self::__init();
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
	 * The validation result.
	 *
	 * @see validateValue()
	 */
	protected $result = null;

	/**
	 * Instances a registered validator.
	 * Can easily be extended for class auto-loading.
	 *
	 * @param string $type The field type to be validated.
	 * @param string $field A field name to use for feedback.
	 * @param mixed $val (optional) A value to be validated.
	 */
	static function getValidator( $type, $field, $val = null ) {
		$class = array_search( $type, self::$validatorClasses, true );
		if ( $class === false ) {
			echo "'$type' is not a registered validator.\nRegistered validator types:\n";
			print_r(self::$validatorClasses);
			die();
		}
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
	protected function __constructor( $field, $val = null ) {
		if ( !is_string($field) || $field === '' )
			return; // possibly a special validator
		$this->fieldName = $field;
		if ( $val === null )
			if ( isset( $_REQUEST[$this->fieldName] ) && !empty($_REQUEST[$this->fieldName]) )
				$this->value = $_REQUEST[$this->fieldName];
		$this->value = $val;
		if ( $this->value !== null )
			$this->validateValue();
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
	 * Stores feedback in $_SESSION[ 'vfb_$fieldName' ] as well.
	 *
	 * @return true|string The value associated with the validator.
	 */
	public function validateValue() {
		$this->result = self::validate( $this->value );
		$vfbfn = "vfb_$this->fieldName";
		if ( $this->fieldName !== '' && $this->result === true ) {
			$_SESSION[ $vfbfn ] = $this->result;
		} else {
			unset( $_SESSION[ $vfbfn ] );
		}
		return $this->result;
	}

	/**
	 * Returns the result of the last validation, or null.
	 * If true, validation succeeded.
	 * If null, no validation performed.
	 * If a string, validation reports an error.
	 *
	 * @return null|true|string The result of the last validation, or null.
	 */
	public function valid() {
		if ( !isset($this->result) )
			return $this->validateValue();
		return $this->result;
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