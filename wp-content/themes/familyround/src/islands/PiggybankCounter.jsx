import { useEffect, useState } from 'react';

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

	if (error) return null;
	if (!data) return <p>Se încarcă...</p>;
	if (!data.mission) return <p>Nicio misiune activă momentan.</p>;

	const pct = Math.min(100, Math.round((data.allocated / data.mission.goal_amount) * 100));

	return (
		<div style={{ maxWidth: 420, margin: '0 auto' }}>
			<p style={{ fontWeight: 600 }}>{data.mission.title}</p>
			<div style={{ background: '#ece1cf', borderRadius: 999, height: 12, overflow: 'hidden' }}>
				<div style={{ width: `${pct}%`, background: '#c6a15b', height: '100%' }} />
			</div>
			<p>
				{data.allocated.toFixed(0)} lei din {data.mission.goal_amount.toFixed(0)} lei
			</p>
		</div>
	);
}
