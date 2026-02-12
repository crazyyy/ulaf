	/*
	$.fn.selectText = function() {
		var node = this[0];
		console.warn('node', node);
		if (window.getSelection) {
			console.log('getSelection');
			const selection = window.getSelection();
			const range = document.createRange();
			range.selectNodeContents(node);
			selection.removeAllRanges();
			selection.addRange(range);
		} else if (document.body.createTextRange) {
			console.log('createTextRange');
			const range = document.body.createTextRange();
			range.moveToElementText(node);
			range.select();
		}
	};
	*/

	/*
	$.fn.bgColor = function() {
		var $node = $(this[0]),
			colors = [],
			bgColor;
		do {
			bgColor = colorParse($node.css("background-color"));
			if ($node.is("html")) {
				bgColor = [255,255,255,1];
			}
			colors.push(bgColor);
			if (bgColor[3] == 1) {
				break;
			}
		} while ($node = $node.parent());
		colors = colors.reverse();
		bgColor = blendColors.apply(null, colors);
		bgColor = 'rgba(' + bgColor.join(',') + ')';
		return bgColor;
	}
	*/

	/*
	function colorParse(color)
	{
		color = (color || "").replace(/\s+/g, '').toLowerCase();
		if (color == "transparent" || color == "") {
			color = [0,0,0,0];
		} else if (matches = color.match(/rgba\((\d+),(\d+),(\d+),(\d+)\)/)) {
			color = matches.slice(1).map(function(val){return parseFloat(val, 10)});
		} else if (matches = color.match(/rgb\((\d+),(\d+),(\d+)\)/)) {
			color = matches.slice(1).map(function(val){return parseFloat(val, 10)});
			color.push(1);
		}
		return color;
	}

	function blendColors() {
		var args = Array.prototype.slice.call(arguments),
			alpha,
			add1 = [0, 0, 0, 0],
			add2,
			mixed;
		while (add2 = args.shift()) {
			if (typeof add2[3] === 'undefined' || add2[3] == 1) {
				add2[3] = 1;
				mixed = add2;
			} else if (add1[3] && add2[3]) {
				alpha = 1 - (1 - add1[3]) * (1 - add2[3]);
				mixed = [
					// red
					Math.round(
						(add2[0] * add2[3] / alpha) +
						(add1[0] * add1[3] * (1 - add2[3]) / alpha)
					),
					// green
					Math.round(
						(add2[1] * add2[3] / alpha) +
						(add1[1] * add1[3] * (1 - add2[3]) / alpha)
					),
					// blue
					Math.round(
						(add2[2] * add2[3] / alpha) +
						(add1[2] * add1[3] * (1 - add2[3]) / alpha)
					),
					alpha
				];
			} else if (add2[3]) {
				mixed = add2;
			} else {
				mixed = add1;
			}
			add1 = mixed;
		}
		return mixed;
	}
	*/

