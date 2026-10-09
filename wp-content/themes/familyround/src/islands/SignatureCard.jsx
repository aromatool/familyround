import { useEffect, useRef } from 'react';
import gsap from 'gsap';

const FRONTS = ['card-19.jpg', 'card-01.jpg', 'card-29.jpg', 'card-11.jpg'];
const AUTO_FLIP_MS = 4500;

export default function SignatureCard() {
	const sceneRef = useRef(null);
	const tiltRef = useRef(null);
	const flipRef = useRef(null);
	const frontImgRef = useRef(null);
	const quickRotX = useRef(null);
	const quickRotY = useRef(null);
	const flippedRef = useRef(false);
	const frontIndexRef = useRef(0);
	const intervalRef = useRef(null);

	const assetsUrl = (typeof window !== 'undefined' && window.frData && window.frData.assetsUrl) || '';

	const doFlip = () => {
		const next = !flippedRef.current;
		if (next) {
			frontIndexRef.current = (frontIndexRef.current + 1) % FRONTS.length;
			if (frontImgRef.current) {
				frontImgRef.current.src = `${assetsUrl}${FRONTS[frontIndexRef.current]}`;
			}
		}
		gsap.to(flipRef.current, { rotationY: next ? 180 : 0, duration: 0.6, ease: 'power2.inOut' });
		flippedRef.current = next;
	};

	const startAutoFlip = () => {
		clearInterval(intervalRef.current);
		intervalRef.current = setInterval(doFlip, AUTO_FLIP_MS);
	};

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
			quickRotY.current(px * 14);
			quickRotX.current(py * -14);
		};
		const onLeave = () => {
			quickRotX.current(0);
			quickRotY.current(0);
		};

		scene.addEventListener('mousemove', onMove);
		scene.addEventListener('mouseleave', onLeave);
		startAutoFlip();

		return () => {
			scene.removeEventListener('mousemove', onMove);
			scene.removeEventListener('mouseleave', onLeave);
			clearInterval(intervalRef.current);
		};
	}, []);

	const handleClick = () => {
		doFlip();
		startAutoFlip();
	};

	return (
		<div className="fr-card-scene" ref={sceneRef}>
			<div className="fr-card-tilt" ref={tiltRef}>
				<div className="fr-card-flip" ref={flipRef} onClick={handleClick} role="button" tabIndex={0}>
					<div className="fr-card-face fr-card-face--front">
						<img src={`${assetsUrl}card-back.jpg`} alt="Verso cartonaș FamilyRound" />
					</div>
					<div className="fr-card-face fr-card-face--back">
						<img ref={frontImgRef} src={`${assetsUrl}${FRONTS[0]}`} alt="O provocare FamilyRound" />
					</div>
				</div>
			</div>
			<p className="fr-card-hint">
				Întoarce un cartonaș
				<br />
				și vezi unde vă duce.
			</p>
		</div>
	);
}
