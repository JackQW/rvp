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
		if ( is_array( $params ) )
			self::maybe_add_auth_params( $params );

		// account for params

		if ( !isset($params['auth-id']) || empty($params['auth-id']) )
			return 'The SmartyStreets Auth ID isn\'t specified.';

		if ( !isset($params['auth-token']) || empty($params['auth-token']) )
			return 'The SmartyStreets Auth Token isn\'t specified.';

		if ( !isset($params['city']) || empty($params['city']) )
			return 'You must specify a city.';

		if ( !isset($params['state']) || empty($params['state']) )
			return 'You must specify a state.';

		if ( !isset($params['zipcode']) || empty($params['zipcode']) )
			return 'You must specify a zip code.';

		// prepare url

		// get just the auth components, remove other
		// see http://smartystreets.com/kb/liveaddress-api/zipcode-api
		// "If used, this value must appear in the query string, not the request body."
		$auth = array_intersect_key( $params, array(
			'auth-id' => 1,
			'auth-token' => 1,
		));

		// http_build_query defaults to RFC1738, desired RFC3986
		$auth_query = null;
		if ( defined('PHP_QUERY_RFC3986') ) {
			// PHP_QUERY_RFC3986 support is php >= 5.4
			$auth_query = http_build_query( $auth, '', '&', PHP_QUERY_RFC3986 );
		} else {
			// emulate RFC3986 via rawurlencode in php < 5.4
			$s = array();
			foreach ( $auth as $k => $v ) {
				$s[] = rawurlencode($k) . '=' . rawurlencode($v);
			}
			$auth_query = implode( '&', $s );
		}
		
		$url = "https://api.smartystreets.com/zipcode?$auth_query";

		// prepare post body

		// remove any additional info in the array
		$params = array_intersect_key( $params, array(
			'city' => 1,
			'state' => 1,
			'zipcode' => 1,
		));

		// encode for posting to smartystreets
		// yes it takes an array of an assoc arrays/objects (eg. [{a=0,...},...] )
		$post_input = json_encode( array( $params ) );

		/* uncomment to try other routes
		 * couldn't get WAMP's curl or pecl working, got openssl working
		if ( function_exists( 'curl_init' ) ) {
			// cURL module route

			$c = curl_init($url);
			curl_setopt_array( $c, array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_HTTPHEADER => array('Content-type: application/json'),
					CURLOPT_POSTFIELDS => $post_input
				) );

			// perform the post, get the result
			$result_json = curl_exec($c);
			
		} else if ( class_exists( 'HTTPRequest' ) ) {
			// PECL HTTPRequest route

			$post_input = json_encode( $params );

			// referenced code:
			// https://github.com/smartystreets/LiveAddressSamples/blob/master/php/post_optimized_pecl.php

			// TODO: exception handling!
			$req = new HTTPRequest($url, HTTP_METH_POST);
			$req->setBody($post_input);

			$resp = $req->send();
			$result_json = $resp->getBody();

		} else {
			// streaming context route; requires openssl support built into php!
		*/
			// http://www.php.net/manual/en/context.http.php
			$ctx = stream_context_create( array (
					'http' => array (
						'method' => 'POST',
						'header' => 'Content-Type: application/json\r\n',
						'content' => $post_input,
					),
				) );

			$result_json = file_get_contents( $url, false, $ctx );

		/* uncomment to try other routes
		}
		*/

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
			// fallthru/else we have a success!
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