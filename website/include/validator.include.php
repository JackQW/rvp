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
	 * @param $field A field name to use for feedback.
	 * @param $val A value to be validated.
	 */
	protected __constructor( $field, $val = null ) {
		if ( !is_string($fieldName) || $fieldName === '' )
			throw new Exception('Specify a valid field name!');
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
	 * @param string $arg The value to validate.
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

/**
 * Validates a username.
 * @see UserNameValidator::validate($arg)
 */
class UserNameValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function init() {
		return "UserName";
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
	 * Validates a username. Allows up to 16 uppercase, lowercase, number, and underscore characters.
	 *
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		// 16 characters; letters numbers underscores
		return ($arg !== '' && preg_match('/^[[:word:]]{1,16}$/', $arg )) ||
			"Your username must be between 1 and 16 characters (inclusive).\n".
			"Allowed characters are letters (upper and lowercase) and numbers.";
	}
}

/**
 * Validates a password.
 * @see PasswordValidator::validate($arg)
 */
class PasswordValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function init() {
		return "Password";
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
	 * Validates a password. Allows all characters.
	 *
	 * @example Derp de derp!1
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		// minimums: 1 digit, 1 uppercase, 1 lowercase, 1 punctuation, 7 total characters
		return ( $arg !== '' && preg_match('/^(?=[[:digit:]])(?=[[:upper:]])(?=[[:lower:]])(?=[[:punct:]]).{7,}$/', $arg)) ||
			"Your password does not meet the security requirements.\n".
			"Please use at least 1 digit, 1 uppercase, 1 lowercase, and 1 punctuation characters.\n".
			"Your password must be at least 7 total characters in length.";
	}
}

/**
 * Validates an email address.
 * @see EmailValidator::validate($arg)
 */
class EmailValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function init() {
		return "Email";
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

/**
 * Validates a zip code.
 * @see ZipValidator::validate($arg)
 */
class ZipValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function init() {
		return "Zip";
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

/**
 * Validates a city name.
 * @see CityValidator::validate($arg)
 */
class CityValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function init() {
		return "City";
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

/**
 * Validates a name of some kind.
 * @see NameValidator::validate_name($arg)
 */
class NameValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function init() {
		return "Name";
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
	 * Validates a typed name. Allows all printable characters.
	 *
	 * @example Sir Pablo
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	protected static function validate_name( $arg, $type ) {
		if ( strlen($arg) > 255 )
			return "Your $type name must be less than 255 characters (for storage reasons).";
		return ($arg !== '' && preg_match('/^[[:graph:]][[:print:]]*$/', $arg)) ||
			"$type must be only printable characters, and atleast 1 printable character long.\n".
			"It may not start with a space.";
	}

	/**
	 * Validates a name. Allows all printable characters.
	 *
	 * @example O'Farley VI
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return validate_name( $arg, 'Name' );
	}
}

/**
 * Validates a last name.
 * @see LastNameValidator::validate($arg)
 */
class LastNameValidator extends NameValidator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function init() {
		return "LastName";
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
	 * Validates a last name. Allows all printable characters.
	 *
	 * @example O'Farley VI
	 * @param string $arg The value to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static function validate( $arg ) {
		return validate_name( $arg, 'Last name' );
	}
}

/**
 * Validates a first name.
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


/**
 * Validates a state.
 * @see StateValidator::validate($arg)
 */
class StateValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function init() {
		return "State";
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
	 * Associative array of states, includes state equivelants (as law requires).
	 * Could
	 * Based on the Census ANSI standard states.txt file.
	 * Transform RegEx: ^([^\|]+?)\|([^\|]+?)\|([^\|]+?)\|([^\|]+?)$
	 * Replacement: '$2',
	 *
	 * @see state( $arg )
	 * @link http://www.census.gov/geo/reference/ansi_statetables.html ANSI State Table Info
	 * @link http://www.census.gov/geo/reference/docs/state.txt state.txt used in generation
	 */
	static $states = array(
		'AL',
		'AK',
		'AZ',
		'AR',
		'CA',
		'CO',
		'CT',
		'DE',
		'DC',
		'FL',
		'GA',
		'HI',
		'ID',
		'IL',
		'IN',
		'IA',
		'KS',
		'KY',
		'LA',
		'ME',
		'MD',
		'MA',
		'MI',
		'MN',
		'MS',
		'MO',
		'MT',
		'NE',
		'NV',
		'NH',
		'NJ',
		'NM',
		'NY',
		'NC',
		'ND',
		'OH',
		'OK',
		'OR',
		'PA',
		'RI',
		'SC',
		'SD',
		'TN',
		'TX',
		'UT',
		'VT',
		'VA',
		'WA',
		'WV',
		'WI',
		'WY',
		'AS',
		'GU',
		'MP',
		'PR',
		'UM',
		'VI',
	);

