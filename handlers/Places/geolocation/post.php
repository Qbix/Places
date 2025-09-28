<?php

/**
 * @module Places
 */

/**
 * Used to set the user's location from geolocation data.
 * @class HTTP Places geolocation
 * @method post
 * @param $_REQUEST
 * @param {double} [$_REQUEST.latitude] The new latitude. If set, must also specify longitude.
 * @param {double} [$_REQUEST.longitude] The new longitude. If set, must also specify latitude.
 * @param {string} [$_REQUEST.postcode] The new zip code. Can be set instead of latitude, longitude.
 * @param {double} [$_REQUEST.meters] The distance around their location around that the user is interested in
 * @param {Number} [$_REQUEST.joinNearby=0] Pass 1 to join to the Places/nearby stream at the new location. Pass 2 to join and subscribe.
 * @param {Number} [$_REQUEST.leaveNearby=0] Pass 1 to unsubscribe from the Places/nearby stream at the old location. Pass 2 to unsubscribe and leave.
 * @param {Number} [$_REQUEST.joinInterests=0] Pass 1 to join to all the local interests at the new location. Pass 2 to join and subscribe.
 * @param {Number} [$_REQUEST.leaveInterests=0] Whether to unsubscribe from all the local interests at the old location. Pass 2 to unsubscribe and leave.
 * @param {double} [$_REQUEST.accuracy]
 * @param {double} [$_REQUEST.altitude]
 * @param {double} [$_REQUEST.altitudeAccuracy]
 * @param {double} [$_REQUEST.heading]
 * @param {double} [$_REQUEST.speed]
 * @param {integer} [$_REQUEST.timezone]
 * @param {string} [$_REQUEST.placeName] optional
 * @param {string} [$_REQUEST.state] optional
 * @param {string} [$_REQUEST.country] optional
 */
function Places_geolocation_post()
{
	$user = Users::loggedInUser(true);

	// call common logic
	Places_Location::changed(array_merge($_REQUEST, array(
		'user'   => $user,
		'source' => 'geolocation'
	)));

	// Only HTTP handler should send the response and keep going
	$timeLimit = Q_Config::get('Places','geolocation','timeLimit',100000);
	ignore_user_abort(true);
	set_time_limit($timeLimit);
	Q_Dispatcher::response(true);
	session_write_close();
}