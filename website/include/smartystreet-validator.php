<?
require_once('validator.php');

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
	private static function maybe_add_auth_params( &$params ) {
		echo "smartystreet maybe_add_auth_params x\n";
		if ( !isset( $params['auth-id'] ) && defined('SMARTYSTREET_AUTH_ID') ) {
			$params['auth-id'] = SMARTYSTREET_AUTH_ID;
			echo "smartystreet auth-id x\n", $params['auth-id'];
		}
		if ( !isset( $params['auth-token'] ) && defined('SMARTYSTREET_AUTH_TOKEN') ) {
			$params['auth-token'] = SMARTYSTREET_AUTH_TOKEN;
			echo "smartystreet auth-token x\n", $params['auth-token'];
		}
	}

	/**
	 * Constructor for Validator derived class.
	 *
	 * @see Validator::__construct($field, $value)
	 */
	public function __construct($field, $params = null) {
		if ( is_array( $params ) )
			self::maybe_add_auth_params( $params );
		else throw new Exception('Syntax error; $params is not an array.');
		parent::__construct($field, $params);
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
		echo "smartystreet validating x\n";
		if ( is_array( $params ) )
			self::maybe_add_auth_params( $params );

		print_r( $params );

		// account for params
		echo "smartystreet accounting for id x\n";
		if ( !isset($params['auth-id']) || empty($params['auth-id']) )
			return 'The SmartyStreets Auth ID isn\'t specified.';

		echo "smartystreet accounting for token x\n";
		if ( !isset($params['auth-token']) || empty($params['auth-token']) )
			return 'The SmartyStreets Auth Token isn\'t specified.';

		echo "smartystreet accounting for city x\n";
		if ( !isset($params['city']) || empty($params['city']) )
			return 'You must specify a city.';

		echo "smartystreet accounting for state x\n";
		if ( !isset($params['state']) || empty($params['state']) )
			return 'You must specify a state.';

		echo "smartystreet accounting for zipcode x\n";
		if ( !isset($params['zipcode']) || empty($params['zipcode']) )
			return 'You must specify a zip code.';

		echo "smartystreet preparing request x\n";

		// remove any additional info in the array
		$params = array_intersect_key( $params, array(
				'auth-id' => 1,
				'auth-token' => 1,
				'city' => 1,
				'state' => 1,
				'zipcode' => 1,
			));

		// encode for posting to smartystreets
		$post_input = json_encode( $params );
		//$query = http_build_query($params);
		// "https://api.smartystreets.com/zipcode?$query"

		if ( function_exists( 'curl_init' ) ) {
			// cURL module route

			$c = curl_init('https://api.smartystreets.com/zipcode');
			curl_setopt_array( $c, array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_HTTPHEADER => array('Content-type: application/json'),
					CURLOPT_POSTFIELDS => $post_input
				) );

			echo "smartystreet performing request x\n";

			// perform the post, get the result
			$result_json = curl_exec($c);
			
		} else if ( class_exists( 'HTTPRequest' ) ) {
			// PECL HTTPRequest route

			$post_input = json_encode( $params );

			// referenced code:
			// https://github.com/smartystreets/LiveAddressSamples/blob/master/php/post_optimized_pecl.php

			// TODO: exception handling!
			$req = new HTTPRequest('https://api.smartystreets.com/zipcode', HTTP_METH_POST);
			$req->setBody($post_input);

			echo "smartystreet performing request x\n";

			$resp = $req->send();
			$result_json = $resp->getBody();

		} else {
			// streaming context route; requires openssl support built into php!

			// http://www.php.net/manual/en/context.http.php
			$ctx = stream_context_create( array (
					'http' => array (
						'method' => 'POST',
						'header' => 'Content-Type: application/json\r\n',
						'content' => $post_input,
					),
				) );

			$result_json = file_get_contents( "https://api.smartystreets.com/zipcode", false, $ctx );

		}
		die("ffs!");


		echo "smartystreet performed request x\n";

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