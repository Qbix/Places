<?php

function Places_after_Q_session_write($params, $result)
{
	// Only act if this was a *new* session
	if (empty($params['new']) || empty($result)) {
		return $result;
	}

	// Grab IP info from request
	list($ip, $protocol, $isPublic) = Q_Request::ip();
	if (!$ip || !$isPublic) {
		return $result; // nothing useful
	}

	// Only proceed if we have a logged-in user
	$user = Users::loggedInUser();
	if (!$user) {
		return $result;
	}

	// Base payload
	$data = array(
		'ip'       => $ip,
		'protocol' => $protocol
	);

	// Add location lookup data if available
	$lookup = array();
	try {
		if ($protocol === 'v6') {
			$lookup = Places_Ipv6::lookup($ip, array('join' => array('postcode')));
		} elseif ($protocol === 'v4') {
			$lookup = Places_Ipv4::lookup($ip, array('join'=>array('postcode')));
		}
		if ($lookup) {
			$postcode = $lookup->get('Places/postcode');
			$city = $lookup->get('Places/city');
			$data = array_merge(
				$data, 
				$lookup->fields, 
				$postcode ? $postcode->fields : array()
			);
			$data['geohash'] = Places_Geohash::encode($lookup->fields['latitude'], $lookup->fields['longitude']);
			unset($data['ipMin']);
			unset($data['ipMax']);
		}
	} catch (Exception $e) {
		Q::log("Places: failed IP lookup for $ip ($protocol): ".$e->getMessage(), 'warn');
	}

	// Save/update the stream
    $stream = Streams_Stream::fetchOrCreate($user->id, $user->id, 'Places/user/location/ip');
    $stream->setAttribute($data);
    $stream->changed();

	return $result;
}