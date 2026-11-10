/* FamilyRound — pagina jocului: reveal-uri fine, asamblarea evantaiului și
   bara sticky de comandă pe mobil. Fail-open + respectă prefers-reduced-motion. */
(function () {
	var root = document.querySelector('.fr-gamepage');
	if (!root) return;

	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	// Every order action uses the same WooCommerce form, preserving the selected
	// quantity. Without JavaScript the link still adds one set and opens the cart.
	document.addEventListener('click', function (event) {
		var link = event.target.closest('[data-fr-order-submit]');
		if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
		var form = root.querySelector('form.cart');
		var submit = form && form.querySelector('.single_add_to_cart_button');
		if (!form || !submit || submit.disabled) return;
		event.preventDefault();
		if (form.requestSubmit) form.requestSubmit(submit);
		else submit.click();
	});

	// Prelaunch email capture: replaces the real add-to-cart form (see FR_PRELAUNCH).
	var preForm = root.querySelector('[data-fr-prelaunch-form]');
	if (preForm) {
		preForm.addEventListener('submit', function (event) {
			event.preventDefault();
			var emailField = preForm.querySelector('input[type="email"]');
			var btn = preForm.querySelector('button');
			btn.disabled = true;
			fetch(preForm.dataset.endpoint, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ email: emailField.value, product_id: preForm.dataset.productId })
			}).then(function (res) {
				return res.json().then(function (data) { return { ok: res.ok, data: data }; });
			}).then(function (result) {
				if (result.ok) {
					preForm.hidden = true;
					var note = root.querySelector('[data-fr-prelaunch-note]');
					if (note) note.hidden = false;
				} else {
					btn.disabled = false;
					window.alert((result.data && result.data.message) || 'A apărut o eroare. Încearcă din nou.');
				}
			}).catch(function () {
				btn.disabled = false;
				window.alert('A apărut o eroare. Încearcă din nou.');
			});
		});
	}

	// Reveals (fade + rise).
	var items = [].slice.call(root.querySelectorAll('.rev'));
	if (!('IntersectionObserver' in window) || reduce) {
		items.forEach(function (e) { e.classList.add('in'); });
	} else {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
		items.forEach(function (e) { io.observe(e); });
		setTimeout(function () { items.forEach(function (e) { e.classList.add('in'); }); }, 2200);
	}

	// Before/after poster reveal: auto-sweeps to show what it does, draggable manually,
	// stops auto-sweeping for good the moment someone touches the slider.
	var reveal = document.querySelector('.gp-reveal');
	var revealRange = reveal && reveal.querySelector('.gp-reveal-range');
	if (reveal && revealRange) {
		var setPct = function (v) {
			revealRange.value = v;
			reveal.style.setProperty('--pct', v + '%');
		};
		var autoId = null;
		var stopAuto = function () { if (autoId) { cancelAnimationFrame(autoId); autoId = null; } };
		revealRange.addEventListener('input', function () {
			reveal.style.setProperty('--pct', revealRange.value + '%');
			stopAuto();
		});
		if (!reduce && 'requestAnimationFrame' in window) {
			var start = null;
			var tick = function (ts) {
				if (!start) start = ts;
				var t = (ts - start) / 1000;
				setPct(Math.round(50 + 42 * Math.sin(t * 0.6)));
				autoId = requestAnimationFrame(tick);
			};
			autoId = requestAnimationFrame(tick);
		}
	}

	// Sticky mobile buy bar: appears after the hero, hides once the final CTA shows.
	var bar = document.getElementById('gpSticky');
	var hero = document.getElementById('top');
	var finalCta = document.querySelector('.gp-final');
	if (bar && hero && 'IntersectionObserver' in window) {
		var pastHero = false, atFinal = false;
		var sync = function () { bar.classList.toggle('is-on', pastHero && !atFinal); };
		new IntersectionObserver(function (e) {
			pastHero = !e[0].isIntersecting && e[0].boundingClientRect.top < 0;
			sync();
		}, { threshold: 0 }).observe(hero);
		if (finalCta) {
			new IntersectionObserver(function (e) {
				atFinal = e[0].isIntersecting;
				sync();
			}, { threshold: 0 }).observe(finalCta);
		}
	}
})();
