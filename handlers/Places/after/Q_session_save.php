<?php

function Places_after_Q_session_save($params, $result)
{
	// Only act if this was a *new* session
	if (empty($params['new']) || empty($result)) {
		return $result;
	}

	// Grab IP info from request
	list($ip, $protocol, $isPublic) = Q_Request::ip();
	if (!$ip) {
		return $result; // nothing useful
	}

	// Only proceed if we have a logged-in user
	$userId = Users::loggedInUserId();
	if (!$userId) {
		return $result;
	}

	// Base payload
	$data = array(
		'ip'       => $ip,
		'protocol' => $protocol,
		'isPublic' => $isPublic,
		'from'     => 'ip'
	);

	// Add location lookup data if available
	$lookup = array();
	try {
		if ($protocol === 'v6') {
			$lookup = Places_Ipv6::lookup($ip, array());
		} elseif ($protocol === 'v4') {
			$lookup = Places_Ipv4::lookup($ip, array());
		}
		if (is_array($lookup)) {
			$data = array_merge($data, $lookup);
		}
	} catch (Exception $e) {
		Q::log("Places: failed IP lookup for $ip ($protocol): ".$e->getMessage(), 'warn');
	}

	// Save/update the stream
    $stream = Streams_Stream::fetchOrCreate($userId, $userId, 'Places/user/location/ip');
    $stream->setAttribute($data);
    $stream->changed();

	return $result;
}