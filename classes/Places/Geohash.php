<?php
/**
 * Geohash
 *
 * @author      Keisuke SATO
 * @license     MIT License
 * 
 * # Based
 * http://github.com/davetroy/geohash-js/blob/master/geohash.js
 * Geohash library for Javascript
 * Copyright © 2008 David Troy, Roundhouse Technologies LLC
 * Distributed under the MIT License
 **/
class Places_Geohash
{
    static private $bits = array(16, 8, 4, 2, 1);
    static private $base32 = '0123456789bcdefghjkmnpqrstuvwxyz';
    static private $neighbors = array(
        'top' => array('even' => 'bc01fg45238967deuvhjyznpkmstqrwx',),
        'bottom' => array('even' => '238967debc01fg45kmstqrwxuvhjyznp',),
        'right' => array('even' => 'p0r21436x8zb9dcf5h7kjnmqesgutwvy',),
        'left' => array('even' => '14365h7k9dcfesgujnmqp0r2twvyx8zb',),
    );
    static private $borders = array(
        'top' => array('even' => 'bcfguvyz'),
        'bottom' => array('even' => '0145hjnp'),
        'right' => array('even' => 'prxz'),
        'left' => array('even' => '028b'),
    );

	/**
	 * Encode latitude and longitude into a geohash.
	 * @param float $latitude
	 * @param float $longitude
	 * @param int|null $length Length of hash (default 12)
	 * @return string
	 */
	static public function encode(float $latitude, float $longitude, ?int $length = 12): string
	{
		$lat_interval = [-90.0, 90.0];
		$lon_interval = [-180.0, 180.0];
		$is_even = true;
		$bit = 0;
		$ch = 0;
		$geohash = '';

		while (strlen($geohash) < $length) {
			if ($is_even) {
				$mid = ($lon_interval[0] + $lon_interval[1]) / 2;
				if ($longitude > $mid) {
					$ch |= self::$bits[$bit];
					$lon_interval[0] = $mid;
				} else {
					$lon_interval[1] = $mid;
				}
			} else {
				$mid = ($lat_interval[0] + $lat_interval[1]) / 2;
				if ($latitude > $mid) {
					$ch |= self::$bits[$bit];
					$lat_interval[0] = $mid;
				} else {
					$lat_interval[1] = $mid;
				}
			}

			$is_even = !$is_even;

			if ($bit < 4) {
				$bit++;
			} else {
				$geohash .= self::$base32[$ch];
				$bit = 0;
				$ch = 0;
			}
		}
		return $geohash;
	}

	/**
	 * Decode a geohash into latitude/longitude (center + error).
	 * @param string $geohash
	 * @return array ['latitude' => float, 'longitude' => float, 'error' => ['lat' => float, 'lon' => float]]
	 */
	static public function decode(string $geohash): array
	{
		$lat_interval = [-90.0, 90.0];
		$lon_interval = [-180.0, 180.0];
		$is_even = true;

		foreach (str_split($geohash) as $char) {
			$cd = strpos(self::$base32, $char);
			for ($mask = 16; $mask >= 1; $mask /= 2) {
				if ($is_even) {
					$mid = ($lon_interval[0] + $lon_interval[1]) / 2;
					if ($cd & $mask) {
						$lon_interval[0] = $mid;
					} else {
						$lon_interval[1] = $mid;
					}
				} else {
					$mid = ($lat_interval[0] + $lat_interval[1]) / 2;
					if ($cd & $mask) {
						$lat_interval[0] = $mid;
					} else {
						$lat_interval[1] = $mid;
					}
				}
				$is_even = !$is_even;
			}
		}

		$latitude = ($lat_interval[0] + $lat_interval[1]) / 2;
		$longitude = ($lon_interval[0] + $lon_interval[1]) / 2;
		$error = [
			'lat' => ($lat_interval[1] - $lat_interval[0]) / 2,
			'lon' => ($lon_interval[1] - $lon_interval[0]) / 2
		];

		return ['latitude' => $latitude, 'longitude' => $longitude, 'error' => $error];
	}

