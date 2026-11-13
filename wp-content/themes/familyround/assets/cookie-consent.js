(function () {
  'use strict';

  var STORAGE_KEY = 'fr_cookie_consent';

  function getConsent() {
    try {
      return window.localStorage.getItem(STORAGE_KEY);
    } catch (e) {
      return null;
    }
  }

  function setConsent(value) {
    try {
      window.localStorage.setItem(STORAGE_KEY, value);
    } catch (e) {
      // Private browsing or storage disabled — consent just won't persist across visits.
    }
  }

  function buildBanner() {
    var banner = document.createElement('div');
    banner.id = 'fr-cookie-banner';
    banner.setAttribute('role', 'dialog');
    banner.setAttribute('aria-label', 'Preferințe cookie-uri');
    banner.style.cssText =
      'position:fixed;left:16px;right:16px;bottom:16px;z-index:9999;max-width:520px;' +
      'margin:0 auto;background:var(--fr-green, #153a2b);color:var(--fr-cream, #e8ece5);' +
      'border-radius:16px;padding:20px 22px;box-shadow:0 18px 44px -20px rgba(15,38,28,0.5);' +
      'font-family:var(--fr-font-sans, sans-serif);display:flex;flex-direction:column;gap:14px;';

    var text = document.createElement('p');
    text.style.cssText = 'margin:0;font-size:14px;line-height:1.5;';
    text.textContent =
      'Folosim cookie-uri pentru a înțelege cum folosești site-ul (Google Analytics). Le poți accepta sau refuza oricând.';

    var actions = document.createElement('div');
    actions.style.cssText = 'display:flex;gap:10px;flex-wrap:wrap;';

    var decline = document.createElement('button');
    decline.type = 'button';
    decline.textContent = 'Refuză';
    decline.style.cssText =
      'background:transparent;color:var(--fr-cream, #e8ece5);border:1px solid rgba(232,236,229,0.4);' +
      'border-radius:999px;padding:10px 18px;font-size:14px;cursor:pointer;';

    var accept = document.createElement('button');
    accept.type = 'button';
    accept.textContent = 'Acceptă';
    accept.style.cssText =
      'background:var(--fr-gold, #d79421);color:var(--fr-green-dark, #0f261c);border:none;' +
      'border-radius:999px;padding:10px 20px;font-size:14px;font-weight:600;cursor:pointer;';

    accept.addEventListener('click', function () {
      setConsent('accepted');
      hideBanner();
      if (window.frLoadGA) window.frLoadGA();
    });
    decline.addEventListener('click', function () {
      setConsent('declined');
      hideBanner();
    });

    actions.appendChild(accept);
    actions.appendChild(decline);
    banner.appendChild(text);
    banner.appendChild(actions);
    return banner;
  }

  var bannerEl = null;

  function showBanner() {
    if (bannerEl) return;
    bannerEl = buildBanner();
    document.body.appendChild(bannerEl);
  }

  function hideBanner() {
    if (bannerEl && bannerEl.parentNode) bannerEl.parentNode.removeChild(bannerEl);
    bannerEl = null;
  }

  function init() {
    var consent = getConsent();
    if (consent === 'accepted') {
      if (window.frLoadGA) window.frLoadGA();
    } else if (consent !== 'declined') {
      showBanner();
    }
    document.addEventListener('click', function (e) {
      if (e.target.closest('[data-fr-cookie-prefs]')) {
        e.preventDefault();
        showBanner();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
