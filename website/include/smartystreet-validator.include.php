<?
require_once('validator.include.php');

// TODO: move these into a config file or something
define('SMARTYSTREET_AUTH_ID', '4f1dc143-5dd3-440d-ab15-977aa759c001');
define('SMARTYSTREET_AUTH_TOKEN', 'imFvFTg8mF0Ka0321Ejg5c2yykLzYxVHDrosWsAwG8SNWAXfMx/7sVH9wNhBaybSgWDoq6Q5kAKOhrM7Yh1r+Q==');


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
	protected static function __init() {
		return "SmartyStreet";
	}

	/**
	 * Augments an associated array with the SmartyStreet authentication constants.
	 *
	 * @param array $params
	 */
	private static function maybe_add_auth_params( $params ) {
		if ( !isset( $params['auth-id'] ) && defined('SMARTYSTREET_AUTH_ID') ) {
			$params['auth-id'] = SMARTYSTREET_AUTH_ID;
		}
		if ( !isset( $params['auth-token'] ) && defined('SMARTYSTREET_AUTH_TOKEN') ) {
			$params['auth-token'] = SMARTYSTREET_AUTH_TOKEN;
		}
	}

	/**
	 * Constructor for Validator derived class.
	 *
	 * @see Validator::__construct($field, $value)
	 */
	public function __constructor($field, $params = null) {
		if ( is_array( $params ) )
			self::maybe_add_auth_params( $params );
		else throw new Exception('Syntax error; $params is not an array.');
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
	public static function validate( $params ) {
		if ( is_array( $params ) )
			self::maybe_add_auth_params( $params );

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

SmartyStreetValidator::init();


?>