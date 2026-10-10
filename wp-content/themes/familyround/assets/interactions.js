(function () {
	// Sticky header shadow
	var header = document.querySelector('.fr-site-header');
	if (header) {
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 10);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	// One-time entrance for .fr-fade-up — opt in only on capable, motion-OK browsers.
	// Content stays visible otherwise, and a safety timeout guarantees it can never
	// get stuck hidden (e.g. background tabs that never fire IntersectionObserver).
	(function () {
		var items = document.querySelectorAll('.fr-fade-up');
		if (!items.length) return;

		var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reduceMotion || !('IntersectionObserver' in window)) return; // leave visible

		document.documentElement.classList.add('fr-js');

		var revealAll = function () {
			items.forEach(function (el) { el.classList.add('fr-in'); });
		};

		var io = new IntersectionObserver(function (entries, obs) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('fr-in');
					obs.unobserve(entry.target);
				}
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

		items.forEach(function (el) { io.observe(el); });

		// Fail-safe: nothing stays invisible past 1.6s regardless of visibility state.
		setTimeout(revealAll, 1600);
	})();

	// "Aruncă o privire în cutie" — crossfade between the set photo and the peek photo.
	(function () {
		document.querySelectorAll('[data-fr-peek-toggle]').forEach(function (btn) {
			var product = btn.closest('.fr-product');
			if (!product) return;
			var figure = product.querySelector('[data-fr-peek]');
			var setImg = product.querySelector('.fr-product-img--set');
			var peekImg = product.querySelector('.fr-product-img--peek');
			if (!figure || !setImg || !peekImg) return;

			var setLabel = figure.getAttribute('data-set-label') || btn.textContent;
			var backLabel = figure.getAttribute('data-back-label') || setLabel;

			btn.addEventListener('click', function () {
				var open = peekImg.classList.toggle('is-active');
				setImg.classList.toggle('is-active', !open);
				btn.setAttribute('aria-pressed', open ? 'true' : 'false');
				btn.textContent = open ? backLabel : setLabel;
			});
		});
	})();
})();
