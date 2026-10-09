import { useEffect, useRef, useState } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ROMANIA_PATH, ROMANIA_VIEWBOX, pointFor } from '../lib/romania-map';

gsap.registerPlugin(ScrollTrigger);

const GOAL_CITIES = 100;

export default function MapPoints({ compact = false }) {
	const wrapperRef = useRef(null);
	const dotsRef = useRef([]);
	const [data, setData] = useState(null);
	const [error, setError] = useState(false);

	useEffect(() => {
		const restUrl = (window.frData && window.frData.restUrl) || '';
		fetch(`${restUrl}/map/points`)
			.then((res) => res.json())
			.then(setData)
			.catch(() => setError(true));
	}, []);

	const plotted = (data?.points || [])
		.map((p) => ({ ...p, coords: pointFor(p.locality, p.county) }))
		.filter((p) => p.coords);

	useEffect(() => {
		dotsRef.current = dotsRef.current.slice(0, plotted.length);
		if (!plotted.length || !wrapperRef.current) return undefined;

		const mm = gsap.matchMedia();

		mm.add('(prefers-reduced-motion: no-preference)', () => {
			gsap.set(dotsRef.current, { scale: 0, transformOrigin: 'center' });
			const trigger = ScrollTrigger.create({
				trigger: wrapperRef.current,
				start: 'top 85%',
				once: true,
				onEnter: () => {
					gsap.to(dotsRef.current, {
						scale: 1,
						duration: 0.6,
						ease: 'back.out(2)',
						stagger: { each: 0.08, from: 'random' },
					});
				},
			});
			return () => trigger.kill();
		});

		mm.add('(prefers-reduced-motion: reduce)', () => {
			gsap.set(dotsRef.current, { scale: 1 });
		});

		return () => mm.revert();
	}, [plotted.length]);

	if (error) return null;
	if (!data) return <p className="fr-map-status">Se încarcă harta...</p>;

	if (data.points.length === 0) {
		return (
			<p className="fr-map-status">
				Prima livrare FamilyRound încă nu a ajuns la o familie — voi puteți fi primul oraș de pe hartă.
			</p>
		);
	}

	return (
		<div className={`fr-map${compact ? ' fr-map--compact' : ''}`} ref={wrapperRef}>
			<svg className="fr-map-svg" viewBox={ROMANIA_VIEWBOX} role="img" aria-label="Harta orașelor FamilyRound din România">
				<path className="fr-map-outline" d={ROMANIA_PATH} />
				{plotted.map((p, i) => (
					<g key={`${p.locality}-${p.county}`} className="fr-map-point">
						<circle
							ref={(el) => (dotsRef.current[i] = el)}
							className={`fr-map-dot${p.is_new ? ' fr-map-dot--new' : ''}`}
							cx={p.coords[0]}
							cy={p.coords[1]}
							r={Math.min(10, 4 + p.count)}
						/>
						<title>
							{p.locality} ({p.county}) — {p.count} {p.count === 1 ? 'familie' : 'familii'}
						</title>
					</g>
				))}
			</svg>
			<p className="fr-map-caption">
				<strong>{data.total_cities}</strong> {data.total_cities === 1 ? 'oraș atins' : 'orașe atinse'} din {GOAL_CITIES}, obiectivul comunității
			</p>
			{!compact && (
				<ul className="fr-map-legend">
					{data.points.map((p) => (
						<li key={`${p.locality}-${p.county}`}>
							<span>
								{p.locality} ({p.county}) {p.is_new ? '· nou' : ''}
							</span>
							<span>{p.count} {p.count === 1 ? 'familie' : 'familii'}</span>
						</li>
					))}
				</ul>
			)}
		</div>
	);
}
