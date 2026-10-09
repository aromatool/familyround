import { useEffect, useRef, useState } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const JAR_TOP = 40;
const JAR_HEIGHT = 90;
const JAR_BOTTOM = JAR_TOP + JAR_HEIGHT;

function useCountUp(target, start, duration = 1200) {
	const [value, setValue] = useState(0);
	const frameRef = useRef(null);

	useEffect(() => {
		if (!start || !target) return undefined;

		let startTs = null;
		const step = (ts) => {
			if (!startTs) startTs = ts;
			const progress = Math.min(1, (ts - startTs) / duration);
			const eased = 1 - Math.pow(1 - progress, 3);
			setValue(target * eased);
			if (progress < 1) frameRef.current = requestAnimationFrame(step);
		};
		frameRef.current = requestAnimationFrame(step);

		return () => cancelAnimationFrame(frameRef.current);
	}, [target, start, duration]);

	return value;
}

export default function PiggybankCounter() {
	const wrapperRef = useRef(null);
	const fillRef = useRef(null);
	const [data, setData] = useState(null);
	const [error, setError] = useState(false);
	const [started, setStarted] = useState(false);

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
	const animatedAllocated = useCountUp(allocated, started);

	useEffect(() => {
		if (!data || !data.mission || !wrapperRef.current) return undefined;

		const trigger = ScrollTrigger.create({
			trigger: wrapperRef.current,
			start: 'top 85%',
			once: true,
			onEnter: () => {
				setStarted(true);
				const targetHeight = (JAR_HEIGHT * pct) / 100;
				gsap.to(fillRef.current, {
					attr: { height: targetHeight, y: JAR_BOTTOM - targetHeight },
					duration: 1.4,
					ease: 'power3.out',
				});
			},
		});

		return () => trigger.kill();
	}, [data, pct]);

	if (error) return null;
	if (!data) return <p>Se încarcă...</p>;
	if (!data.mission) return <p>Nicio misiune activă momentan.</p>;

	return (
		<div className="fr-piggy-widget" ref={wrapperRef}>
			<svg className="fr-jar" viewBox="0 0 120 140" aria-hidden="true">
				<defs>
					<clipPath id="fr-jar-clip">
						<rect x="10" y={JAR_TOP} width="100" height={JAR_HEIGHT} rx="18" />
					</clipPath>
				</defs>
				<rect x="35" y="20" width="50" height="22" rx="6" className="fr-jar-lid" />
				<g clipPath="url(#fr-jar-clip)">
					<rect ref={fillRef} x="10" y={JAR_BOTTOM} width="100" height="0" className="fr-jar-fill" />
				</g>
				<rect x="10" y={JAR_TOP} width="100" height={JAR_HEIGHT} rx="18" className="fr-jar-outline" />
			</svg>
			<p className="fr-piggy-title">{data.mission.title}</p>
			<p className="fr-piggy-amount">
				{Math.round(animatedAllocated)} lei din {Math.round(goal)} lei
			</p>
		</div>
	);
}
