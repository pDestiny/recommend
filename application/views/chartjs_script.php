<script>
window.chartColors = {
	red: 'rgb(255, 99, 132)',
	orange: 'rgb(255, 159, 64)',
	yellow: 'rgb(255, 205, 86)',
	green: 'rgb(75, 192, 192)',
	blue: 'rgb(54, 162, 235)',
	purple: 'rgb(153, 102, 255)',
	grey: 'rgb(201, 203, 207)'
};

(function(global) {
	var MONTHS = [
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
	];

	var COLORS = [
		'#4dc9f6',
		'#f67019',
		'#f53794',
		'#537bc4',
		'#acc236',
		'#166a8f',
		'#00a950',
		'#58595b',
		'#8549ba'
	];

    

	var Samples = global.Samples || (global.Samples = {});
	var Color = global.Color;

	Samples.utils = {
		// Adapted from http://indiegamr.com/generate-repeatable-random-numbers-in-js/
		srand: function(seed) {
			this._seed = seed;
		},

		rand: function(min, max) {
			var seed = this._seed;
			min = min === undefined ? 0 : min;
			max = max === undefined ? 1 : max;
			this._seed = (seed * 9301 + 49297) % 233280;
			return min + (this._seed / 233280) * (max - min);
		},

		numbers: function(config) {
			var cfg = config || {};
			var min = cfg.min || 0;
			var max = cfg.max || 1;
			var from = cfg.from || [];
			var count = cfg.count || 8;
			var decimals = cfg.decimals || 8;
			var continuity = cfg.continuity || 1;
			var dfactor = Math.pow(10, decimals) || 0;
			var data = [];
			var i, value;

			for (i = 0; i < count; ++i) {
				value = (from[i] || 0) + this.rand(min, max);
				if (this.rand() <= continuity) {
					data.push(Math.round(dfactor * value) / dfactor);
				} else {
					data.push(null);
				}
			}

			return data;
		},

		labels: function(config) {
			var cfg = config || {};
			var min = cfg.min || 0;
			var max = cfg.max || 100;
			var count = cfg.count || 8;
			var step = (max - min) / count;
			var decimals = cfg.decimals || 8;
			var dfactor = Math.pow(10, decimals) || 0;
			var prefix = cfg.prefix || '';
			var values = [];
			var i;

			for (i = min; i < max; i += step) {
				values.push(prefix + Math.round(dfactor * i) / dfactor);
			}

			return values;
		},

		months: function(config) {
			var cfg = config || {};
			var count = cfg.count || 12;
			var section = cfg.section;
			var values = [];
			var i, value;

			for (i = 0; i < count; ++i) {
				value = MONTHS[Math.ceil(i) % 12];
				values.push(value.substring(0, section));
			}

			return values;
		},

		color: function(index) {
			return COLORS[index % COLORS.length];
		},

		transparentize: function(color, opacity) {
			var alpha = opacity === undefined ? 0.5 : 1 - opacity;
			return Color(color).alpha(alpha).rgbString();
		}
	};

	// DEPRECATED
	window.randomScalingFactor = function() {
		return Math.round(Samples.utils.rand(-100, 100));
	};

	// INITIALIZATION

    Samples.utils.srand(Date.now());
}(this));
		var config = {
			type: 'line',
			data: {
				labels: _.map(graph_data.income.mvar, d => d.ar_epoch),
				datasets: [{
					label: 'MVAR',
					backgroundColor: window.chartColors.red,
					borderColor: window.chartColors.red,
					data: _.map(graph_data.income.mvar, d => d.ar_remain_assets),
					fill: false,
				}, {
					label: 'Ant',
					fill: false,
					backgroundColor: window.chartColors.blue,
					borderColor: window.chartColors.blue,
					data: _.map(graph_data.income.ant, d => d.ar_remain_assets),
				}]
			},
			options: {
				responsive: true,
				title: {
					display: true,
					text: 'Income rate comparision'
				},
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
				scales: {
					xAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Month'
						}
					}],
					yAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Value'
						}
					}]
				}
			}
		};

        var config2 = {
			type: 'line',
			data: {
				labels: _.chain(graph_data.weight.mvar).map(d => d.ar_epoch).uniq().value(),
				datasets: _.chain(graph_data.weight.mvar).groupBy(d => d.ar_name).map((val, key) => {
					color = Samples.utils.color(Math.round(Math.random() * 10))
					return {
						label: key,
						backgroundColor: color,
						borderColor: color,
						data: _.map(val, d => d.ar_weight),
						fill: false
					}
				}).value()
			},
			options: {
				responsive: true,
				title: {
					display: true,
					text: 'MVAR weight by stock name'
				},
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
				scales: {
					xAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Month'
						}
					}],
					yAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Value'
						}
					}]
				}
			}
		};
		
		var color = Chart.helpers.color;
		var barChartData = {
			labels: ["MVAR", "Ant"],
			datasets: [{
				label: ['MVAR', "Ant"],
				backgroundColor: color(window.chartColors.green).alpha(0.7).rgbString(),
				borderColor: window.chartColors.green,
				borderWidth: 1,
				barThickness: 100,
				data: _.map(graph_data.income.std, d => d.std)
			}]

		};

		window.onload = function() {
            var ctx = document.getElementById('line2-canvas').getContext('2d');
			window.myLine = new Chart(ctx, config2);
            var ctx = document.getElementById('line-canvas').getContext('2d');
			window.myLine = new Chart(ctx, config);

			var ctx = document.getElementById('bar-canvas').getContext('2d');
			window.myBar = new Chart(ctx, {
				type: 'bar',
				data: barChartData,
				options: {
					responsive: true,
					legend: {
						position: 'top',
					},
					title: {
						display: true,
						text: 'MVAR vs Ant standard deviation'
					}
				}
			});

		};
</script>
