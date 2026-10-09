import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

/**
 * Reveal discret la scroll pentru secțiunile marcate `.fr-reveal`. Secțiunile
 * de tip pillar (imagine/vizual + text) intră cu un mic decalaj între cele
 * două jumătăți, în loc să apară dintr-o singură mișcare rigidă.
 */
export function initScrollReveal() {
	const sections = gsap.utils.toArray('.fr-reveal');
	if (!sections.length) return;

	const mm = gsap.matchMedia();

	mm.add('(prefers-reduced-motion: no-preference)', () => {
		sections.forEach((section) => {
			const grid = section.querySelector('.fr-pillar-grid, .fr-piggybank-grid');
			const halves = grid ? Array.from(grid.children) : null;

			if (halves && halves.length === 2) {
				gsap.set(halves[0], { autoAlpha: 0, x: -24 });
				gsap.set(halves[1], { autoAlpha: 0, x: 24 });
				ScrollTrigger.create({
					trigger: section,
					start: 'top 82%',
					once: true,
					onEnter: () => {
						gsap.to(halves, {
							autoAlpha: 1,
							x: 0,
							duration: 0.9,
							ease: 'power3.out',
							stagger: 0.12,
						});
					},
				});
			} else {
				gsap.set(section, { autoAlpha: 0, y: 28 });
				ScrollTrigger.create({
					trigger: section,
					start: 'top 85%',
					once: true,
					onEnter: () => {
						gsap.to(section, { autoAlpha: 1, y: 0, duration: 0.9, ease: 'power3.out' });
					},
				});
			}
		});

		return () => {
			sections.forEach((section) => gsap.set(section, { clearProps: 'all' }));
		};
	});

	mm.add('(prefers-reduced-motion: reduce)', () => {
		sections.forEach((section) => gsap.set(section, { clearProps: 'all' }));
	});
}
