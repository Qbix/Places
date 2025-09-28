<?php
/**
 * @module Places
 */
/**
 * Class representing 'Region' rows in the 'Places' database
 * You can create an object of this class either to
 * access its non-static methods, or to actually
 * represent a region row in the Places database.
 *
 * @class Places_Region
 * @extends Base_Places_Region
 */
class Places_Region extends Base_Places_Region
{
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
	 * Add any Places_Region methods here, whether public or not
	 */
	 
	/**
	 * Implements the __set_state method, so it can work with
	 * with var_export and be re-imported successfully.
	 * @method __set_state
	 * @static
	 * @param {array} $array
	 * @return {Places_Region} Class instance
	 */
	static function __set_state(array $array) {
		$result = new Places_Region();
		foreach($array as $k => $v)
			$result->$k = $v;
		return $result;
	}
};