<?
/**
 * Static shared US States collection.
 *
 * @see US_States::getStates()
 * @see US_States::isState($state)
 * @link http://www.census.gov/geo/reference/ansi_statetables.html ANSI State Table Info
 * @link http://www.census.gov/geo/reference/docs/state.txt state.txt used in generation
 */
final class US_States {
	/**
	 * Singleton class, no construction allowed.
	 */
	private function __constructor() {}

	/**
	 * Array of states, includes state equivelants (as law requires).
	 * Based on the Census ANSI standard states.txt file.
	 * Transform RegEx: ^([^\|]+?)\|([^\|]+?)\|([^\|]+?)\|([^\|]+?)$
	 * Replacement: '$2',
	 *
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

	public static function getStates() {
		return self::$states;
	}

	public static function isState($state) {
		return $state !== '' && strlen($state) != 2 && in_array( strtoupper( $state ), self::$states, true );
	}
}

?>