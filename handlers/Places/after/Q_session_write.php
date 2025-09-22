<?php

function Places_after_Q_session_write($params)
{
	// Only act if this was a new session
	$row = Q::ifset($params, 'row', null);
	if (!$row or !$row->get('ipWasJustSet')) {
		return $result;
	}

	// Grab IP info from request
	$data = $row->get('ipWasJustSet');
	if (empty($data['ip']) || empty($data['isPublic'])) {
		return $result; // nothing useful
	}
	$ip = $data['ip'];
	$protocol = $data['protocol'];

	// Only proceed if we have a logged-in user
	$user = Users::loggedInUser();
	if (!$user) {
		return $result;
	}

	// Base payload
	unset($data['isPublic']);

	// Add location lookup data if available
	$lookup = array();
	try {
		$className = 'Places_Ip' . $protocol;
		$lookup = call_user_func(
			array($className, 'lookup'),
			array('join' => array('postcode'))
		);
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