	public static getStates() {
		return $states;
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
		return ($arg !== '' && strlen($arg) != 2 && in_array( $states, $arg, true )) ||
			'Sorry, the state you specified is not a valid known state.';
	}
}



/**
 * Validates a city, state, and zip combination.
 * @see SmartyStreetValidator::validate($params)
 */
class SmartyStreetValidator extends Validator {
	/**
	 * Registers the class in the validator factory.
	 * Uses late static binding to returns field type.
	 * Call by init on parent {@link Validator} class.
	 */
	protected static function init() {
		return "SmartyStreet";
	}

	/**
	 * Constructor for Validator derived class.
	 *
	 * @see Validator::__construct($field, $value)
	 */
	public __constructor($field, $params = null) {
		parent::__constructor($field, $params);
	}


	/*
	Request format (GET URL params, POST JSON object params)

	https://api.smartystreets.com/zipcode
	city=los+angeles
	state=california
	zipcode=90230
	auth-id=<id>
	auth-token=<token>

	*/

	/**
	 * Validates a city, state, and zip code combination.
	 * This is the static form of the operation of the class.
	 *
	 * @link https://smartystreets.com/account/keys
	 * @param string id Auth ID from SmartyStreets API.
	 * @param string token Auth Token from SmartyStreets API.
	 * @param string $city The city to validate.
	 * @param string $state The state to validate.
	 * @param string $zip The zip code to validate.
	 * @return true|string True if the value was valid, or an error message if not.
	 */
	public static validate( $params ) {
		// account for info
		if ( !isset($params['city']) || empty($params['city']) )
			return 'You must specify a city.';
		if ( !isset($params['state']) || empty($params['state']) )
			return 'You must specify a state.';
		if ( !isset($params['zip']) || empty($params['zip']) )
			return 'You must specify a zip code.';
		if ( !isset($params['auth-id']) || empty($params['auth-id']) )
			return 'The SmartyStreets Auth ID isn\'t specified.';
		if ( !isset($params['auth-token']) || empty($params['auth-token']) )
			return 'The SmartyStreets Auth Token isn\'t specified.';

		// remove any additional info in the array
		$params = array_intersect_key( $params, array(
				'city' => 1,
				'state' => 1,
				'zip' => 1,
				'auth-id' => 1,
				'auth-token' => 1,
			));

		// encode for posting to smartystreets
		$post_input = json_encode( $params );

		$c = curl_init('https://api.smartystreets.com/zipcode');
		curl_setopt_array( $c, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => array('Content-type: application/json'),
				CURLOPT_POSTFIELDS => $post_input
			) );
		// perform the post, get the result
		$result_json = curl_exec($c);

		if ( $result_json === false )
			return 'Unable to get a result from SmartyStreets at this time.';

		// handle the feedback
		$results = json_decode( $result_json );
		if ( count( $results ) === 1 ) {
			$result = $results[0];
			if ( isset( $result->status ) ) {
				$status = $result->status;
				$reson = isset( $result->reason ) ? $result->reason : 'Unknown';
				if ( $status === 'blank' ) // should not encounter in scope of project
					return 'The city, state, and/or zip field was left blank.';
				if ( $status === 'invalid_zipcode' )
					return 'The specified zip code is invalid.';
				if ( $status === 'invalid_state' )
					return 'The specified state is invalid.';
				if ( $status === 'invalid_city' )
					return 'The specified city is invalid.';
				if ( $status === 'conflict' )
					return 'There is a conflict with the provided city, state, and zip code combination.';
				return "The SmartyStreets ZipCode API returned an known status: $status\n$reason";
			}
			return true;
		}
		//$s = '';
		// TODO: parse results into specifics to append to error message.
		/*
		foreach ( $results as $result ) {
			$result->city_states
		}*/
		return 'The location you specified is ambiguous, please be more specific.';

	}

}

// current smarty street auth tokens
//	'4f1dc143-5dd3-440d-ab15-977aa759c001',
//	'imFvFTg8mF0Ka0321Ejg5c2yykLzYxVHDrosWsAwG8SNWAXfMx/7sVH9wNhBaybSgWDoq6Q5kAKOhrM7Yh1r+Q==');


?>