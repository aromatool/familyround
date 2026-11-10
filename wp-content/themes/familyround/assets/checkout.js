(function () {
  'use strict';

  function localizePickupPointControls(list) {
    if (!list) return;
    list.querySelectorAll('select[id^="coleteonline_service_shipping_point"] option[value="0"]').forEach((option) => {
      if (option.textContent !== 'Alege punctul de ridicare') option.textContent = 'Alege punctul de ridicare';
    });
    list.querySelectorAll('.select2-selection__rendered').forEach((rendered) => {
      if (rendered.textContent.trim() === 'Choose shipping point') rendered.textContent = 'Alege punctul de ridicare';
    });
    list.querySelectorAll('.coleteonline-open-point-picker').forEach((button) => {
      if (button.textContent.trim() !== 'Alege punctul pe hartă') button.textContent = 'Alege punctul pe hartă';
    });
  }

  function initShippingLabels() {
    const update = () => {
      const list = document.querySelector('#order_review #shipping_method');
      localizePickupPointControls(list);
      renderShippingChoices(list);
    };
    update();
    const review = document.getElementById('order_review');
    if (window.jQuery) {
      window.jQuery(document.body).on('updated_checkout', () => window.setTimeout(update, 0));
      window.jQuery(document.body).on('change', 'select[id^="coleteonline_service_shipping_point-"]', () => window.setTimeout(update, 0));
    }
    if (review) {
      new MutationObserver(() => localizePickupPointControls(review.querySelector('#shipping_method')))
        .observe(review, { childList: true, subtree: true });
      review.addEventListener('change', (event) => {
        if (event.target.matches('input.shipping_method, select[id^="coleteonline_service_shipping_point-"]')) {
          window.setTimeout(update, 0);
        }
      });
    }
  }

  function shippingMethodDetails(row) {
    const input = row.querySelector('input.shipping_method');
    if (!input) return null;
    const metadata = row.querySelector('.coleteonline-method-data');
    const amount = row.querySelector('.amount')?.textContent?.trim() || '';
    const type = input.value.startsWith('local_pickup') ? 'pickup'
      : metadata?.dataset.hasLockers === 'true' ? 'locker' : 'courier';
    const price = amount ? Number(amount.replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.')) : 0;
    if (!Number.isFinite(price)) return null;
    return {
      input, row, type, price,
      amount: amount || 'Gratuit',
      name: row.querySelector('label')?.textContent?.replace(amount, '').replace(/:\s*$/, '').trim() || 'Curier'
    };
  }

  function chooseShippingMethod(method) {
    if (method.input.checked) return;
    method.input.checked = true;
    if (window.jQuery) window.jQuery(method.input).trigger('change');
    else method.input.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function renderShippingChoices(list) {
    if (!list) return;
    const methods = Array.from(list.querySelectorAll(':scope > li'))
      .map(shippingMethodDetails).filter(Boolean);
    const locker = methods.filter((item) => item.type === 'locker').sort((a, b) => a.price - b.price);
    const courier = methods.filter((item) => item.type === 'courier').sort((a, b) => a.price - b.price);
    const chosen = methods.find((item) => item.input.checked);

    /**
     * Pickup is a dedicated choice higher up the form (the "Cum primești
     * comanda?" toggle) — nothing left to pick here, just confirm it.
     */
    if (currentShippingMode === 'pickup') {
      list.classList.add('fr-shipping-simplified');
      let note = list.parentElement.querySelector('.fr-shipping-pickup-note');
      if (!note) {
        note = document.createElement('p');
        note.className = 'fr-shipping-pickup-note';
        list.before(note);
      }
      note.textContent = 'Ridici coletul personal din Florești, Cluj. Te contactăm pentru detalii.';
      list.parentElement?.querySelector('.fr-shipping-choices')?.remove();
      list.parentElement?.querySelector('.fr-shipping-pending-note')?.remove();
      return;
    }
    list.parentElement?.querySelector('.fr-shipping-pickup-note')?.remove();

    /**
     * Before a full address is typed, Colete has nothing to quote yet — the
     * raw list would just show the pickup rate alone, which reads as if
     * "Ridicare" is still selected even though the toggle says "Livrare".
     */
    if (!courier.length && !locker.length) {
      list.classList.add('fr-shipping-simplified');
      let pending = list.parentElement.querySelector('.fr-shipping-pending-note');
      if (!pending) {
        pending = document.createElement('p');
        pending.className = 'fr-shipping-pending-note';
        list.before(pending);
      }
      pending.textContent = 'Taxele de livrare se calculează după ce introduci adresa completă.';
      list.parentElement?.querySelector('.fr-shipping-choices')?.remove();
      return;
    }
    list.parentElement?.querySelector('.fr-shipping-pending-note')?.remove();

    let panel = list.parentElement.querySelector('.fr-shipping-choices');
    if (!panel) {
      panel = document.createElement('div');
      panel.className = 'fr-shipping-choices';
      panel.setAttribute('role', 'group');
      panel.setAttribute('aria-label', 'Modalitate de livrare');
      list.before(panel);
    }
    list.classList.add('fr-shipping-simplified');
    panel.replaceChildren();

    const addChoice = (type, title, note, items, onClick) => {
      if (!items.length) return null;
      const selected = chosen?.type === type;
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'fr-shipping-choice';
      button.dataset.shippingType = type;
      button.setAttribute('aria-pressed', String(selected));
      const copy = document.createElement('span');
      copy.className = 'fr-shipping-choice-copy';
      const name = document.createElement('strong');
      name.textContent = title;
      const detail = document.createElement('small');
      detail.textContent = note;
      copy.append(name, detail);
      const amount = document.createElement('span');
      amount.className = 'fr-shipping-choice-price';
      amount.textContent = type === 'pickup' ? items[0].amount
        : selected && chosen ? chosen.amount : `de la ${items[0].amount}`;
      button.append(copy, amount);
      button.addEventListener('click', onClick);
      panel.append(button);
      return button;
    };

    if (locker.length) {
      const selectedPoint = chosen?.type === 'locker'
        ? chosen.row.querySelector('select[id^="coleteonline_service_shipping_point"]')?.selectedOptions[0]?.textContent?.trim()
        : '';
      addChoice('locker', 'La locker', selectedPoint && selectedPoint !== 'Alege punctul de ridicare'
        ? selectedPoint : 'Alege punctul pe hartă', locker, () => {
        const mapButton = list.querySelector('.coleteonline-open-point-picker');
        if (mapButton) mapButton.click();
        else chooseShippingMethod(locker[0]);
      });
    }

    if (courier.length) {
      addChoice('courier', 'La adresă, prin curier', chosen?.type === 'courier'
        ? `Transportator: ${chosen.name}` : 'Alegi transportatorul mai jos', courier, () => {
        if (chosen?.type !== 'courier') chooseShippingMethod(courier[0]);
      });
      const choices = document.createElement('div');
      choices.className = 'fr-courier-choices';
      choices.setAttribute('role', 'group');
      choices.setAttribute('aria-label', 'Alege transportatorul');
      choices.hidden = chosen?.type !== 'courier';
      courier.forEach((method) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'fr-courier-choice';
        button.setAttribute('aria-pressed', String(method.input.checked));
        const label = document.createElement('span');
        label.textContent = method.name;
        const amount = document.createElement('strong');
        amount.textContent = method.amount;
        button.append(label, amount);
        button.addEventListener('click', () => chooseShippingMethod(method));
        choices.append(button);
      });
      panel.append(choices);
    }
  }

  let shippingModeToggleEl = null;
  let currentShippingMode = 'delivery';
  let lastReferralResult = null;

  /**
   * WC's own "chosen_shipping_methods" session value is unreliable as a
   * pickup/delivery signal server-side: before a full address is entered,
   * local_pickup can be the only available rate and gets auto-selected even
   * though the toggle is on "Livrare" — that stale value made the cod gateway
   * briefly read "Plată la ridicare" for a delivery order. This hidden field
   * mirrors the toggle directly and rides along in WC's existing
   * update_checkout post data, so the server can trust it instead.
   */
  function syncShippingModeField(mode) {
    const form = document.querySelector('form.checkout');
    if (!form) return;
    let field = form.querySelector('#fr_shipping_mode');
    if (!field) {
      field = document.createElement('input');
      field.type = 'hidden';
      field.id = 'fr_shipping_mode';
      field.name = 'fr_shipping_mode';
      form.appendChild(field);
    }
    field.value = mode;
  }

  function availableShippingMethods() {
    const list = document.querySelector('#order_review #shipping_method');
    return list ? Array.from(list.querySelectorAll(':scope > li')).map(shippingMethodDetails).filter(Boolean) : [];
  }

  /**
   * Hides the whole address block for pickup — it isn't needed, and WC's own
   * required-field check is relaxed server-side too (see
   * FR_Checkout::relax_address_fields_for_pickup) so a pickup order can submit
   * with the fields empty, not just hidden.
   */
  function setShippingModeUI(mode) {
    currentShippingMode = mode;
    syncShippingModeField(mode);
    if (shippingModeToggleEl) {
      shippingModeToggleEl.querySelectorAll('button').forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.shippingMode === mode));
      });
    }
    const hide = mode === 'pickup';
    DELIVERY_FIELD_IDS.map((id) => document.getElementById(id)).filter(Boolean).forEach((field) => {
      field.hidden = hide;
      field.querySelectorAll('input, select').forEach((control) => {
        if (hide) {
          if (control.required) control.dataset.frWasRequired = 'true';
          control.required = false;
        } else if (control.dataset.frWasRequired === 'true') {
          control.required = true;
          delete control.dataset.frWasRequired;
        }
      });
    });
    const description = document.querySelector('.fr-checkout-group-description');
    if (description) description.hidden = hide;
    const heading = document.querySelector('.fr-checkout-group-title[data-fr-group="delivery"]');
    if (heading) heading.hidden = hide;
  }

  /**
   * Keeps the real WC shipping radio in sync with the toggle. Delivery rates
   * only exist once Colete has quoted a real address, so picking "Livrare"
   * before that can't select anything yet — this re-runs on every
   * updated_checkout and promotes off the stale pickup default the moment a
   * courier/locker rate actually shows up.
   */
  function reconcileShippingMode() {
    const methods = availableShippingMethods();
    if (!methods.length) return;
    const checked = methods.find((item) => item.input.checked);
    if (currentShippingMode === 'pickup') {
      const pickup = methods.find((item) => item.type === 'pickup');
      if (pickup && checked !== pickup) chooseShippingMethod(pickup);
      return;
    }
    if (checked && checked.type !== 'pickup') return;
    const courier = methods.find((item) => item.type === 'courier' || item.type === 'locker');
    if (courier) chooseShippingMethod(courier);
  }

  function buildShippingModeToggle() {
    const toggle = document.createElement('div');
    toggle.id = 'fr-shipping-mode-toggle';
    toggle.className = 'fr-invoice-switch fr-shipping-mode-switch';
    toggle.setAttribute('role', 'group');
    toggle.setAttribute('aria-label', 'Cum primești comanda');
    [['delivery', 'Livrare'], ['pickup', 'Ridicare']].forEach(([mode, label]) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = label;
      button.dataset.shippingMode = mode;
      button.setAttribute('aria-pressed', String(mode === currentShippingMode));
      button.addEventListener('click', () => {
        if (currentShippingMode === mode) return;
        setShippingModeUI(mode);
        reconcileShippingMode();
        if (window.jQuery) window.jQuery(document.body).trigger('update_checkout');
      });
      toggle.appendChild(button);
    });
    return toggle;
  }

  function initShippingModeToggle() {
    const wrapper = document.querySelector('.fr-purchase--checkout .woocommerce-billing-fields__field-wrapper');
    if (!wrapper) return;
    setShippingModeUI('delivery');
    if (window.jQuery) {
      window.jQuery(document.body).on('updated_checkout', () => window.setTimeout(reconcileShippingMode, 0));
    }
  }

  function initInvoiceType() {
    const select = document.getElementById('smartbill_billing_type');
    if (!select) return;

    const field = document.getElementById('smartbill_billing_type_field');
    const switcher = document.createElement('div');
    switcher.className = 'fr-invoice-switch';
    switcher.setAttribute('role', 'group');
    switcher.setAttribute('aria-label', 'Tipul facturii');

    const choices = [
      { value: 'pf', label: 'Persoană fizică' },
      { value: 'pj', label: 'Firmă' }
    ];
    choices.forEach((choice) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = choice.label;
      button.dataset.invoiceType = choice.value;
      button.addEventListener('click', () => {
        select.value = choice.value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
      });
      switcher.appendChild(button);
    });

    select.classList.add('fr-native-select-hidden');
    select.setAttribute('tabindex', '-1');
    select.setAttribute('aria-hidden', 'true');
    field.appendChild(switcher);

    const companyIds = [
      'billing_company_field',
      'smartbill_billing_company_name_field',
      'smartbill_billing_cif_field',
      'smartbill_billing_nr_reg_com_field'
    ];
    const companyFields = companyIds.map((id) => document.getElementById(id)).filter(Boolean);
    const hasSmartbillCompany = Boolean(document.getElementById('smartbill_billing_company_name_field'));
    function update() {
      const isCompany = select.value === 'pj';
      switcher.querySelectorAll('button').forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.invoiceType === select.value));
      });
      companyFields.forEach((row) => {
        const duplicate = hasSmartbillCompany && row.id === 'billing_company_field';
        row.hidden = !isCompany || duplicate;
        row.querySelectorAll('input').forEach((input) => {
          const required = isCompany && !duplicate && (
            input.id === 'billing_company' ||
            input.id === 'smartbill_billing_company_name' ||
            input.id === 'smartbill_billing_cif'
          );
          input.required = required;
          const label = row.querySelector('label');
          if (!label) return;
          label.querySelector('.optional')?.remove();
          label.querySelector('.required')?.remove();
          if (required) {
            const marker = document.createElement('span');
            marker.className = 'required';
            marker.setAttribute('aria-hidden', 'true');
            marker.textContent = '*';
            label.append(marker);
          } else if (!duplicate) {
            const marker = document.createElement('span');
            marker.className = 'optional';
            marker.textContent = '(opțional)';
            label.append(marker);
          }
        });
      });
    }
    select.addEventListener('change', update);
    update();
  }

  const DELIVERY_FIELD_IDS = ['billing_country_field', 'billing_city_state_field', 'billing_locality_field', 'billing_state_field', 'billing_city_field', 'billing_postcode_field', 'billing_street_field', 'billing_street_number_field', 'billing_address_1_field', 'billing_address_2_field'];

  function arrangeBillingFields() {
    const wrapper = document.querySelector('.fr-purchase--checkout .woocommerce-billing-fields__field-wrapper');
    if (!wrapper) return;
    const groups = [
      ['invoice', 'Cui facem factura?', ['smartbill_billing_type_field', 'billing_company_field', 'smartbill_billing_company_name_field', 'smartbill_billing_cif_field', 'smartbill_billing_nr_reg_com_field']],
      ['contact', 'Date de contact', ['billing_last_name_field', 'billing_first_name_field', 'billing_email_field', 'billing_phone_field']],
      ['shipping_mode', 'Cum primești comanda?', []],
      ['delivery', 'Adresa ta', DELIVERY_FIELD_IDS]
    ];
    const desired = [];
    groups.forEach(([key, title, ids]) => {
      // Un grup cu o listă de câmpuri declarată, dar fără niciunul prezent în
      // DOM (ex. "Cui facem factura?" când secțiunea de firmă e ascunsă),
      // rămâne doar un titlu gol — îl omitem complet.
      if (ids.length && !ids.some((id) => document.getElementById(id))) {
        const staleHeading = wrapper.querySelector(`.fr-checkout-group-title[data-fr-group="${key}"]`);
        if (staleHeading) staleHeading.remove();
        return;
      }
      let heading = wrapper.querySelector(`.fr-checkout-group-title[data-fr-group="${key}"]`);
      if (!heading) {
        heading = document.createElement('h4');
        heading.className = 'fr-checkout-group-title';
        heading.dataset.frGroup = key;
        heading.textContent = title;
      }
      desired.push(heading);
      if (key === 'shipping_mode') {
        if (!shippingModeToggleEl) shippingModeToggleEl = buildShippingModeToggle();
        desired.push(shippingModeToggleEl);
      }
      if (key === 'delivery') {
        let description = wrapper.querySelector('.fr-checkout-group-description');
        if (!description) {
          description = document.createElement('p');
          description.className = 'fr-checkout-group-description';
          description.textContent = 'Pentru factură și livrarea prin curier. Dacă alegi locker, selectezi punctul de ridicare pe hartă.';
        }
        desired.push(description);
      }
      ids.forEach((id) => {
        const field = document.getElementById(id);
        if (field && field.parentElement === wrapper) desired.push(field);
      });
    });
    const current = Array.from(wrapper.children);
    const remaining = current.filter((node) => !desired.includes(node));
    const ordered = desired.concat(remaining);
    if (current.length === ordered.length && current.every((node, index) => node === ordered[index])) return;
    ordered.forEach((node) => wrapper.appendChild(node));
  }

  function initBillingLayout() {
    arrangeBillingFields();
    const wrapper = document.querySelector('.fr-purchase--checkout .woocommerce-billing-fields__field-wrapper');
    if (!wrapper) return;
    const observer = new MutationObserver(() => arrangeBillingFields());
    observer.observe(wrapper, { childList: true });
  }

  function localizeCheckoutLabels() {
    const labels = {
      billing_phone: 'Telefon',
      billing_country: 'Țară',
      billing_state: 'Județ',
      billing_city: 'Localitate',
      billing_postcode: 'Cod poștal',
      billing_address_2: 'Bloc, scară, apartament'
    };
    Object.entries(labels).forEach(([id, text]) => {
      const label = document.querySelector(`.fr-purchase--checkout label[for="${id}"]`);
      if (label?.firstChild?.nodeType === Node.TEXT_NODE) label.firstChild.textContent = text;
    });
    document.querySelectorAll('.fr-purchase--checkout .optional').forEach((optional) => {
      optional.textContent = '(opțional)';
    });
	const phoneLabel = document.querySelector('#billing_phone_field label');
	if (phoneLabel && document.getElementById('billing_phone')?.getAttribute('aria-required') === 'true') {
	  phoneLabel.querySelector('.optional')?.remove();
	  if (!phoneLabel.querySelector('.required')) {
	    const marker = document.createElement('span');
	    marker.className = 'required';
	    marker.setAttribute('aria-hidden', 'true');
	    marker.textContent = '*';
	    phoneLabel.append(marker);
	  }
	}
    const placeholders = {
      billing_city_state: 'Alege localitatea și județul',
      billing_street: 'Alege strada',
      billing_street_number: 'Număr',
      billing_address_2: 'Bloc, scară, apartament (opțional)',
      order_comments: 'O observație sau un mesaj pentru noi, dacă vrei să lași unul'
    };
    Object.entries(placeholders).forEach(([id, text]) => {
      const input = document.getElementById(id);
      if (!input) return;
      if (input.hasAttribute('placeholder')) input.placeholder = text;
      if (input.hasAttribute('data-placeholder')) input.dataset.placeholder = text;
      const rendered = input.closest('.form-row')?.querySelector('.select2-selection__placeholder');
      if (rendered) rendered.textContent = text;
    });
  }

  function initReferralValidation() {
    const input = document.getElementById('fr_referral_code');
    const field = document.getElementById('fr-referral-field');
    if (!input || !field || !window.frCheckout) return;

    let status = field.querySelector('#fr-referral-status');
    if (!status) {
      status = document.createElement('p');
      status.id = 'fr-referral-status';
      status.className = 'fr-referral-status';
      status.setAttribute('role', 'status');
      status.setAttribute('aria-live', 'polite');
      field.appendChild(status);
      input.setAttribute('aria-describedby', [input.getAttribute('aria-describedby'), status.id].filter(Boolean).join(' '));
    }

    /**
     * initReferralValidation() re-runs on every updated_checkout, but the
     * input/button nodes don't always get replaced by the fragment update —
     * rebinding listeners on the same persisted node doubled every click and
     * every status message. Bind once per real element, keyed on the node
     * itself; a genuinely fresh node (if the fragment did replace it) won't
     * carry the flag and rebinds normally.
     */
    if (input.dataset.frReferralBound) {
      const normalized = input.value.replace(/[^a-z0-9]/gi, '').toUpperCase();
      if (normalized && lastReferralResult && lastReferralResult.code === normalized) {
        status.textContent = lastReferralResult.message;
        status.dataset.state = lastReferralResult.state;
      }
      return;
    }
    input.dataset.frReferralBound = '1';

    let request;
    let serial = 0;

    function show(message, state) {
      status.textContent = message;
      status.dataset.state = state;
      if (state === 'invalid') input.setAttribute('aria-invalid', 'true');
      else input.removeAttribute('aria-invalid');
    }

    /**
     * verify() ends by triggering update_checkout to refresh the total, which
     * re-renders this whole block (it lives in the order-review fragment) and
     * re-runs initReferralValidation() — remembering the last checked code
     * (module-scope, survives the re-render) stops that from re-verifying and
     * re-triggering update_checkout forever.
     */
    async function verify() {
      const code = input.value.replace(/[^a-z0-9]/gi, '').toUpperCase();
      const current = ++serial;
      if (request) request.abort();
      if (!code) {
        show('', 'idle');
        lastReferralResult = { code: '', message: '', state: 'idle' };
        if (window.jQuery) window.jQuery(document.body).trigger('update_checkout');
        return;
      }
      if (code.length > 20) {
        const message = 'Codul este prea lung. Verifică-l și încearcă din nou.';
        show(message, 'invalid');
        lastReferralResult = { code, message, state: 'invalid' };
        if (window.jQuery) window.jQuery(document.body).trigger('update_checkout');
        return;
      }

      show('Verificăm codul…', 'pending');
      request = new AbortController();
      const body = new URLSearchParams({
        action: 'fr_check_referral_code',
        nonce: window.frCheckout.referralNonce,
        code,
        email: document.getElementById('billing_email')?.value || '',
        phone: document.getElementById('billing_phone')?.value || ''
      });
      try {
        const response = await fetch(window.frCheckout.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body,
          signal: request.signal
        });
        const result = await response.json();
        if (current !== serial) return;
        if (!response.ok || !result.success) throw new Error('Referral check failed');
        const data = result.data;
        let message;
        let state;
        if (!data.valid) {
          message = 'Codul nu a fost găsit. Verifică-l înainte de a plasa comanda.';
          state = 'invalid';
        } else if (data.bonus_eligible) {
          message = 'Felicitări! Codul e valid — 5 lei extra merg în Pușculiță din această comandă.';
          state = 'valid';
        } else if (data.self_referral) {
          message = 'Acesta e codul tău propriu. Bonusul se acordă altor familii, la prima lor comandă — pentru jocurile tale tot punem 5 lei per joc în Pușculiță.';
          state = 'invalid';
        } else if (data.repeat_buyer) {
          message = 'Ai mai folosit acest cod la o comandă anterioară — bonusul e valabil doar pentru prima comandă. Punem oricum 5 lei per joc în Pușculiță.';
          state = 'invalid';
        } else {
          message = 'Cod valid. Completează email și telefon ca să confirmăm bonusul de 5 lei per joc.';
          state = 'valid';
        }
        show(message, state);
        lastReferralResult = { code, message, state };
        if (window.jQuery) window.jQuery(document.body).trigger('update_checkout');
      } catch (error) {
        if (error.name !== 'AbortError' && current === serial) {
          show('Nu am putut verifica acum. Vom verifica din nou la plasarea comenzii.', 'unavailable');
        }
      }
    }

    /**
     * Only the "Aplică" button checks the code — no live/auto verification
     * while typing, so a half-typed code never fires a request or an update.
     */
    const applyButton = document.getElementById('fr_referral_apply');
    if (applyButton) {
      applyButton.addEventListener('click', verify);
    }
    input.addEventListener('input', () => {
      if (status.dataset.state && status.dataset.state !== 'idle') show('', 'idle');
    });
    const normalized = input.value.replace(/[^a-z0-9]/gi, '').toUpperCase();
    if (normalized && lastReferralResult && lastReferralResult.code === normalized) {
      show(lastReferralResult.message, lastReferralResult.state);
    }
  }

  function initReferralPreview() {
    if (!window.jQuery) return;
    let timer;
    ['billing_email', 'billing_phone'].forEach((id) => {
      const input = document.getElementById(id);
      if (!input) return;
      input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = window.setTimeout(() => window.jQuery(document.body).trigger('update_checkout'), 650);
      });
      input.addEventListener('change', () => {
        clearTimeout(timer);
        window.jQuery(document.body).trigger('update_checkout');
      });
    });
  }

  function initCheckoutQuantity() {
    const review = document.getElementById('order_review');
    if (!review || !window.frCheckout) return;
    let updating = false;

    function feedback(message, isError = false) {
      const container = document.querySelector('#payment .place-order');
      if (!container) return;
      let status = container.querySelector('.fr-checkout-feedback');
      if (!status) {
        status = document.createElement('p');
        status.className = 'fr-checkout-feedback';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        container.prepend(status);
      }
      status.textContent = message;
      status.classList.toggle('fr-checkout-feedback--error', isError);
      status.hidden = !message;
    }

    async function update(input, quantity) {
      if (updating) return;
      const previous = Number(input.defaultValue);
      const maximum = input.max ? Number(input.max) : Infinity;
      if (!Number.isInteger(quantity) || quantity < 1 || quantity > maximum) {
        input.value = String(previous);
        return;
      }
      if (quantity === previous) return;
      updating = true;
      input.value = String(quantity);
      const placeOrder = review.querySelector('#place_order');
      const placeOrderWasEnabled = placeOrder && !placeOrder.disabled;
      feedback('Actualizăm cantitatea și totalul comenzii…');
      review.querySelectorAll('.fr-checkout-quantity button, .fr-checkout-quantity input')
        .forEach((control) => { control.disabled = true; });
      if (placeOrderWasEnabled) placeOrder.disabled = true;
      const body = new URLSearchParams({
        action: 'fr_checkout_quantity',
        nonce: window.frCheckout.quantityNonce,
        cart_key: input.dataset.cartKey,
        quantity: String(quantity)
      });
      try {
        const response = await fetch(window.frCheckout.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.data?.message || 'Nu am putut schimba cantitatea.');
        updating = false;
        input.defaultValue = String(quantity);
        review.querySelectorAll('.fr-checkout-quantity button, .fr-checkout-quantity input')
          .forEach((control) => { control.disabled = false; });
        const decrease = input.parentElement.querySelector('[data-step="-1"]');
        const increase = input.parentElement.querySelector('[data-step="1"]');
        if (decrease) decrease.disabled = quantity <= 1;
        if (increase) increase.disabled = quantity >= maximum;
        if (placeOrderWasEnabled && placeOrder.isConnected) placeOrder.disabled = false;
        feedback('');
        if (window.jQuery) {
          window.jQuery(document.body).trigger('update_checkout');
        } else {
          window.location.reload();
        }
      } catch (error) {
        updating = false;
        input.value = String(previous);
        review.querySelectorAll('.fr-checkout-quantity button, .fr-checkout-quantity input')
          .forEach((control) => { control.disabled = false; });
        if (placeOrderWasEnabled && placeOrder.isConnected) placeOrder.disabled = false;
        feedback(`${error.message} Încearcă din nou.`, true);
      }
    }

    review.addEventListener('click', (event) => {
      const button = event.target.closest('.fr-quantity-step');
      if (!button || updating) return;
      const input = button.parentElement.querySelector('.fr-quantity-input');
      update(input, Number(input.value) + Number(button.dataset.step));
    });
    review.addEventListener('change', (event) => {
      if (event.target.matches('.fr-quantity-input')) update(event.target, Number(event.target.value));
    });
  }

  function initCheckoutErrors() {
    if (!window.jQuery) return;
    window.jQuery(document.body).on('checkout_error', () => {
      window.setTimeout(() => {
        const notice = document.querySelector('.woocommerce-NoticeGroup-checkout .woocommerce-error li, .woocommerce-NoticeGroup-checkout .woocommerce-error');
        const container = document.querySelector('#payment .place-order');
        if (!notice || !container) return;
        let status = container.querySelector('.fr-checkout-feedback');
        if (!status) {
          status = document.createElement('p');
          status.className = 'fr-checkout-feedback';
          status.setAttribute('role', 'alert');
          container.prepend(status);
        }
        status.textContent = notice.textContent.trim();
        status.classList.add('fr-checkout-feedback--error');
        status.hidden = false;
        const group = document.querySelector('.woocommerce-NoticeGroup-checkout');
        if (group) group.style.display = 'none';
      }, 0);
    });
  }

  /**
   * Pickup only ever pays cash on delivery — a one-option radio list reads as
   * a pointless choice. Swap it for a plain line once cod is the only
   * gateway on screen (restrict_payment_by_shipping already guarantees that
   * only happens for pickup); the real cod radio stays in the DOM, just
   * visually hidden, so it keeps submitting correctly.
   */
  /**
   * Cash only makes sense for local pickup, card (NETOPIA) only for courier
   * delivery — no bank transfer in the real setup. Stays purely visual (see
   * class-fr-checkout.php for why a server-side gateway filter broke real
   * shipping quotes). Reads whichever shipping radio is actually checked
   * right now, so it never forces a default before the customer has chosen
   * anything.
   */
  function initPickupPaymentMessage() {
    const render = () => {
      const list = document.querySelector('#payment .payment_methods');
      if (!list) return;
      /**
       * Driven by the "Cum primești comanda?" toggle, not the raw checked
       * radio — before an address is typed, pickup is the only rate WC has
       * to show regardless of what the toggle says "Livrare" is selected,
       * reading the radio here would flash "Plătești cash la ridicare" under
       * a toggle that still says "Livrare".
       *
       * "Sole mode" covers pickup only — cash at pickup is the only option
       * there, so the radio is hidden entirely (kept checked, so it still
       * submits) and a plain sentence takes its place. Delivery now has a
       * real choice (Ramburs / Transfer bancar), so both stay visible and
       * selectable — no forced sole method there anymore.
       */
      const isPickup = currentShippingMode === 'pickup';
      const soleMethod = isPickup ? 'cod' : null;
      let note = list.parentElement.querySelector('.fr-payment-pickup-note');

      // Pickup: hide the whole list, a sentence takes its place. Delivery:
      // both Ramburs (cod) and Transfer bancar (bacs) stay visible and
      // selectable — nothing else is ever enabled alongside them (card
      // arrives, and this whole function stops mattering, once Netopia is
      // configured for real).
      list.querySelectorAll('.wc_payment_method').forEach((li) => {
        li.hidden = !!soleMethod;
      });

      if (soleMethod) {
        const radio = list.querySelector(`.payment_method_${soleMethod} input[type="radio"]`);
        if (radio && !radio.checked) radio.checked = true;
      } else {
        const visible = Array.from(list.querySelectorAll('.wc_payment_method')).filter((li) => !li.hidden);
        const alreadyChecked = visible.some((li) => li.querySelector('input[type="radio"]')?.checked);
        if (!alreadyChecked && visible[0]) {
          const radio = visible[0].querySelector('input[type="radio"]');
          if (radio) radio.checked = true;
        }
      }

      if (soleMethod) {
        if (!note) {
          note = document.createElement('p');
          note.className = 'fr-payment-pickup-note';
          list.insertAdjacentElement('afterend', note);
        }
        note.textContent = 'Plătești cash la ridicare.';
      } else if (note) {
        note.remove();
      }
    };
    render();
    if (window.jQuery) {
      window.jQuery(document.body).on('updated_checkout', () => window.setTimeout(render, 0));
      window.jQuery(document.body).on('change', '#shipping_method input', () => window.setTimeout(render, 0));
    }
  }

  function init() {
    initBillingLayout();
    initShippingModeToggle();
    initInvoiceType();
    localizeCheckoutLabels();
    initReferralValidation();
    initReferralPreview();
    initShippingLabels();
    initCheckoutQuantity();
    initCheckoutErrors();
    initPickupPaymentMessage();
    if (window.jQuery) window.jQuery(document.body).on('updated_checkout', () => { localizeCheckoutLabels(); initReferralValidation(); });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
