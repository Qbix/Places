<?php
/**
 * @module Places
 */
/**
 * Class representing 'City' rows in the 'Places' database
 * You can create an object of this class either to
 * access its non-static methods, or to actually
 * represent a city row in the Places database.
 *
 * @class Places_City
 * @extends Base_Places_City
 */
class Places_City extends Base_Places_City
{
	/**
	 * Fetch nearest cities by geohash distance.
	 * @method fetchByDistance
	 * @static
	 * @param {string} $geohash Geohash to search around
	 * @param {integer} $limit Maximum number of results (default 10)
	 * @param {array} [$options] Additional options
	 * @param {integer} [$options.population=0] Only return cities with at least this population
	 * @param {string} [$options.featureCode] Only return cities with this featureCode
	 * @param {string} [$options.countryCode] Only return cities with this countryCode
	 * @param {boolean} [$options.skipDecoding=false] If true, skips haversine distance
	 * @return {array} Array of Places_City objects sorted by increasing distance
	 */
	static function fetchByDistance($geohash, $limit = 10, $options = array())
	{
		$query = self::select();
		$field = 'geohash';
		$skipDecoding = Q::ifset($options, 'skipDecoding', false);
		$population = Q::ifset($options, 'population', 0);
		if ($population > 0) {
			$query = $query->where(array(
				'population' => new Db_Range($population, true)
			));
		}
		foreach (array('featureCode', 'countryCode') as $opt) {
			if (!empty($options[$opt])) {
				$query = $query->where(array($opt => $options[$opt]));
			}
		}
		return Places_Geohash::fetchByDistance($query, $field, $geohash, $limit, $skipDecoding);
	}

	/**
	 * The setUp() method is called the first time
	 * an object of this class is constructed.
	 * @method setUp
	 */
	function setUp()
	{
		parent::setUp();
		// INSERT YOUR CODE HERE
		// e.g. $this->hasMany(...) and stuff like that.
	}

	/*
	 * Add any Places_City methods here, whether public or not
	 */
	 
	/**
	 * Implements the __set_state method, so it can work with
	 * with var_export and be re-imported successfully.
	 * @method __set_state
	 * @static
	 * @param {array} $array
	 * @return {Places_City} Class instance
	 */
	static function __set_state(array $array) {
		$result = new Places_City();
		foreach($array as $k => $v)
			$result->$k = $v;
		return $result;
	}
};