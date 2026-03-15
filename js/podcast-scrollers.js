(function () {
	// Attach scroller controls on podcast archive sections.
	window.addEventListener('DOMContentLoaded', function () {
		var sections = document.querySelectorAll('.hts-podcast-show-section');
		if (!sections.length) {
			return;
		}

		var getScrollAmount = function (scroller) {
			var firstCard = scroller.querySelector('.hts-podcast-show-card');
			var gap = 0;

			if (!firstCard) {
				return Math.max(scroller.clientWidth * 0.75, 320);
			}

			var styles = window.getComputedStyle(scroller);
			var gapValue = styles.getPropertyValue('gap') || styles.getPropertyValue('column-gap') || '0';
			gap = parseFloat(gapValue) || 0;

			return firstCard.getBoundingClientRect().width + gap;
		};

		sections.forEach(function (section) {
			var scroller = section.querySelector('[data-hts-podcast-scroller]');
			if (!scroller) {
				return;
			}

			var prev = section.querySelector('.hts-podcast-show-nav--prev');
			var next = section.querySelector('.hts-podcast-show-nav--next');

			var scrollBy = function (direction) {
				var amount = getScrollAmount(scroller) * direction;
				scroller.scrollBy({
					left: amount,
					behavior: 'smooth',
				});
			};

			if (prev) {
				prev.addEventListener('click', function () {
					scrollBy(-1);
				});
			}

			if (next) {
				next.addEventListener('click', function () {
					scrollBy(1);
				});
			}
		});
	});
})();
