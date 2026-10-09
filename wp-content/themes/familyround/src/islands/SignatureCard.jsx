import { useEffect, useRef, useState } from 'react';
import gsap from 'gsap';

const CHALLENGES = [
	'Priviți-vă în ochi 30 de secunde, fără să vorbiți.',
	'Gătiți ceva nou, împreună, fără rețetă.',
	'Faceți un picnic în living, pe jos.',
	'Spuneți-vă, pe rând, o amintire preferată.',
];

export default function SignatureCard() {
	const sceneRef = useRef(null);
	const tiltRef = useRef(null);
	const flipRef = useRef(null);
	const quickRotX = useRef(null);
	const quickRotY = useRef(null);
	const [flipped, setFlipped] = useState(false);
	const [challengeIndex, setChallengeIndex] = useState(0);

	useEffect(() => {
		gsap.set(tiltRef.current, { rotation: -3 });
		gsap.from(sceneRef.current, { opacity: 0, y: 16, duration: 0.6, ease: 'power2.out', delay: 0.15 });

		quickRotX.current = gsap.quickTo(tiltRef.current, 'rotationX', { duration: 0.4, ease: 'power3.out' });
		quickRotY.current = gsap.quickTo(tiltRef.current, 'rotationY', { duration: 0.4, ease: 'power3.out' });

		const scene = sceneRef.current;
		const onMove = (e) => {
			const rect = scene.getBoundingClientRect();
			const px = (e.clientX - rect.left) / rect.width - 0.5;
			const py = (e.clientY - rect.top) / rect.height - 0.5;
			quickRotY.current(px * 16);
			quickRotX.current(py * -16);
		};
		const onLeave = () => {
			quickRotX.current(0);
			quickRotY.current(0);
		};

		scene.addEventListener('mousemove', onMove);
		scene.addEventListener('mouseleave', onLeave);
		return () => {
			scene.removeEventListener('mousemove', onMove);
			scene.removeEventListener('mouseleave', onLeave);
		};
	}, []);

	const handleClick = () => {
		const next = !flipped;
		if (next) {
			setChallengeIndex((i) => (i + 1) % CHALLENGES.length);
		}
		gsap.to(flipRef.current, { rotationY: next ? 180 : 0, duration: 0.6, ease: 'power2.inOut' });
		setFlipped(next);
	};

	return (
		<div className="fr-card-scene" ref={sceneRef}>
			<div className="fr-card-tilt" ref={tiltRef}>
				<div className="fr-card-flip" ref={flipRef} onClick={handleClick} role="button" tabIndex={0}>
					<div className="fr-card-face fr-card-face--front">
						<span className="fr-card-mark">FamilyRound</span>
						<span className="fr-card-number">Cartonaș 01 / 30</span>
						<span className="fr-card-hint">Atinge pentru a-l întoarce</span>
					</div>
					<div className="fr-card-face fr-card-face--back">
						<p>{CHALLENGES[challengeIndex]}</p>
					</div>
				</div>
			</div>
		</div>
	);
}
