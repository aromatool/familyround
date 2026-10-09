import { useEffect, useRef, useState } from 'react';
import { createFamilyRoundJar } from '../lib/pusculita';
import { PUSCULITA_INNER_SVG, PUSCULITA_VIEWBOX } from '../lib/pusculita-svg';

export default function PiggybankCounter({ compact = false }) {
	const svgRef = useRef(null);
	const jarRef = useRef(null);
	const [data, setData] = useState(null);
	const [error, setError] = useState(false);

	useEffect(() => {
		const restUrl = (window.frData && window.frData.restUrl) || '';
		fetch(`${restUrl}/piggybank/summary`)
			.then((res) => res.json())
			.then(setData)
			.catch(() => setError(true));
	}, []);

	const allocated = data?.allocated || 0;
	const goal = data?.mission?.goal_amount || 0;
	const pct = goal ? Math.min(100, (allocated / goal) * 100) : 0;

	useEffect(() => {
		if (!svgRef.current) return undefined;
		const jar = createFamilyRoundJar(svgRef.current);
		jarRef.current = jar;
		return () => jar.destroy();
	}, []);

	useEffect(() => {
		if (!jarRef.current) return;
		jarRef.current.setProgress(pct / 100);
	}, [pct]);

	if (error) return null;
	if (!data) return <p>Se încarcă...</p>;
	if (!data.mission) return <p>Nicio misiune activă momentan.</p>;

	return (
		<div className="fr-piggy-widget">
			<svg
				ref={svgRef}
				className="fr-jar"
				viewBox={PUSCULITA_VIEWBOX}
				dangerouslySetInnerHTML={{ __html: PUSCULITA_INNER_SVG }}
			/>
			<p className="fr-piggy-title">{data.mission.title}</p>
			<p className="fr-piggy-amount">
				{Math.round(allocated)} lei din {Math.round(goal)} lei
			</p>
			<p className="fr-piggy-note">Sumă alocată din vânzări reale — nu este încă suma transferată cauzei.</p>
			{!compact && data.mission.description && <p className="fr-piggy-desc">{data.mission.description}</p>}
			{!compact && data.past_missions && data.past_missions.length > 0 && (
				<div className="fr-piggy-history">
					<p className="fr-piggy-history-title">Campanii încheiate</p>
					<ul>
						{data.past_missions.map((m) => (
							<li key={m.id}>
								<span>{m.title}</span>
								<span>
									{Math.round(m.allocated)} / {Math.round(m.goal_amount)} lei
								</span>
							</li>
						))}
					</ul>
				</div>
			)}
		</div>
	);
}
