import gsap from 'gsap';

/**
 * Reveal discret la scroll pentru secțiunile marcate `.fr-reveal`. Secțiunile
 * de tip pillar (imagine/vizual + text) intră cu un mic decalaj între cele
 * două jumătăți, în loc să apară dintr-o singură mișcare rigidă.
 *
 * Deliberat pe IntersectionObserver nativ, nu pe GSAP ScrollTrigger: pragul
 * de declanșare al ScrollTrigger e calculat o singură dată, în pixeli, și
 * poate rămâne nealiniat cu pagina reală dacă ceva (extensii de browser,
 * throttling, resurse încărcate cu întârziere) întârzie recalcularea —
 * riscul fiind conținut blocat la opacitate 0, invizibil permanent.
 * IntersectionObserver recalculează mereu față de viewport-ul curent, fără
 * cache de poziții, deci nu poate rămâne "în urmă" în acest fel.
 */
export function initScrollReveal() {
	const sections = document.querySelectorAll('.fr-reveal');
	if (!sections.length) return;

	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	sections.forEach((section) => {
		const grid = section.querySelector('.fr-pillar-grid, .fr-piggybank-grid');
		const halves = grid ? Array.from(grid.children) : null;
		const isSplit = halves && halves.length === 2;

		if (reduceMotion) {
			gsap.set(isSplit ? halves : section, { clearProps: 'all' });
			return;
		}

		if (isSplit) {
			gsap.set(halves[0], { autoAlpha: 0, x: -24 });
			gsap.set(halves[1], { autoAlpha: 0, x: 24 });
		} else {
			gsap.set(section, { autoAlpha: 0, y: 28 });
		}

		const io = new IntersectionObserver(
			(entries) => {
				entries.forEach((entry) => {
					if (!entry.isIntersecting) return;
					if (isSplit) {
						gsap.to(halves, { autoAlpha: 1, x: 0, duration: 0.9, ease: 'power3.out', stagger: 0.12 });
					} else {
						gsap.to(section, { autoAlpha: 1, y: 0, duration: 0.9, ease: 'power3.out' });
					}
					io.unobserve(section);
				});
			},
			{ threshold: 0.15, rootMargin: '0px 0px -10% 0px' }
		);
		io.observe(section);
	});
}
