<?php
class Places_Ipv6 extends Base_Places_Ipv6
{
    /**
     * Lookup IPv6 address and return matching row, optionally joined with city/country/postcode.
     * @method lookup
     * @static
     * @param {string} $ip IPv6 string (e.g. "2001:4860:4860::8888")
     * @param {array} [$options=array()] Optional arguments:
     *   @param {array} [$options.join=array()] Which related tables to join.
     *     Can include "postcode", "city", "country".
     * @return {Db_Row|null} Row object with optional joined data attached, or null if not found.
     */
    static function lookup($ip, $options = array())
    {
        $packed = @inet_pton($ip); // 16-byte binary
        if ($packed === false) {
            return null;
        }

        // Step 1: candidate by ipMin (fast with index)
        $query = self::select()
            ->where(array(
                'ipMin' => new Db_Range(null, false, false, $packed) // ipMin <= $packed
            ))
            ->orderBy('ipMin')
            ->limit(1);

        $row = $query->fetchDbRow();

        // Step 2: check ipMax condition
        if (!$row || $row->ipMax < $packed) {
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
                    'postcode'    => $row->postcode
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

    function setUp()
    {
        parent::setUp();
        // define hasOne/hasMany if needed
    }

    static function __set_state(array $array) {
        $result = new Places_Ipv6();
        foreach($array as $k => $v)
            $result->$k = $v;
        return $result;
    }
};