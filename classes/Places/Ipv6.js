/**
 * Class representing ipv6 rows.
 *
 * This description should be revised and expanded.
 *
 * @module Places
 */
var Q = require('Q');
var Db = Q.require('Db');
var Ipv6 = Q.require('Base/Places/Ipv6');

/**
 * Class representing 'Ipv6' rows in the 'Places' database
 * @namespace Places
 * @class Ipv6
 * @extends Base.Places.Ipv6
 * @constructor
 * @param {Object} fields The fields values to initialize table row as
 * an associative array of {column: value} pairs
 */
function Places_Ipv6 (fields) {

	// Run mixed-in constructors
	Places_Ipv6.constructors.apply(this, arguments);
	
	/*
 	 * Add any privileged methods to the model class here.
	 * Public methods should probably be added further below.
	 */
}

Q.mixin(Places_Ipv6, Ipv6);

/*
 * Add any public methods here by assigning them to Places_Ipv6.prototype
 */

/**
 * The setUp() method is called the first time
 * an object of this class is constructed.
 * @method setUp
 */
Places_Ipv6.prototype.setUp = function () {
	// put any code here
	// overrides the Base class
};

module.exports = Places_Ipv6;