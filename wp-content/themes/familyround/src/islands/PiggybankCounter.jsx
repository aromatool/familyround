import { useEffect, useRef, useState } from 'react';

function useCountUp(target, duration = 1200) {
	const [value, setValue] = useState(0);
	const startRef = useRef(null);
	const frameRef = useRef(null);

	useEffect(() => {
		startRef.current = null;
		cancelAnimationFrame(frameRef.current);

		if (!target) {
			setValue(0);
			return;
		}

		const step = (ts) => {
			if (!startRef.current) startRef.current = ts;
			const progress = Math.min(1, (ts - startRef.current) / duration);
			const eased = 1 - Math.pow(1 - progress, 3);
			setValue(target * eased);
			if (progress < 1) {
				frameRef.current = requestAnimationFrame(step);
			}
		};
		frameRef.current = requestAnimationFrame(step);

		return () => cancelAnimationFrame(frameRef.current);
	}, [target, duration]);

	return value;
}

export default function PiggybankCounter() {
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
	const animatedAllocated = useCountUp(data ? allocated : 0);
	const pct = goal ? Math.min(100, (animatedAllocated / goal) * 100) : 0;

	if (error) return null;
	if (!data) return <p>Se încarcă...</p>;
	if (!data.mission) return <p>Nicio misiune activă momentan.</p>;

	return (
		<div className="fr-piggy-widget">
			<p className="fr-piggy-title">{data.mission.title}</p>
			<div className="fr-piggy-track">
				<div className="fr-piggy-fill" style={{ width: `${pct}%` }} />
			</div>
			<p className="fr-piggy-amount">
				{Math.round(animatedAllocated)} lei din {Math.round(goal)} lei
			</p>
		</div>
	);
}
