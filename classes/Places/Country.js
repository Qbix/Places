/**
 * Class representing country rows.
 *
 * This description should be revised and expanded.
 *
 * @module Places
 */
var Q = require('Q');
var Db = Q.require('Db');
var Country = Q.require('Base/Places/Country');

/**
 * Class representing 'Country' rows in the 'Places' database
 * @namespace Places
 * @class Country
 * @extends Base.Places.Country
 * @constructor
 * @param {Object} fields The fields values to initialize table row as
 * an associative array of {column: value} pairs
 */
function Places_Country (fields) {

	// Run mixed-in constructors
	Places_Country.constructors.apply(this, arguments);
	
	/*
 	 * Add any privileged methods to the model class here.
	 * Public methods should probably be added further below.
	 */
}

Q.mixin(Places_Country, Country);

/*
 * Add any public methods here by assigning them to Places_Country.prototype
 */

/**
 * The setUp() method is called the first time
 * an object of this class is constructed.
 * @method setUp
 */
Places_Country.prototype.setUp = function () {
	// put any code here
	// overrides the Base class
};

module.exports = Places_Country;