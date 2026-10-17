import { useEffect, useRef, useState } from 'react';

const SHARE_TEXT = 'Am ales o experiență FamilyRound pentru familia mea. Dacă vrei să descoperi și tu jocurile lor, intră prin linkul meu. La prima ta comandă, FamilyRound mai pune 5 lei pentru fiecare joc în Pușculiță, fără să plătești tu în plus:';
const lei = (amount) => `${new Intl.NumberFormat('ro-RO', { maximumFractionDigits: 0 }).format(Number(amount) || 0)} lei`;

function useQueryParam(name) {
	const [value] = useState(() => new URLSearchParams(window.location.search).get(name));
	return value;
}

function ShareLink({ link }) {
	const inputRef = useRef(null);
	const [status, setStatus] = useState('');
	const canShare = typeof navigator.share === 'function';
	const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(`${SHARE_TEXT} ${link}`)}`;

	const copy = async () => {
		try {
			if (navigator.clipboard?.writeText) {
				await navigator.clipboard.writeText(link);
			} else {
				inputRef.current?.select();
				if (!document.execCommand('copy')) throw new Error('copy failed');
			}
			setStatus('Linkul a fost copiat. Îl poți trimite oriunde dorești.');
		} catch {
			inputRef.current?.select();
			setStatus('Selectează și copiază linkul din câmpul de mai sus.');
		}
	};

	const share = async () => {
		if (!navigator.share) {
			await copy();
			return;
		}
		try {
			await navigator.share({ title: 'FamilyRound', text: SHARE_TEXT, url: link });
			setStatus('Mulțumim că dai ideea mai departe.');
		} catch (error) {
			if (error?.name !== 'AbortError') setStatus('Poți copia linkul sau îl poți trimite pe WhatsApp.');
		}
	};

	return (
		<div className="fr-referral-share">
			<label htmlFor="fr-referral-link">Linkul tău personal</label>
			<input id="fr-referral-link" ref={inputRef} readOnly value={link} onFocus={(event) => event.target.select()} />
			<div className="fr-referral-share-actions">
				{canShare && <button type="button" className="fr-referral-primary" onClick={share}>Distribuie linkul ↗</button>}
				<button type="button" className={canShare ? 'fr-referral-secondary' : 'fr-referral-primary'} onClick={copy}>Copiază linkul</button>
				<a className="fr-referral-whatsapp" href={whatsappUrl} target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>
			</div>
			<p className="fr-referral-share-status" role="status" aria-live="polite">{status}</p>
			<p className="fr-referral-share-hint">Poți adăuga o poză sau propriul mesaj când îl distribui. Bonusul de 5 lei per joc se acordă la prima comandă a fiecărui cumpărător nou.</p>
		</div>
	);
}

function Dashboard({ token }) {
	const [data, setData] = useState(null);
	const [error, setError] = useState(false);

	useEffect(() => {
		const restUrl = (window.frData && window.frData.restUrl) || '';
		fetch(`${restUrl}/referrals/me`, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ token }),
			cache: 'no-store',
		})
			.then((res) => {
				if (!res.ok) throw new Error('not found');
				return res.json();
			})
			.then(setData)
			.catch(() => setError(true));
	}, [token]);

	if (error) return <><p className="fr-referral-feedback">Linkul de acces nu mai este valid. Completează formularul și îți trimitem unul nou.</p><SignupForm /></>;
	if (!data) return <p className="fr-referral-feedback">Pregătim linkul tău…</p>;

	return (
		<div>
			<p className="fr-referral-hello">Salut, {data.first_name}!</p>
			<h2 className="fr-referral-impact-title">Timpul dat mai departe se adună.</h2>
			<p className="fr-referral-impact-total"><strong>{lei(data.total_impact)}</strong> puși deoparte în Pușculiță prin comenzile și recomandările tale.</p>
			<div className="fr-referral-stats">
				<div><strong>{lei(data.own_amount)}</strong><span>din jocurile cumpărate de tine</span></div>
				<div><strong>{lei(data.total_bonus)}</strong><span>în plus, din recomandări validate</span></div>
				<div><strong>{data.validated_count}</strong><span>{data.validated_count === 1 ? 'comandă făcută prin link' : 'comenzi făcute prin link'}</span></div>
			</div>
			<p className="fr-referral-impact-note">Sumele sunt alocate de FamilyRound. Banii transferați către o cauză vor fi arătați separat, după ce are loc transferul.</p>
			<p className="fr-referral-code">Codul tău: <strong>{data.code}</strong></p>
			<ShareLink link={data.link} />
		</div>
	);
}

function PublicDashboard({ code }) {
	const [data, setData] = useState(null);
	const [error, setError] = useState('');
	const [lookup, setLookup] = useState(code);

	useEffect(() => {
		const restUrl = (window.frData && window.frData.restUrl) || '';
		fetch(`${restUrl}/referrals/public`, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ code }),
			cache: 'no-store',
		})
			.then(async (res) => {
				const result = await res.json();
				if (!res.ok) throw new Error(result.message || 'Nu am putut încărca impactul codului.');
				return result;
			})
			.then(setData)
			.catch((problem) => setError(problem.message));
	}, [code]);

	if (error) return (
		<form className="fr-referral-form" onSubmit={(event) => {
			event.preventDefault();
			const nextCode = lookup.trim().toUpperCase();
			if (nextCode) window.location.href = `${window.frData?.referralUrl || '/da-timpul-mai-departe/'}?code=${encodeURIComponent(nextCode)}`;
		}}>
			<p className="fr-referral-error" role="alert">{error}</p>
			<label htmlFor="fr-public-code">Codul de recomandare</label>
			<input id="fr-public-code" type="text" autoComplete="off" maxLength="20" required value={lookup} onChange={(event) => setLookup(event.target.value)} />
			<button type="submit" className="fr-referral-primary">Încearcă din nou</button>
		</form>
	);
	if (!data) return <p className="fr-referral-feedback" role="status">Pregătim impactul codului…</p>;

	return (
		<div>
			<h2 className="fr-referral-impact-title">Cercul crește cu fiecare recomandare.</h2>
			<p className="fr-referral-code">Codul de recomandare: <strong>{data.code}</strong></p>
			<p className="fr-referral-impact-total"><strong>{lei(data.total_bonus)}</strong> adăugați în Pușculiță prin acest cod.</p>
			<div className="fr-referral-stats">
				<div><strong>{data.validated_count}</strong><span>{data.validated_count === 1 ? 'recomandare validată' : 'recomandări validate'}</span></div>
				<div><strong>{data.eligible_games}</strong><span>{data.eligible_games === 1 ? 'joc cumpărat prin recomandare' : 'jocuri cumpărate prin recomandare'}</span></div>
			</div>
			<p className="fr-referral-impact-note">Aici apar doar recomandările confirmate. Suma este pusă deoparte de FamilyRound; donațiile efective sunt prezentate separat în Pușculiță.</p>
			<a className="fr-referral-map-link" href={`${window.frData?.mapUrl || '/harta-familyround/'}?code=${encodeURIComponent(data.code)}`}>Vezi pe hartă unde au fost cumpărate jocurile prin codul tău →</a>
			<ShareLink link={data.link} />
			<p className="fr-referral-private-hint">Păstrează codul din email. Îl poți introduce oricând în „Vezi impactul” ca să revii aici.</p>
		</div>
	);
}

function SignupForm() {
	const [firstName, setFirstName] = useState('');
	const [email, setEmail] = useState('');
	const [updatesOptIn, setUpdatesOptIn] = useState(false);
	const [result, setResult] = useState(null);
	const [error, setError] = useState(null);
	const [loading, setLoading] = useState(false);

	const submit = (event) => {
		event.preventDefault();
		setLoading(true);
		setError(null);
		const restUrl = (window.frData && window.frData.restUrl) || '';
		fetch(`${restUrl}/referrals`, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ first_name: firstName, email, updates_opt_in: updatesOptIn }),
		})
			.then(async (res) => ({ ok: res.ok, data: await res.json() }))
			.then(({ ok, data }) => {
				if (ok && data.link) setResult(data);
				else setError(data.message || 'Nu am putut pregăti linkul. Încearcă din nou.');
			})
			.catch(() => setError('Nu am putut pregăti linkul. Încearcă din nou.'))
			.finally(() => setLoading(false));
	};

	if (result) {
		return (
			<div>
				<p className="fr-referral-hello">Gata, linkul tău este aici.</p>
				<ShareLink link={result.link} />
				<p className="fr-referral-email-note">Ți-am trimis pe email codul și linkul. Păstrează mesajul: cu acel cod poți reveni oricând să vezi recomandările validate.</p>
			</div>
		);
	}

	return (
		<form className="fr-referral-form" onSubmit={submit}>
			<label htmlFor="fr-referral-name">Prenume</label>
			<input id="fr-referral-name" type="text" autoComplete="given-name" required value={firstName} onChange={(event) => setFirstName(event.target.value)} />
			<label htmlFor="fr-referral-email">Email</label>
			<input id="fr-referral-email" type="email" autoComplete="email" required value={email} onChange={(event) => setEmail(event.target.value)} />
			<label className="fr-referral-consent-choice" htmlFor="fr-referral-updates"><input id="fr-referral-updates" type="checkbox" checked={updatesOptIn} onChange={(event) => setUpdatesOptIn(event.target.checked)} /> <span>Vreau să primesc noutăți despre recomandările mele, cel mult o dată pe săptămână, doar când există comenzi noi validate. Mă pot dezabona oricând.</span></label>
			{error && <p className="fr-referral-error" role="alert">{error}</p>}
			<button type="submit" className="fr-referral-primary" disabled={loading}>{loading ? 'Pregătim linkul…' : 'Primește linkul personal ↗'}</button>
			<p>Îți trimitem linkul și pe email. Nu cerem prietenilor tăi nicio contribuție în plus.</p>
		</form>
	);
}

export default function ReferralProgram() {
	const token = useQueryParam('token');
	const code = useQueryParam('code');
	return token ? <Dashboard token={token} /> : code ? <PublicDashboard code={code} /> : <SignupForm />;
}
