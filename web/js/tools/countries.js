(function (Q, window, document, undefined) {

var Places = Q.Places;

/**
 * Displays a dropdown to select one or more countries, with optional globe synchronization
 * @class Places countries
 * @constructor
 * @param {Object} [options] Configuration options
 * @param {String} [options.countryCode=null] The initial ISO-3166-1 alpha-2 country code to select
 * @param {Array} [options.firstCountryCodes=['US']] Country codes to appear at the top of the list
 * @param {Boolean} [options.sort=false] If true, countries are sorted alphabetically
 * @param {Q.Tool|String|null} [options.globe=null] A reference or ID of a "Places/globe" tool to synchronize with
 * @param {Boolean} [options.localized=false] Whether to display localized country names (requires language)
 * @param {String|null} [options.language=null] Language code (e.g. "fr", "es") to localize country names
 * @param {Q.Event} [options.onReady] Fired when the selector is fully initialized
 * @param {Q.Event} [options.onChange] Fired when the selected country changes.
 *   Callback arguments: `(countryCode)`
 */
Q.Tool.define("Places/countries", function _Places_countries(options) {
	var tool = this;
	var state = tool.state;
	var el = tool.element;

	if (typeof state.globe === 'string') {
		state.globe = Q.Tool.byId(state.globe);
	}

	state.countryCode = state.countryCode && state.countryCode.toUpperCase();
	tool.optionsByCode = {};

	// match jQuery’s css('position')
	if (getComputedStyle(el).position === "static") {
		el.style.position = "relative";
	}

	Places.loadCountries(function () {
		// Build <select>
		var select = tool.select = Q.element("select", {
			'class': "Places_countries_select"
		});
		el.appendChild(select);

		if (!state.countries) {
			state.countries = [];
			for (var k in Places.countries) {
				state.countries.push(k);
			}
		}

		tool.refresh();

		select.addEventListener("change",
			Q.preventRecursion("Places/countries onchange", function () {
				var countryCode = select.value || state.countryCode;
				if (state.globe) {
					state.globe.rotateToCountry(countryCode);
				}
				Q.handle(state.onChange, tool, [countryCode]);
			})
		);

		Q.handle(state.onReady, tool);
	}, {
		localized: state.localized,
		language: state.language
	});

	tool.Q.onStateChanged("countryCode").set(function () {
		var globe = this.state.globe;
		var countryCode = this.state.countryCode;
		this.select.value = countryCode;
		this.select.dispatchEvent(new Event("change"));
		if (globe) {
			globe.rotateToCountry(countryCode);
		}
	}, "Places/countries");

	if (state.globe) {
		this.globe(state.globe);
	}
},

{ // defaults
	flags: "{{Places}}/img/squareflags",
	countryCode: null,
	firstCountryCodes: ['US'],
	globe: null,
	sort: false,
	onChange: new Q.Event(),
	onReady: new Q.Event(),
	language: null,
	localized: false
},

{ // methods

	setCountry: Q.preventRecursion("Places/countries setCountry", function (countryCode) {
		this.state.countryCode = countryCode;
		this.stateChanged("countryCode");
	}),

	globe: function (globeTool) {
		if (!globeTool) {
			this.state.globe = null;
			return;
		}
		this.state.globe = globeTool;
		var tool = this;
		globeTool.state.beforeRotateToCountry.set(function (countryCode) {
			tool.setCountry(countryCode);
		}, true);
	},

	refresh: function () {
		this.state.onReady.add(function () {
			var tool = this;
			var state = tool.state;
			var select = tool.select;
			select.innerHTML = "";

			if (state.countryCode) {
				select.value = state.countryCode;
				select.dispatchEvent(new Event("change"));
			} else {
				var placeholder = Q.element("option", {
					value: "",
					disabled: true,
					selected: true
				}, [tool.text.countries.SelectCountry]);
				select.insertBefore(placeholder, select.firstChild);
				select.value = "";
			}

			var codes = {};
			Q.each(state.firstCountryCodes, function (i, countryCode) {
				var flag = Places.countries[countryCode][3];
				var name = Places.countries[countryCode][0];
				var text = flag + " " + name;
				var option = Q.element("option", { value: countryCode }, [text]);
				select.appendChild(option);
				tool.optionsByCode[countryCode] = option;
				codes[countryCode] = true;
			});

			if (state.sort) {
				state.countries.sort(function (a, b) {
					var a1 = Places.countries[a][0];
					var b1 = Places.countries[b][0];
					return a1 > b1 ? 1 : (a1 === b1 ? 0 : -1);
				});
			}

			Q.each(state.countries, function (i, countryCode) {
				if (codes[countryCode]) return;
				var flag = Places.countries[countryCode][3];
				var name = Places.countries[countryCode][0];
				var text = flag + " " + name;
				var option = Q.element("option", { value: countryCode }, [text]);
				select.appendChild(option);
				tool.optionsByCode[countryCode] = option;
			});
		}, this);
	}
});

})(Q, window, document);