import { useEffect, useState } from 'react';

export default function MapPoints() {
	const [data, setData] = useState(null);
	const [error, setError] = useState(false);

	useEffect(() => {
		const restUrl = (window.frData && window.frData.restUrl) || '';
		fetch(`${restUrl}/map/points`)
			.then((res) => res.json())
			.then(setData)
			.catch(() => setError(true));
	}, []);

	if (error) return <p>Harta nu a putut fi încărcată.</p>;
	if (!data) return <p>Se încarcă...</p>;

	if (data.points.length === 0) {
		return <p>Prima livrare FamilyRound încă nu a ajuns la o familie — tu poți fi primul oraș de pe hartă.</p>;
	}

	return (
		<div>
			<p style={{ fontWeight: 600, marginBottom: 16 }}>
				{data.total_cities} {data.total_cities === 1 ? 'oraș atins' : 'orașe atinse'} din 100, obiectivul comunității
			</p>
			<ul style={{ listStyle: 'none', padding: 0, maxWidth: 480, margin: '0 auto' }}>
				{data.points.map((p) => (
					<li
						key={`${p.locality}-${p.county}`}
						style={{
							display: 'flex',
							justifyContent: 'space-between',
							padding: '8px 0',
							borderBottom: '1px solid #ece1cf',
						}}
					>
						<span>
							{p.locality} ({p.county}) {p.is_new ? '🟡 nou' : ''}
						</span>
						<span>
							{p.count} {p.count === 1 ? 'familie' : 'familii'}
						</span>
					</li>
				))}
			</ul>
			<p style={{ fontSize: 13, color: '#6b6b6b', marginTop: 16 }}>
				Harta vizuală a României vine odată ce avem destule livrări reale de arătat — deocamdată, lista de mai sus e sursa de adevăr.
			</p>
		</div>
	);
}
