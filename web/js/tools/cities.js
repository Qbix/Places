(function (Q, window, document, undefined) {

Q.Tool.define("Places/cities", function Places_cities(options) {
	var tool = this;
	var state = tool.state;

	// Render template into tool element
	Q.Template.render('Places/cities', state).then(html => {
		tool.element.innerHTML = html;
		Q.activate(tool.element, {}, tool); // fills tool.elements
	});

	// Load cities.json
	fetch(Q.url("{{Places}}/data/cities.json"))
		.then(r => r.json())
		.then(json => {
			tool.citiesByCountry = json;
			Q.handle(state.onReady, tool);
		});
}, {
	countryCode: null,
	cityName: null,
	show: "{{ascii}} ({{local}})",
	onReady: new Q.Event(),
	onChange: new Q.Event()
}, {
	refresh: function () {
		var tool = this;
		var state = tool.state;
		if (!tool.citiesByCountry || !state.countryCode || !tool.elements.select) return;

		var cities = tool.citiesByCountry[state.countryCode] || [];
		var selectEl = tool.elements.select;
		selectEl.innerHTML = "";

		if (!cities.length) {
			var opt = document.createElement("option");
			opt.textContent = "(No cities)";
			selectEl.appendChild(opt);
			return;
		}

		cities.forEach(arr => {
			var eng   = arr[0];
			var local = arr[1];
			var opt   = document.createElement("option");
			opt.value = eng;
			opt.textContent = state.show.interpolate({ ascii: eng, local: local });

			if (state.cityName && (state.cityName === eng || state.cityName === local)) {
				opt.selected = true;
			}
			selectEl.appendChild(opt);
		});

		selectEl.onchange = () => {
			Q.handle(state.onChange, tool, [selectEl.value]);
		};
	},
	setCountry: function (cc) {
		this.state.countryCode = cc;
		this.refresh();
	},
	setCity: function (name) {
		this.state.cityName = name;
		this.refresh();
	}
});

})(Q, window, document);

Q.Template.set('Places/cities',
`<select class="Places_cities_select"></select>`, {
	elements: {
		select: 'select.Places_cities_select'
	}
});