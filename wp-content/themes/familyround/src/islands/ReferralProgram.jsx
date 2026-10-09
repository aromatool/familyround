import { useEffect, useState } from 'react';

function useQueryParam(name) {
	const [value] = useState(() => new URLSearchParams(window.location.search).get(name));
	return value;
}

function Dashboard({ token }) {
	const [data, setData] = useState(null);
	const [error, setError] = useState(false);

	useEffect(() => {
		const restUrl = (window.frData && window.frData.restUrl) || '';
		fetch(`${restUrl}/referrals/me?token=${encodeURIComponent(token)}`)
			.then((res) => {
				if (!res.ok) throw new Error('not found');
				return res.json();
			})
			.then(setData)
			.catch(() => setError(true));
	}, [token]);

	if (error) return <p>Linkul tău nu mai este valid. Înscrie-te din nou mai jos ca să primești unul nou.</p>;
	if (!data) return <p>Se încarcă...</p>;

	return (
		<div style={{ maxWidth: 480, margin: '0 auto', textAlign: 'center' }}>
			<p style={{ fontSize: 20 }}>Salut, {data.first_name}!</p>
			<p>
				Recomandări validate: <strong>{data.validated_count}</strong>
			</p>
			<p>
				Bonus generat pentru Pușculiță: <strong>{data.total_bonus} lei</strong>
			</p>
			<p>Linkul tău de distribuit:</p>
			<input
				readOnly
				value={data.link}
				onFocus={(e) => e.target.select()}
				style={{ width: '100%', padding: 10, borderRadius: 8, border: '1px solid #c6a15b', textAlign: 'center' }}
			/>
		</div>
	);
}

function SignupForm() {
	const [firstName, setFirstName] = useState('');
	const [email, setEmail] = useState('');
	const [result, setResult] = useState(null);
	const [error, setError] = useState(null);
	const [loading, setLoading] = useState(false);

	const submit = (e) => {
		e.preventDefault();
		setLoading(true);
		setError(null);
		const restUrl = (window.frData && window.frData.restUrl) || '';
		fetch(`${restUrl}/referrals`, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ first_name: firstName, email }),
		})
			.then(async (res) => ({ ok: res.ok, data: await res.json() }))
			.then(({ ok, data }) => {
				if (ok && data.link) {
					setResult(data);
				} else {
					setError(data.message || 'Ceva nu a mers — încearcă din nou.');
				}
			})
			.catch(() => setError('Ceva nu a mers — încearcă din nou.'))
			.finally(() => setLoading(false));
	};

	if (result) {
		return (
			<div style={{ maxWidth: 480, margin: '0 auto', textAlign: 'center' }}>
				<p>Gata! Linkul tău de distribuit:</p>
				<input
					readOnly
					value={result.link}
					onFocus={(e) => e.target.select()}
					style={{ width: '100%', padding: 10, borderRadius: 8, border: '1px solid #c6a15b', textAlign: 'center' }}
				/>
				<p style={{ fontSize: 13, marginTop: 12 }}>
					Ți-am trimis și pe email dashboard-ul tău privat, ca să nu pierzi linkul.
				</p>
			</div>
		);
	}

	return (
		<form onSubmit={submit} style={{ maxWidth: 380, margin: '0 auto', textAlign: 'center' }}>
			<input
				type="text"
				placeholder="Prenume"
				required
				value={firstName}
				onChange={(e) => setFirstName(e.target.value)}
				style={{ width: '100%', padding: 10, marginBottom: 10, borderRadius: 8, border: '1px solid #ccc' }}
			/>
			<input
				type="email"
				placeholder="Email"
				required
				value={email}
				onChange={(e) => setEmail(e.target.value)}
				style={{ width: '100%', padding: 10, marginBottom: 10, borderRadius: 8, border: '1px solid #ccc' }}
			/>
			{error && <p style={{ color: '#b00020' }}>{error}</p>}
			<button
				type="submit"
				disabled={loading}
				style={{
					background: '#c6a15b',
					color: '#122118',
					border: 'none',
					borderRadius: 999,
					padding: '12px 32px',
					fontWeight: 600,
					cursor: 'pointer',
				}}
			>
				{loading ? 'Se trimite...' : 'Vreau linkul meu'}
			</button>
		</form>
	);
}

export default function ReferralProgram() {
	const token = useQueryParam('token');
	return token ? <Dashboard token={token} /> : <SignupForm />;
}
