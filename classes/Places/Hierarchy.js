/**
 * Class representing hierarchy rows.
 *
 * This description should be revised and expanded.
 *
 * @module Places
 */
var Q = require('Q');
var Db = Q.require('Db');
var Hierarchy = Q.require('Base/Places/Hierarchy');

/**
 * Class representing 'Hierarchy' rows in the 'Places' database
 * @namespace Places
 * @class Hierarchy
 * @extends Base.Places.Hierarchy
 * @constructor
 * @param {Object} fields The fields values to initialize table row as
 * an associative array of {column: value} pairs
 */
function Places_Hierarchy (fields) {

	// Run mixed-in constructors
	Places_Hierarchy.constructors.apply(this, arguments);
	
	/*
 	 * Add any privileged methods to the model class here.
	 * Public methods should probably be added further below.
	 */
}

Q.mixin(Places_Hierarchy, Hierarchy);

/*
 * Add any public methods here by assigning them to Places_Hierarchy.prototype
 */

/**
 * The setUp() method is called the first time
 * an object of this class is constructed.
 * @method setUp
 */
Places_Hierarchy.prototype.setUp = function () {
	// put any code here
	// overrides the Base class
};

module.exports = Places_Hierarchy;