    /**
     * Call this function to find adjacent hashes
     * @method adjacent
     * @static
	 * @param {string} $hash currently only works for hashes of even length
	 * @param {string} $dir could be "top", "right", "bottom", "left"
	 * @return {string}
     */
    static function adjacent($hash, $dir){
        /***
            eq('xne', Places_Geohash::adjacent('xn7', 'top'));
            eq('xnk', Places_Geohash::adjacent('xn7', 'right'));
            eq('xn5', Places_Geohash::adjacent('xn7', 'bottom'));
            eq('xn6', Places_Geohash::adjacent('xn7', 'left'));
        */
        $hash = strtolower($hash);
        $last = substr($hash, -1);
        // $type = (strlen($hash) % 2)? 'odd': 'even';
        $type = 'even'; //FIXME
        $base = substr($hash, 0, strlen($hash) - 1);
        if(strpos(self::$borders[$dir][$type], $last) !== false){
            $base = self::adjacent($base, $dir);
        }
		$neighbors = strpos(self::$neighbors[$dir][$type], $last);
        return $base. self::$base32[$neighbors];
    }
    
    static private function refine_interval(&$interval, $cd, $mask){
        $interval[($cd & $mask)? 0: 1] = ($interval[0] + $interval[1]) / 2;
    }

    /**
     * Use this method to fetch database rows and order them by geohash distance
     * from a given center.
     * @method fetchByDistance
     * @static
     * @param {Db_Query} $query A database query, generated with Table_Class::select(),
     *  to extend and run fetchDbRows() on
     * @param {string} $field The name of the field to test
     * @param {string} $center A geohash that represents the center point
     * @param {integer} $limit The number of items to return, at most
     * @param {boolean} $skipDecoding If true, the distance will not be calculated
     *  using the haversine formula, instead the geohashes themselves will be compared.
     *  This is faster, but less accurate.
     * @return {array} An array of Db_Row objects sorted by increasing distance from center
     */
    static function fetchByDistance($query, $field, $center, $limit, $skipDecoding = false)
    {
        $above = clone $query;
        $above = $above->where(array(
            $field => new Db_Range($center, true, false, null)
        ))->orderBy($field, true)->fetchAll();

        $below = clone $query;
        $below = $below->where(array(
            $field => new Db_Range(null, false, false, $center)
        ))->orderBy($field, false)->fetchAll();

        $result = array();
        $i = $j = $k = 0;
        $a = count($above);
        $b = count($below);

        // merge rows until limit reached
        while ($k < $limit && $i < $a && $j < $b) {
            if (self::closer($center, $above[$i], $below[$j])) {
                $result[] = $above[$i++];
            } else {
                $result[] = $below[$j++];
            }
            ++$k;
        }
        while ($k < $limit && $i < $a) {
            $result[] = $above[$i++];
            ++$k;
        }
        while ($k < $limit && $j < $b) {
            $result[] = $below[$j++];
            ++$k;
        }

        if (!$skipDecoding) {
            // decode center geohash
            list($lat0, $lon0) = Places_Geohash::decode($center);

            // annotate rows with haversine distance
            foreach ($result as $row) {
                list($lat1, $lon1) = Places_Geohash::decode($row->geohash);
                $dist = Places::distance($lat0, $lon0, $lat1, $lon1);
                $row->set('Places/distance', $dist);
            }

            // sort rows by annotated distance
            usort($result, array('Places_City', 'compareByDistance'));
        }

        return array_slice($result, 0, $limit);
    }

    private static function compareByDistance($a, $b)
    {
        $da = $a->get('Places/distance');
        $db = $b->get('Places/distance');
        if ($da == $db) return 0;
        return ($da < $db) ? -1 : 1;
    }


    private static function closer($center, $a, $b) {
    	$cn = self::alpha2num($center);
    	$an = self::alpha2num($a);
    	$bn = self::alpha2num($b);
    	return abs($an - $cn) < abs($bn - $cn);
    }

	/**
	 * Converts an alphabetic string into an integer.
	 * @param int $n This is the number to convert.
	 * @return string The converted number.
	 * @author Theriault
	 */
	private static function alpha2num($a) {
		$r = 0;
		$l = strlen($a);
		for ($i = 0; $i < $l; $i++) {
			$r += pow(26, $i) * (ord($a[$l - $i - 1]) - 0x30);
		}
		return $r;
	}
}