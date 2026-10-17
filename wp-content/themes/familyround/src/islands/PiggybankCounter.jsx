import { useEffect, useRef, useState } from 'react';

const fmtLei = (n) => Math.round(n).toLocaleString('ro-RO');
const fmtPct = (n) => n.toLocaleString('ro-RO', { minimumFractionDigits: 1, maximumFractionDigits: 1 });

export default function PiggybankCounter({ compact = false }) {
	const panelRef = useRef(null);
	const [data, setData] = useState(null);
	const [error, setError] = useState(false);
	const [barWidth, setBarWidth] = useState(0);
	const [shownAmount, setShownAmount] = useState(0);

	useEffect(() => {
		const restUrl = (window.frData && window.frData.restUrl) || '';
		fetch(`${restUrl}/piggybank/summary`)
			.then((res) => res.json())
			.then(setData)
			.catch(() => setError(true));
	}, []);

	const mission = data?.mission || null;
	const allocated = data?.allocated || 0;
	const donated = data?.donated || 0;
	const goal = mission?.goal_amount || 0;
	const rawPct = goal > 0 ? (allocated / goal) * 100 : 0;
	const pct = Math.max(0, Math.min(100, rawPct));
	const remaining = Math.max(0, goal - allocated);
	const cause = data?.cause || null;

	// The bar grows to the real percent once. It fails open: the width is always set
	// from JS (immediately on reduced motion, on viewport entry otherwise, and via a
	// safety timeout regardless), so it can never stay stuck at 0 in a background tab.
	useEffect(() => {
		if (!data || !mission) return undefined;
		const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reduce) {
			setBarWidth(pct);
			setShownAmount(allocated);
			return undefined;
		}

		// Count the amount up from 0 — anchored to rAF's own timestamp so it animates
		// frame-by-frame (mixing performance.now() with the rAF clock made it snap
		// straight to the final value on frame one).
		let raf = 0;
		const countUp = (to) => {
			const dur = 1600;
			let start = null;
			const tick = (now) => {
				if (start === null) start = now;
				const t = Math.min(1, (now - start) / dur);
				setShownAmount(to * (1 - Math.pow(1 - t, 3)));
				if (t < 1) raf = requestAnimationFrame(tick);
				else setShownAmount(to);
			};
			raf = requestAnimationFrame(tick);
		};

		let settled = false;
		const finish = (animate) => {
			if (settled) return;
			settled = true;
			setBarWidth(pct);
			if (animate) countUp(allocated);
			else setShownAmount(allocated);
		};

		const el = panelRef.current;
		if ('IntersectionObserver' in window && el) {
			const io = new IntersectionObserver(
				(entries) => entries.forEach((e) => {
					if (e.isIntersecting) {
						finish(true);
						io.disconnect();
					}
				}),
				{ threshold: 0.25 }
			);
			io.observe(el);
			// Fail-safe only: if the panel is never scrolled into view, show the final
			// value — no wasted off-screen animation.
			const t = setTimeout(() => { finish(false); io.disconnect(); }, 6000);
			return () => {
				clearTimeout(t);
				cancelAnimationFrame(raf);
				io.disconnect();
			};
		}
		finish(true);
		return () => cancelAnimationFrame(raf);
	}, [data, mission, pct, allocated]);

	if (error) return null;

	const pusculitaUrl = (window.frData && window.frData.pusculitaUrl) || '/pusculita-familyround/';

	return (
		<div ref={panelRef} className={`fr-pp${compact ? ' fr-pp--compact' : ''}`}>
			{!data ? (
				<p className="fr-pp-loading">Se încarcă…</p>
			) : !mission ? (
				<>
					<p className="fr-pp-label">Pușculița, până acum</p>
					{allocated > 0 && <p className="fr-pp-amount">{fmtLei(allocated)} lei</p>}
					<p className="fr-pp-empty">{allocated > 0 ? 'Suma este alocată. Următoarea cauză va fi anunțată aici.' : 'Pregătim următoarea misiune FamilyRound.'}</p>
				</>
			) : (
				<>
					<p className="fr-pp-label">Pușculița, până acum</p>
					<p className="fr-pp-amount">{fmtLei(shownAmount)} lei</p>
					<p className="fr-pp-amount-sub">puși deoparte din vânzări</p>

					<div className="fr-pp-progress">
						<span className="fr-pp-pct">{fmtPct(pct)}%</span>
						<div
							className="fr-pp-bar"
							role="progressbar"
							aria-valuenow={Math.round(pct)}
							aria-valuemin={0}
							aria-valuemax={100}
							aria-label={`Progres către obiectiv: ${fmtPct(pct)}%`}
						>
							<span className="fr-pp-bar-fill" style={{ transform: `scaleX(${Math.max(0, Math.min(1, barWidth / 100))})` }} />
						</div>
					</div>

					<div className="fr-pp-figures">
						<span>Obiectiv: {fmtLei(goal)} lei</span>
						<span>Mai avem: {fmtLei(remaining)} lei</span>
					</div>

					<hr className="fr-pp-divider" />

					<p className="fr-pp-label">Destinația fondurilor</p>
					<p className="fr-pp-cause">{cause ? cause.name : 'Cauza urmează să fie anunțată.'}</p>

					{donated > 0 ? (
						<p className="fr-pp-status">
							{fmtLei(allocated)} lei alocați · <strong>{fmtLei(donated)} lei donați</strong>
						</p>
					) : (
						<p className="fr-pp-status">Suma afișată este alocată, încă nedonată.</p>
					)}

					{cause && cause.note && <p className="fr-pp-status">{cause.note}</p>}

					{compact && (
						<a className="fr-btn-solid fr-pp-btn" href={pusculitaUrl}>
							Vezi Pușculița și donațiile →
						</a>
					)}

					{!compact && mission.description && <p className="fr-pp-desc">{mission.description}</p>}

					{!compact && data.past_missions && data.past_missions.length > 0 && (
						<div className="fr-pp-history">
							<p className="fr-pp-history-title">Campanii încheiate</p>
							<ul>
								{data.past_missions.map((m) => (
									<li key={m.id}>
										<span>{m.title}</span>
										<span>
											{fmtLei(m.allocated)} / {fmtLei(m.goal_amount)} lei
										</span>
									</li>
								))}
							</ul>
						</div>
					)}
				</>
			)}
		</div>
	);
}
