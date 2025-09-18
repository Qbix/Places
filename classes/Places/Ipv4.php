<?php
/**
 * @module Places
 */
/**
 * Class representing 'Ipv4' rows in the 'Places' database
 * You can create an object of this class either to
 * access its non-static methods, or to actually
 * represent a ipv4 row in the Places database.
 *
 * @class Places_Ipv4
 * @extends Base_Places_Ipv4
 */
class Places_Ipv4 extends Base_Places_Ipv4
{
    /**
     * Lookup IPv4 address and return matching row, optionally joined with city/country/postcode.
     * @method lookup
     * @static
     * @param {string} $ip IPv4 string (e.g. "8.8.8.8")
     * @param {array} [$options=array()] Optional arguments:
     *   @param {array} [$options.join=array()] Which related tables to join.
     *     Can include "postcode", "city", "country".
     * @return {Db_Row|null} Row object with optional joined data attached, or null if not found.
     */
    static function lookup($ip, $options = array())
    {
        $ipNum = sprintf('%u', ip2long($ip)); // convert to unsigned int
        if ($ipNum === false) {
            return null;
        }

        $query = self::select()
            ->where(array(
                'ipMin' => new Db_Range(null, false, false, $ipNum),
                'ipMax' => new Db_Range($ipNum, false, false, null)
            ))
            ->limit(1);

        $row = $query->fetchRow();
        if (!$row) {
            return null;
        }

        $joins = !empty($options['join']) ? $options['join'] : array();
        $geoId = $row->geonameId;
        $cc    = $row->countryCode;

        // join with postcode
        if (in_array('postcode', $joins) && $row->postcode) {
            $pc = Places_Postcode::select()
                ->where(array(
                    'countryCode' => $cc,
                    'postcode' => $row->postcode
                ))
                ->limit(1)
                ->fetchRow();
            if ($pc) {
                $row->set('Places/postcode', $pc);
            }
        }

        // join with city
        if (in_array('city', $joins) && $geoId) {
            $city = Places_City::select()
                ->where(array('geonameId' => $geoId))
                ->limit(1)
                ->fetchRow();
            if ($city) {
                $row->set('Places/city', $city);
            }
        }

        // join with country
        if (in_array('country', $joins) && $cc) {
            $country = Places_Country::select()
                ->where(array('countryCode' => $cc))
                ->limit(1)
                ->fetchRow();
            if ($country) {
                $row->set('Places/country', $country);
            }
        }

        return $row;
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
	 * Add any Places_Ipv4 methods here, whether public or not
	 */
	 
	/**
	 * Implements the __set_state method, so it can work with
	 * with var_export and be re-imported successfully.
	 * @method __set_state
	 * @static
	 * @param {array} $array
	 * @return {Places_Ipv4} Class instance
	 */
	static function __set_state(array $array) {
		$result = new Places_Ipv4();
		foreach($array as $k => $v)
			$result->$k = $v;
		return $result;
	}
};