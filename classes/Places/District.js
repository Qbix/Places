/**
 * Class representing district rows.
 *
 * This description should be revised and expanded.
 *
 * @module Places
 */
var Q = require('Q');
var Db = Q.require('Db');
var District = Q.require('Base/Places/District');

/**
 * Class representing 'District' rows in the 'Places' database
 * @namespace Places
 * @class District
 * @extends Base.Places.District
 * @constructor
 * @param {Object} fields The fields values to initialize table row as
 * an associative array of {column: value} pairs
 */
function Places_District (fields) {

	// Run mixed-in constructors
	Places_District.constructors.apply(this, arguments);
	
	/*
 	 * Add any privileged methods to the model class here.
	 * Public methods should probably be added further below.
	 */
}

Q.mixin(Places_District, District);

/*
 * Add any public methods here by assigning them to Places_District.prototype
 */

/**
 * The setUp() method is called the first time
 * an object of this class is constructed.
 * @method setUp
 */
Places_District.prototype.setUp = function () {
	// put any code here
	// overrides the Base class
};

module.exports = Places_District;