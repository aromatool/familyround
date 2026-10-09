import { createRoot } from 'react-dom/client';
import PiggybankCounter from './islands/PiggybankCounter';

const islands = {
	'piggybank-counter': PiggybankCounter,
};

document.querySelectorAll('[data-fr-island]').forEach((el) => {
	const name = el.getAttribute('data-fr-island');
	const Component = islands[name];
	if (Component) {
		createRoot(el).render(<Component />);
	}
});
