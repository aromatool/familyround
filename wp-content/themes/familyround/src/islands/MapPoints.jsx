import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { HARTA_INNER_SVG, HARTA_VIEWBOX, projectLonLat } from '../lib/harta-svg';
import { lonLatFor } from '../lib/romania-map';

const GOAL_CITIES = 100;

export default function MapPoints({ compact = false }) {
	const svgRef = useRef(null);
	const [markersHost, setMarkersHost] = useState(null);
	const [data, setData] = useState(null);
	const [error, setError] = useState(false);
	const [selected, setSelected] = useState(null);

	useEffect(() => {
		const restUrl = (window.frData && window.frData.restUrl) || '';
		fetch(`${restUrl}/map/points`)
			.then((res) => res.json())
			.then(setData)
			.catch(() => setError(true));
	}, []);

	useEffect(() => {
		if (svgRef.current && !markersHost) {
			setMarkersHost(svgRef.current.querySelector('#city-markers'));
		}
	}, [data, markersHost]);

	const plotted = (data?.points || [])
		.map((p) => {
			const lonLat = lonLatFor(p.locality, p.county);
			return lonLat ? { ...p, coords: projectLonLat(lonLat[0], lonLat[1]) } : null;
		})
		.filter(Boolean);

	if (error) return null;
	if (!data) return <p className="fr-map-status">Se încarcă harta...</p>;

	const pointKey = (p) => `${p.locality}-${p.county}`;
	const selectPoint = (p) => setSelected((cur) => (cur && pointKey(cur) === pointKey(p) ? null : p));

	return (
		<div className={`fr-map${compact ? ' fr-map--compact' : ''}`}>
			<svg
				ref={svgRef}
				className="fr-map-svg"
				viewBox={HARTA_VIEWBOX}
				role="img"
				aria-label="Harta orașelor FamilyRound din România"
				dangerouslySetInnerHTML={{ __html: HARTA_INNER_SVG }}
			/>
			{markersHost &&
				createPortal(
					plotted.map((p) => (
						<g
							key={`${p.locality}-${p.county}`}
							className="fr-map-point"
							role="button"
							tabIndex={0}
							aria-label={`${p.locality}, ${p.count} ${p.count === 1 ? 'joc livrat' : 'jocuri livrate'}`}
							onClick={() => selectPoint(p)}
							onKeyDown={(e) => {
								if (e.key === 'Enter' || e.key === ' ') {
									e.preventDefault();
									selectPoint(p);
								}
							}}
						>
							<circle
								className={`fr-map-dot${p.is_new ? ' fr-map-dot--new' : ''}`}
								cx={p.coords[0]}
								cy={p.coords[1]}
								r={Math.min(13, 6 + p.count)}
							/>
						</g>
					)),
					markersHost
				)}
			{data.points.length === 0 ? (
				<p className="fr-map-status">
					Prima livrare FamilyRound încă nu a ajuns la o familie — voi puteți fi primul oraș de pe hartă.
				</p>
			) : (
				<>
					<p className="fr-map-caption">
						{selected ? (
							<>
								<strong>{selected.locality}</strong> ({selected.county}) — {selected.count}{' '}
								{selected.count === 1 ? 'joc livrat' : 'jocuri livrate'}
							</>
						) : (
							<>
								<strong>{data.total_cities}</strong> {data.total_cities === 1 ? 'oraș atins' : 'orașe atinse'} din{' '}
								{GOAL_CITIES}, obiectivul comunității
							</>
						)}
					</p>
					{!compact && <p className="fr-map-hint">Atinge un oraș de pe hartă pentru detalii.</p>}
					{!compact && (
						<ul className="fr-map-legend">
							{data.points.map((p) => (
								<li key={`${p.locality}-${p.county}`}>
									<span>
										{p.locality} ({p.county}) {p.is_new ? '· nou' : ''}
									</span>
									<span>{p.count} {p.count === 1 ? 'joc' : 'jocuri'}</span>
								</li>
							))}
						</ul>
					)}
				</>
			)}
		</div>
	);
}
