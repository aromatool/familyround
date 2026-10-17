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
    const pickup = methods.filter((item) => item.type === 'pickup');
    const chosen = methods.find((item) => item.input.checked);
    if (!courier.length && !locker.length) {
      list.classList.remove('fr-shipping-simplified');
      list.parentElement?.querySelector('.fr-shipping-choices')?.remove();
      return;
    }

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

    if (pickup.length) {
      addChoice('pickup', 'Ridicare personală', 'Din Florești, Cluj', pickup,
        () => chooseShippingMethod(pickup[0]));
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

  function arrangeBillingFields() {
    const wrapper = document.querySelector('.fr-purchase--checkout .woocommerce-billing-fields__field-wrapper');
    if (!wrapper) return;
    const groups = [
      ['invoice', 'Cui facem factura?', ['smartbill_billing_type_field', 'billing_company_field', 'smartbill_billing_company_name_field', 'smartbill_billing_cif_field', 'smartbill_billing_nr_reg_com_field']],
      ['contact', 'Date de contact', ['billing_first_name_field', 'billing_last_name_field', 'billing_email_field', 'billing_phone_field']],
      ['delivery', 'Adresa ta', ['billing_country_field', 'billing_city_state_field', 'billing_locality_field', 'billing_state_field', 'billing_city_field', 'billing_postcode_field', 'billing_street_field', 'billing_street_number_field', 'billing_address_1_field', 'billing_address_2_field']]
    ];
    const desired = [];
    groups.forEach(([key, title, ids]) => {
      let heading = wrapper.querySelector(`.fr-checkout-group-title[data-fr-group="${key}"]`);
      if (!heading) {
        heading = document.createElement('h4');
        heading.className = 'fr-checkout-group-title';
        heading.dataset.frGroup = key;
        heading.textContent = title;
      }
      desired.push(heading);
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
      order_comments: 'De exemplu: Cu drag, Mihai'
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
    const field = document.getElementById('fr_referral_code_field');
    if (!input || !field || !window.frCheckout) return;

    const status = document.createElement('p');
    status.id = 'fr-referral-status';
    status.className = 'fr-referral-status';
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    field.appendChild(status);
    input.setAttribute('aria-describedby', [input.getAttribute('aria-describedby'), status.id].filter(Boolean).join(' '));

    let timer;
    let request;
    let serial = 0;

    function show(message, state) {
      status.textContent = message;
      status.dataset.state = state;
      if (state === 'invalid') input.setAttribute('aria-invalid', 'true');
      else input.removeAttribute('aria-invalid');
    }

    async function verify() {
      const code = input.value.replace(/[^a-z0-9]/gi, '').toUpperCase();
      const current = ++serial;
      if (request) request.abort();
      if (!code) {
        show('', 'idle');
        if (window.jQuery) window.jQuery(document.body).trigger('update_checkout');
        return;
      }
      if (code.length > 20) {
        show('Codul este prea lung. Verifică-l și încearcă din nou.', 'invalid');
        if (window.jQuery) window.jQuery(document.body).trigger('update_checkout');
        return;
      }

      show('Verificăm codul…', 'pending');
      request = new AbortController();
      const body = new URLSearchParams({
        action: 'fr_check_referral_code',
        nonce: window.frCheckout.referralNonce,
        code
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
        show(
          result.data.valid
            ? 'Cod găsit. Bonusul de 5 lei per joc este pentru prima ta comandă.'
            : 'Codul nu a fost găsit. Verifică-l înainte de a plasa comanda.',
          result.data.valid ? 'valid' : 'invalid'
        );
        if (window.jQuery) window.jQuery(document.body).trigger('update_checkout');
      } catch (error) {
        if (error.name !== 'AbortError' && current === serial) {
          show('Nu am putut verifica acum. Vom verifica din nou la plasarea comenzii.', 'unavailable');
        }
      }
    }

    input.addEventListener('input', () => {
      clearTimeout(timer);
      if (request) request.abort();
      show('', 'idle');
      timer = setTimeout(verify, 450);
    });
    input.addEventListener('blur', () => {
      clearTimeout(timer);
      verify();
    });
    if (input.value.trim()) verify();
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
      }, 0);
    });
  }

  function init() {
    initBillingLayout();
    initInvoiceType();
    localizeCheckoutLabels();
    initReferralValidation();
    initReferralPreview();
    initShippingLabels();
    initCheckoutQuantity();
    initCheckoutErrors();
    if (window.jQuery) window.jQuery(document.body).on('updated_checkout', localizeCheckoutLabels);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
