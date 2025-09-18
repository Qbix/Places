<?php

function Places_before_Db_Row_Metrics_Visit_saveExecute($params)
{
    if (!Q_Config::get('Metrics', 'visit', 'enrich', 'location', false)) {
        return $params['query'];
    }

    $row   = $params['row'];
    $query = $params['query'];

    // Only enrich on insert
    if ($params['inserted'] && $row->IP && Q_Request::isPublicIP($row->IP)) {
        $place = Places::lookupFromRequest(array(
            'join' => array('city', 'country', 'postcode')
        ));
        if ($place) {
            $updates = array();

            if (!empty($place->countryCode)) {
                $row->countryCode = $place->countryCode;
                $updates['countryCode'] = $place->countryCode;
            }
            if (!empty($place->city) && !empty($place->city->geonameId)) {
                $row->geonameId = $place->city->geonameId;
                $updates['geonameId'] = $place->city->geonameId;
            }
            if (!empty($place->postcode) && !empty($place->postcode->postcode)) {
                $row->postcode = $place->postcode->postcode;
                $updates['postcode'] = $place->postcode->postcode;
            }

            // Mutate query as well, so the INSERT reflects these
            if (!empty($updates)) {
                $query->set($updates);
            }
        }
    }

    return $query; // must always return the (possibly modified) query
}