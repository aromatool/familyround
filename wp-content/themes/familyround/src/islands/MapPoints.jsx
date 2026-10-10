import { useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { HARTA_INNER_SVG, HARTA_VIEWBOX, projectLonLat } from '../lib/harta-svg';
import { lonLatFor } from '../lib/romania-map';

const VB_W = 1600;
const VB_H = 1100;
const TIP_W = 184;

// Normalize a locality/county name for grouping variants (diacritics, case, punctuation).
const norm = (s) =>
	(s || '')
		.toString()
		.normalize('NFD')
		.replace(/[̀-ͯ]/g, '')
		.toLowerCase()
		.replace(/[^a-z0-9]+/g, ' ')
		.trim();

// Capitalize a name only when it arrives all-lowercase, so "floresti" reads "Floresti"
// while "Cluj-Napoca" / "București" are left untouched.
const displayName = (s) =>
	s && s === s.toLowerCase() ? s.replace(/\b\p{L}/gu, (c) => c.toUpperCase()) : s;

// Between two spellings of the same place, keep the richer one (more diacritics, then cased).
const richer = (a, b) => {
	const na = (a.match(/[^\x00-\x7f]/g) || []).length;
	const nb = (b.match(/[^\x00-\x7f]/g) || []).length;
	if (nb > na) return b;
	if (na > nb) return a;
	if (b[0] === b[0].toUpperCase() && a[0] !== a[0].toUpperCase()) return b;
	return a;
};

const jocuri = (n) => `${n} ${n === 1 ? 'joc' : 'jocuri'}`;

export default function MapPoints({ compact = false, variant = 'default', title, subtitle, href }) {
	const wrapRef = useRef(null);
	const svgRef = useRef(null);
	const [markersHost, setMarkersHost] = useState(null);
	const [data, setData] = useState(null);
	const [error, setError] = useState(false);
	const [selected, setSelected] = useState(null);
	const [hovered, setHovered] = useState(null);
	const [cw, setCw] = useState(0);
	const [query, setQuery] = useState('');

	useEffect(() => {
		const restUrl = (window.frData && window.frData.restUrl) || '';
		fetch(`${restUrl}/map/points`)
			.then((res) => (res.ok ? res.json() : Promise.reject(res)))
			.then(setData)
			.catch(() => setError(true));
	}, []);

	useEffect(() => {
		if (svgRef.current && !markersHost) {
			setMarkersHost(svgRef.current.querySelector('#city-markers'));
		}
	}, [data, markersHost]);

	// Track the rendered map width so the HTML tooltip can be positioned over SVG points.
	const measure = () => {
		const el = wrapRef.current;
		if (el && el.clientWidth) setCw(el.clientWidth);
	};
	useEffect(() => {
		const el = wrapRef.current;
		if (!el) return undefined;
		measure();
		if ('ResizeObserver' in window) {
			const ro = new ResizeObserver(measure);
			ro.observe(el);
			return () => ro.disconnect();
		}
		window.addEventListener('resize', measure);
		return () => window.removeEventListener('resize', measure);
	}, [data]);

	// Aggregate variants of the same locality; distinguish same-name localities by county.
	const points = useMemo(() => {
		if (!data || !data.points) return [];
		const agg = {};
		data.points.forEach((p) => {
			const key = `${norm(p.locality)}|${norm(p.county)}`;
			if (!agg[key]) {
				agg[key] = { locality: p.locality, county: p.county, count: 0, is_new: false };
			}
			agg[key].count += p.count;
			agg[key].is_new = agg[key].is_new || p.is_new;
			agg[key].locality = richer(agg[key].locality, p.locality);
		});
		return Object.values(agg)
			.map((p) => {
				const lonLat = lonLatFor(p.locality, p.county);
				return {
					...p,
					name: displayName(p.locality),
					coords: lonLat ? projectLonLat(lonLat[0], lonLat[1]) : null,
				};
			})
			.sort((a, b) => b.count - a.count);
	}, [data]);

	const plotted = points.filter((p) => p.coords);
	const totalGames = points.reduce((s, p) => s + p.count, 0);
	const totalCities = points.length;

	// Dots appear once when the map enters view. Fails open: if armed and the observer
	// never fires (e.g. a background tab), a timeout still reveals them.
	useEffect(() => {
		if (!plotted.length) return undefined;
		const root = wrapRef.current;
		const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reduce || !('IntersectionObserver' in window) || !root) return undefined;

		root.classList.add('fr-dots-armed');
		let done = false;
		const reveal = () => {
			if (!done) {
				done = true;
				root.classList.add('fr-dots-in');
			}
		};
		const io = new IntersectionObserver(
			(entries) => entries.forEach((e) => {
				if (e.isIntersecting) {
					reveal();
					io.disconnect();
				}
			}),
			{ threshold: 0.25 }
		);
		io.observe(root);
		const t = setTimeout(() => {
			reveal();
			io.disconnect();
		}, 2600);
		return () => {
			clearTimeout(t);
			io.disconnect();
		};
	}, [plotted.length]);

	// A tap/click anywhere outside a point clears the selection.
	useEffect(() => {
		if (!selected) return undefined;
		const onDown = (e) => {
			if (!e.target.closest('.fr-map-point') && !e.target.closest('.fr-map-tip')) {
				setSelected(null);
			}
		};
		document.addEventListener('pointerdown', onDown);
		return () => document.removeEventListener('pointerdown', onDown);
	}, [selected]);

	const keyOf = (p) => `${p.locality}-${p.county}`;
	const activeKey = hovered || selected;
	const active = plotted.find((p) => keyOf(p) === activeKey) || null;

	const filtered = useMemo(() => {
		const q = norm(query);
		if (!q) return points;
		return points.filter((p) => norm(p.locality).includes(q) || norm(p.county).includes(q));
	}, [points, query]);

	const scale = cw ? cw / VB_W : 0;
	let tip = null;
	if (active && cw) {
		const px = active.coords[0] * scale;
		const py = active.coords[1] * scale;
		const left = Math.min(Math.max(px, TIP_W / 2 + 6), cw - TIP_W / 2 - 6);
		tip = { left, top: py, shift: px - left, name: active.name, count: active.count };
	}

	const toggle = (p) => setSelected((cur) => (cur === keyOf(p) ? null : keyOf(p)));
	const isHome = variant === 'home';

	const mapStage = (
		<div className="fr-map-stage" ref={wrapRef}>
			<svg
				ref={svgRef}
				className="fr-map-svg"
				viewBox={HARTA_VIEWBOX}
				role="img"
				aria-label="Harta localităților FamilyRound din România"
				dangerouslySetInnerHTML={{ __html: HARTA_INNER_SVG }}
			/>

			{markersHost &&
				createPortal(
					plotted.map((p, i) => {
						const isSel = selected === keyOf(p);
						const r = Math.min(13, 7 + p.count);
						return (
							<g
								key={keyOf(p)}
								className="fr-map-point"
								style={{ '--fr-i': i }}
								role="button"
								tabIndex={0}
								aria-label={`${p.name}, ${jocuri(p.count)}`}
								aria-pressed={isSel}
								onClick={() => { measure(); toggle(p); }}
								onMouseEnter={() => { measure(); setHovered(keyOf(p)); }}
								onMouseLeave={() => setHovered(null)}
								onFocus={() => { measure(); setHovered(keyOf(p)); }}
								onBlur={() => setHovered(null)}
								onKeyDown={(e) => {
									if (e.key === 'Enter' || e.key === ' ') {
										e.preventDefault();
										measure();
										toggle(p);
									}
								}}
							>
								<circle className="fr-map-hit" cx={p.coords[0]} cy={p.coords[1]} r={r + 12} />
								{isSel && <circle className="fr-map-ring" cx={p.coords[0]} cy={p.coords[1]} r={r + 6} />}
								<circle className={`fr-map-dot${p.is_new ? ' fr-map-dot--new' : ''}`} cx={p.coords[0]} cy={p.coords[1]} r={r} />
							</g>
						);
					}),
					markersHost
				)}

			{tip && (
				<div className="fr-map-tip" style={{ left: `${tip.left}px`, top: `${tip.top}px` }} role="status">
					<span className="fr-map-tip-name">{tip.name}</span>
					<span className="fr-map-tip-count">{jocuri(tip.count)}</span>
					<span className="fr-map-tip-arrow" style={{ left: `calc(50% + ${tip.shift}px)` }} />
				</div>
			)}
		</div>
	);

	const statsBlock = (
		<div className="fr-map-stats">
			<div className="fr-map-stat">
				<span className="fr-map-stat-num">{totalGames}</span>
				<span className="fr-map-stat-label">{totalGames === 1 ? 'joc cumpărat' : 'jocuri cumpărate'}</span>
			</div>
			<div className="fr-map-stat">
				<span className="fr-map-stat-num">{totalCities}</span>
				<span className="fr-map-stat-label">{totalCities === 1 ? 'localitate' : 'localități'}</span>
			</div>
		</div>
	);

	const statusMsg = error
		? 'Harta nu este disponibilă momentan.'
		: !data
			? 'Se încarcă harta…'
			: plotted.length === 0
				? 'Povestea noastră abia începe.'
				: null;

	// Homepage: two columns — copy (title, explanation, totals, link) left, map right.
	if (isHome) {
		return (
			<div className="fr-map fr-map--home">
				<div className="fr-map-copy">
					<div className="fr-map-copy-head">
						<p className="fr-collection-label">Harta FamilyRound</p>
						{title && <h2>{title}</h2>}
						{subtitle && <p className="fr-map-copy-sub">{subtitle}</p>}
					</div>
					<div className="fr-map-copy-foot">
						{statusMsg ? (
							<p className="fr-map-status">{statusMsg}</p>
						) : (
							<>
								{statsBlock}
								{href && (
									<a className="fr-cta-ghost" href={href}>Explorează harta →</a>
								)}
							</>
						)}
					</div>
				</div>
				{mapStage}
			</div>
		);
	}

	// Dedicated page: centered map, totals and the searchable locality list.
	return (
		<div className="fr-map">
			{mapStage}

			{statusMsg ? (
				<p className="fr-map-status">{statusMsg}</p>
			) : (
				<>
					<p className="fr-map-hint">Alege un punct pentru a descoperi localitatea.</p>
					{statsBlock}

					<div className="fr-map-list">
						<label className="fr-map-search">
							<span className="fr-visually-hidden">Caută o localitate</span>
							<input
								type="search"
								value={query}
								onChange={(e) => setQuery(e.target.value)}
								placeholder="Caută o localitate…"
								aria-label="Caută o localitate"
							/>
						</label>
						{filtered.length === 0 ? (
							<p className="fr-map-status">Nicio localitate nu se potrivește căutării.</p>
						) : (
							<ul>
								{filtered.map((p) => (
									<li key={keyOf(p)}>
										<button
											type="button"
											className="fr-map-list-item"
											aria-pressed={selected === keyOf(p)}
											onClick={() => {
												if (p.coords) {
													setSelected(keyOf(p));
													wrapRef.current?.scrollIntoView({ behavior: 'smooth', block: 'center' });
												}
											}}
										>
											<span>
												{p.name} <span className="fr-map-list-county">({p.county})</span>
											</span>
											<span>{jocuri(p.count)}</span>
										</button>
									</li>
								))}
							</ul>
						)}
					</div>
				</>
			)}
		</div>
	);
}
