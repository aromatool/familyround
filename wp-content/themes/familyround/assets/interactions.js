(function () {
	var header = document.querySelector('.fr-site-header');
	if (header) {
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 10);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	// Reveal-ul la scroll al secțiunilor `.fr-reveal` e gestionat de GSAP
	// ScrollTrigger în bundle-ul React (src/scroll-reveal.js) — acest fișier
	// se încarcă și fără build-ul Vite, deci tratăm acel caz ca fallback.
	var hasIslandsBundle = !!document.querySelector('[data-fr-island]');
	if (!hasIslandsBundle) {
		document.querySelectorAll('.fr-reveal').forEach(function (el) {
			el.classList.add('is-visible');
		});
	}
})();
