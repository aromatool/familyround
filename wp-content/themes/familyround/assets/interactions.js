(function () {
	// The referral code opens a small, accessible path to public impact totals.
	(function () {
		var trigger = document.querySelector('[data-fr-impact-open]');
		var dialog = document.getElementById('fr-impact-dialog');
		if (!trigger || !dialog) return;
		var closeButton = dialog.querySelector('[data-fr-impact-close]');
		var codeInput = dialog.querySelector('#fr-impact-code');
		var closeTimer;
		var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

		var closeDialog = function () {
			if (!dialog.open || dialog.dataset.state === 'closing') return;
			dialog.dataset.state = 'closing';
			window.clearTimeout(closeTimer);
			closeTimer = window.setTimeout(function () {
				dialog.close();
				delete dialog.dataset.state;
				trigger.focus({ preventScroll: true });
			}, reduceMotion.matches ? 120 : 220);
		};

		trigger.addEventListener('click', function () {
			if (typeof dialog.showModal !== 'function') {
				window.location.href = dialog.querySelector('form').action;
				return;
			}
			window.clearTimeout(closeTimer);
			dialog.dataset.state = 'opening';
			dialog.showModal();
			requestAnimationFrame(function () {
				requestAnimationFrame(function () {
					delete dialog.dataset.state;
					if (codeInput) codeInput.focus({ preventScroll: true });
				});
			});
		});
		if (closeButton) closeButton.addEventListener('click', closeDialog);
		dialog.addEventListener('cancel', function (event) {
			event.preventDefault();
			closeDialog();
		});
		dialog.addEventListener('click', function (event) {
			if (event.target !== dialog) return;
			var bounds = dialog.getBoundingClientRect();
			if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) closeDialog();
		});
	})();

	// Sticky header shadow
	var header = document.querySelector('.fr-site-header');
	if (header) {
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 10);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	// Mobile menu — full-screen overlay toggle
	(function () {
		var toggle = document.querySelector('.fr-nav-toggle');
		if (!toggle || !header) return;
		var root = document.documentElement;
		var menu = document.getElementById('fr-menu');
		var menuBreakpoint = 1120;

		var setOpen = function (open, returnFocus) {
			header.classList.toggle('menu-open', open);
			root.classList.toggle('fr-menu-open', open);
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			toggle.setAttribute('aria-label', open ? 'Închide meniul' : 'Deschide meniul');
			if (open && menu) {
				requestAnimationFrame(function () {
					if (!header.classList.contains('menu-open')) return;
					var first = menu.querySelector('.fr-nav a');
					if (first) first.focus({ preventScroll: true });
				});
			} else if (returnFocus) {
				toggle.focus({ preventScroll: true });
			}
		};

		toggle.addEventListener('click', function () {
			setOpen(!header.classList.contains('menu-open'), false);
		});

		// A destination always closes the full-screen menu, including secondary links.
		document.querySelectorAll('.fr-menu a').forEach(function (a) {
			a.addEventListener('click', function () {
				var targetUrl = new URL(a.href, window.location.href);
				var samePageAnchor = targetUrl.pathname === window.location.pathname && targetUrl.hash;
				setOpen(false, false);
				if (samePageAnchor) {
					requestAnimationFrame(function () {
						var target = document.getElementById(decodeURIComponent(targetUrl.hash.slice(1)));
						if (target) {
							target.setAttribute('tabindex', '-1');
							target.focus({ preventScroll: true });
						}
					});
				}
			});
		});

		// Keep keyboard focus inside the overlay while it is open.
		document.addEventListener('keydown', function (e) {
			if (!header.classList.contains('menu-open')) return;
			if (e.key === 'Escape') {
				setOpen(false, true);
				return;
			}
			if (e.key === 'Tab') {
				var focusable = [].slice.call(header.querySelectorAll('a, button')).filter(function (element) {
					return element.getClientRects().length && getComputedStyle(element).visibility !== 'hidden';
				});
				if (!focusable.length) return;
				var first = focusable[0];
				var last = focusable[focusable.length - 1];
				if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
				else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
			}
		});

		// Close when resizing up to desktop
		window.addEventListener('resize', function () {
			if (window.innerWidth > menuBreakpoint && header.classList.contains('menu-open')) {
				setOpen(false, false);
			}
		}, { passive: true });
	})();

	// One-time entrance for .fr-fade-up — opt in only on capable, motion-OK browsers.
	// Content stays visible otherwise, and a safety timeout guarantees it can never
	// get stuck hidden (e.g. background tabs that never fire IntersectionObserver).
	(function () {
		var items = document.querySelectorAll('.fr-fade-up');
		if (!items.length) return;

		var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reduceMotion || !('IntersectionObserver' in window)) return; // leave visible

		document.documentElement.classList.add('fr-js');

		var revealAll = function () {
			items.forEach(function (el) { el.classList.add('fr-in'); });
		};

		var io = new IntersectionObserver(function (entries, obs) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('fr-in');
					obs.unobserve(entry.target);
				}
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

		items.forEach(function (el) { io.observe(el); });

		// Fail-safe: nothing stays invisible past 1.6s regardless of visibility state.
		setTimeout(revealAll, 1600);
	})();

	// "Aruncă o privire în cutie" — crossfade between the set photo and the peek photo.
	(function () {
		document.querySelectorAll('[data-fr-peek-toggle]').forEach(function (btn) {
			var product = btn.closest('.fr-product');
			if (!product) return;
			var figure = product.querySelector('[data-fr-peek]');
			var setImg = product.querySelector('.fr-product-img--set');
			var peekImg = product.querySelector('.fr-product-img--peek');
			if (!figure || !setImg || !peekImg) return;

			var setLabel = figure.getAttribute('data-set-label') || btn.textContent;
			var backLabel = figure.getAttribute('data-back-label') || setLabel;

			btn.addEventListener('click', function () {
				var open = peekImg.classList.toggle('is-active');
				setImg.classList.toggle('is-active', !open);
				btn.setAttribute('aria-pressed', open ? 'true' : 'false');
				btn.textContent = open ? backLabel : setLabel;
			});
		});
	})();

	// Hero photo parallax — a gentle drift as you scroll, so the hero feels alive
	// rather than static. Desktop + motion-OK only; the image has overscan (118%
	// tall, offset up) so it never reveals an edge.
	(function () {
		var photo = document.querySelector('.fr-hero-photo');
		if (!photo) return;
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reduce) return;

		var hero = document.querySelector('.fr-hero');
		var ticking = false;

		var apply = function () {
			ticking = false;
			if (window.innerWidth <= 860) { photo.style.transform = ''; return; }
			var rect = hero.getBoundingClientRect();
			if (rect.bottom < 0 || rect.top > window.innerHeight) return; // off-screen
			// progress of the hero through the viewport → small vertical drift
			var y = Math.max(-60, Math.min(60, (window.scrollY || window.pageYOffset) * 0.12));
			photo.style.transform = 'translate3d(0,' + y.toFixed(1) + 'px,0)';
		};
		var onScroll = function () {
			if (!ticking) { ticking = true; requestAnimationFrame(apply); }
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll, { passive: true });
		apply();
	})();

	// Section-photo parallax — each .fr-parallax-img drifts gently against the scroll,
	// giving the page depth. Images are pre-scaled (see .fr-parallax-img) so the drift
	// never shows an edge. Desktop + motion-OK only.
	(function () {
		var imgs = [].slice.call(document.querySelectorAll('.fr-parallax-img'));
		if (!imgs.length) return;
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reduce) return;

		var ticking = false;
		var apply = function () {
			ticking = false;
			if (window.innerWidth <= 720) {
				imgs.forEach(function (img) { img.style.transform = 'scale(1.12)'; });
				return;
			}
			var vh = window.innerHeight;
			imgs.forEach(function (img) {
				var r = img.getBoundingClientRect();
				if (r.bottom < -120 || r.top > vh + 120) return; // off-screen
				var delta = (r.top + r.height / 2) - vh / 2;     // distance from viewport center
				var cap = r.height * 0.05;                        // stay within the 6% overscan
				var y = Math.max(-cap, Math.min(cap, delta * -0.05));
				img.style.transform = 'translate3d(0,' + y.toFixed(1) + 'px,0) scale(1.12)';
			});
		};
		var onScroll = function () {
			if (!ticking) { ticking = true; requestAnimationFrame(apply); }
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll, { passive: true });
		apply();
	})();

	// The order confirmation offers the buyer their personal referral link.
	// Native sharing opens the device's available apps; copying is the fallback.
	document.querySelectorAll('.fr-thanks-share').forEach(function (panel) {
		if (!navigator.share) panel.classList.add('fr-no-native-share');
	});
	document.addEventListener('click', async function (event) {
		var button = event.target.closest('[data-fr-share-action]');
		if (!button) return;
		var panel = button.closest('.fr-thanks-share');
		var status = panel && panel.querySelector('[data-fr-share-status]');
		var field = panel && panel.querySelector('#fr-thanks-referral-link');
		var url = button.getAttribute('data-fr-share-url');
		if (!url) return;

		var copy = async function () {
			try {
				if (navigator.clipboard && navigator.clipboard.writeText) {
					await navigator.clipboard.writeText(url);
				} else {
					if (field) field.select();
					if (!document.execCommand('copy')) throw new Error('copy failed');
				}
				if (status) status.textContent = 'Linkul a fost copiat. Îl poți trimite oriunde dorești.';
			} catch (error) {
				if (field) field.select();
				if (status) status.textContent = 'Selectează și copiază linkul din câmpul de mai sus.';
			}
		};

		if (button.getAttribute('data-fr-share-action') === 'copy' || !navigator.share) {
			await copy();
			return;
		}
		try {
			await navigator.share({
				title: 'FamilyRound',
				text: button.getAttribute('data-fr-share-text') || 'Descoperă FamilyRound.',
				url: url,
			});
			if (status) status.textContent = 'Mulțumim că dai ideea mai departe.';
		} catch (error) {
			if (error.name !== 'AbortError' && status) status.textContent = 'Poți copia linkul sau îl poți trimite pe WhatsApp.';
		}
	});

	// Keep every FamilyRound pillar visible while a slow, quiet emphasis moves
	// between them. Pointer and keyboard interaction always takes priority.
	(function () {
		var chapters = document.querySelector('[data-fr-hero-chapters]');
		if (!chapters) return;
		var items = [].slice.call(chapters.querySelectorAll('.fr-home-hero-chapter'));
		if (items.length < 2) return;
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var compact = window.matchMedia && window.matchMedia('(max-width: 700px)').matches;
		var active = Math.max(0, items.findIndex(function (item) { return item.classList.contains('is-active'); }));
		var timer = 0;

		var show = function (index) {
			active = index;
			items.forEach(function (item, itemIndex) { item.classList.toggle('is-active', itemIndex === active); });
		};
		var stop = function () { window.clearInterval(timer); timer = 0; };
		var start = function () {
			if (reduce || compact || timer) return;
			timer = window.setInterval(function () { show((active + 1) % items.length); }, 6200);
		};

		items.forEach(function (item, index) {
			item.addEventListener('mouseenter', function () { stop(); show(index); });
			item.addEventListener('focus', function () { stop(); show(index); });
		});
		chapters.addEventListener('mouseleave', start);
		chapters.addEventListener('focusout', function (event) {
			if (!chapters.contains(event.relatedTarget)) start();
		});
		start();
	})();
})();
