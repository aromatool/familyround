import { createRoot } from 'react-dom/client';
import PiggybankCounter from './islands/PiggybankCounter';
import MapPoints from './islands/MapPoints';
import ReferralProgram from './islands/ReferralProgram';

const islands = {
	'piggybank-counter': PiggybankCounter,
	'map-points': MapPoints,
	'referral-program': ReferralProgram,
};

document.querySelectorAll('[data-fr-island]').forEach((el) => {
	const name = el.getAttribute('data-fr-island');
	const Component = islands[name];
	if (Component) {
		createRoot(el).render(<Component />);
	}
});
