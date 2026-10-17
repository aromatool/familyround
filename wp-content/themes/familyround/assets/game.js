/* FamilyRound — pagina jocului: reveal-uri fine, asamblarea evantaiului și
   bara sticky de comandă pe mobil. Fail-open + respectă prefers-reduced-motion. */
(function () {
	var root = document.querySelector('.fr-gamepage');
	if (!root) return;

	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	// Every order action uses the same WooCommerce form, preserving the selected
	// quantity. Without JavaScript the link still adds one set and opens the cart.
	document.addEventListener('click', function (event) {
		var link = event.target.closest('[data-fr-order-submit]');
		if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
		var form = root.querySelector('form.cart');
		var submit = form && form.querySelector('.single_add_to_cart_button');
		if (!form || !submit || submit.disabled) return;
		event.preventDefault();
		if (form.requestSubmit) form.requestSubmit(submit);
		else submit.click();
	});

	// Reveals (fade + rise) and the fan assembly.
	var items = [].slice.call(root.querySelectorAll('.rev, .gp-fan'));
	if (!('IntersectionObserver' in window) || reduce) {
		items.forEach(function (e) { e.classList.add('in'); });
	} else {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
		items.forEach(function (e) { io.observe(e); });
		setTimeout(function () { items.forEach(function (e) { e.classList.add('in'); }); }, 2200);
	}

	// Sticky mobile buy bar: appears after the hero, hides once the final CTA shows.
	var bar = document.getElementById('gpSticky');
	var hero = document.getElementById('top');
	var finalCta = document.querySelector('.gp-final');
	if (bar && hero && 'IntersectionObserver' in window) {
		var pastHero = false, atFinal = false;
		var sync = function () { bar.classList.toggle('is-on', pastHero && !atFinal); };
		new IntersectionObserver(function (e) {
			pastHero = !e[0].isIntersecting && e[0].boundingClientRect.top < 0;
			sync();
		}, { threshold: 0 }).observe(hero);
		if (finalCta) {
			new IntersectionObserver(function (e) {
				atFinal = e[0].isIntersecting;
				sync();
			}, { threshold: 0 }).observe(finalCta);
		}
	}
})();
