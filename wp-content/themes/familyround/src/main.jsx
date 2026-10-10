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
		const props = {
			compact: el.dataset.frVariant === 'compact',
			variant: el.dataset.frVariant || 'default',
			title: el.dataset.title,
			subtitle: el.dataset.subtitle,
			href: el.dataset.href,
		};
		createRoot(el).render(<Component {...props} />);
	}
});
