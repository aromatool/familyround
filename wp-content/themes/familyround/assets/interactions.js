(function () {
	var header = document.querySelector('.fr-site-header');
	var parallaxEl = document.querySelector('[data-fr-parallax]');
	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (header || (parallaxEl && !reduceMotion)) {
		var ticking = false;
		var onScroll = function () {
			if (header) {
				header.classList.toggle('is-scrolled', window.scrollY > 10);
			}
			if (parallaxEl && !reduceMotion) {
				var offset = Math.min(window.scrollY * 0.25, 120);
				parallaxEl.style.transform = 'translateY(' + offset + 'px)';
			}
			ticking = false;
		};
		window.addEventListener(
			'scroll',
			function () {
				if (!ticking) {
					requestAnimationFrame(onScroll);
					ticking = true;
				}
			},
			{ passive: true }
		);
		onScroll();
	}

	var revealEls = document.querySelectorAll('.fr-reveal');
	if (revealEls.length && 'IntersectionObserver' in window) {
		var io = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-visible');
						io.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.15 }
		);
		revealEls.forEach(function (el) {
			io.observe(el);
		});
	} else {
		revealEls.forEach(function (el) {
			el.classList.add('is-visible');
		});
	}
})();
