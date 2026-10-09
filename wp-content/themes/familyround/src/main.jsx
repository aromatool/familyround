import { createRoot } from 'react-dom/client';
import PiggybankCounter from './islands/PiggybankCounter';
import MapPoints from './islands/MapPoints';
import ReferralProgram from './islands/ReferralProgram';
import SignatureCard from './islands/SignatureCard';

const islands = {
	'piggybank-counter': PiggybankCounter,
	'map-points': MapPoints,
	'referral-program': ReferralProgram,
	'signature-card': SignatureCard,
};

document.querySelectorAll('[data-fr-island]').forEach((el) => {
	const name = el.getAttribute('data-fr-island');
	const Component = islands[name];
	if (Component) {
		const props = el.dataset.frVariant === 'compact' ? { compact: true } : {};
		createRoot(el).render(<Component {...props} />);
	}
});
