(function () {
    'use strict';

    var CHECKOUT_KEY = 'wepago_checkout_v1';
    var AVATAR_COLORS = ['#7cb8f5', '#b026ff', '#5fd1a0', '#ffb84d', '#ff7a90', '#8f9bff'];
    var SENSITIVE_FIELDS = ['cardNumber', 'cardExp', 'cardCvv'];

    var cartApi = window.WePagoCart || null;
    var content = document.getElementById('ckContent');
    var emptyState = document.getElementById('ckEmpty');
    var itemsList = document.getElementById('ckItems');
    var countLabel = document.getElementById('ckCount');
    var totalLabel = document.getElementById('ckTotal');
    var badge = document.getElementById('ckCartBadge');
    var payButton = document.getElementById('ckPayButton');
    var errorBox = document.getElementById('ckError');
    var picker = document.getElementById('ckPicker');
    var chosen = document.getElementById('ckChosen');
    var changeButton = document.getElementById('ckChange');
    var selectedLogo = document.getElementById('ckSelectedLogo');
    var selectedName = document.getElementById('ckSelectedName');
    var selectedSub = document.getElementById('ckSelectedSub');
    var methodInputs = document.querySelectorAll('input[name="paymentMethod"]');
    var forms = document.querySelectorAll('.ck-form');

    var items = cartApi ? cartApi.load() : [];
    var currentMethod = '';
    var currentLabel = '';

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
        });
    }

    function formatMoney(amount) {
        return '$' + new Intl.NumberFormat('es-CO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(Number(amount) || 0);
    }

    function initials(name) {
        var words = String(name || '').trim().split(/\s+/).filter(Boolean);
        return (words.slice(0, 2).map(function (word) { return word.charAt(0); }).join('') || 'WP').toUpperCase();
    }

    /* ---------- Validación ---------- */

    function luhn(number) {
        var sum = 0;
        var flip = false;

        for (var i = number.length - 1; i >= 0; i--) {
            var digit = Number(number.charAt(i));
            if (flip) {
                digit *= 2;
                if (digit > 9) { digit -= 9; }
            }
            sum += digit;
            flip = !flip;
        }

        return sum % 10 === 0;
    }

    var validators = {
        select: function (v) { return v !== '' ? '' : 'Selecciona una opción.'; },
        email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) ? '' : 'Ingresa un correo válido.'; },
        phone: function (v) { return /^3\d{9}$/.test(v.replace(/\D/g, '')) ? '' : 'Ingresa un celular de 10 dígitos.'; },
        doc: function (v) { return /^[A-Za-z0-9]{5,15}$/.test(v) ? '' : 'Ingresa un documento válido.'; },
        name: function (v) { return v.trim().length >= 3 ? '' : 'Ingresa el nombre completo.'; },
        card: function (v) {
            var digits = v.replace(/\D/g, '');
            return digits.length >= 13 && digits.length <= 19 && luhn(digits) ? '' : 'Número de tarjeta inválido.';
        },
        exp: function (v) {
            var match = /^(\d{2})\/(\d{2})$/.exec(v);
            if (!match) { return 'Usa el formato MM/AA.'; }
            var month = Number(match[1]);
            var year = 2000 + Number(match[2]);
            var now = new Date();
            if (month < 1 || month > 12) { return 'Mes inválido.'; }
            if (year < now.getFullYear() || (year === now.getFullYear() && month < now.getMonth() + 1)) { return 'Tarjeta vencida.'; }
            return '';
        },
        cvv: function (v) { return /^\d{3,4}$/.test(v) ? '' : 'CVV inválido.'; }
    };

    function fieldMessage(control) {
        var check = validators[control.getAttribute('data-validate')];
        return check ? check(control.value.trim()) : '';
    }

    function showFieldError(control, message) {
        var holder = control.closest('.ck-field');
        var errorNode = holder ? holder.querySelector('.ck-field-error') : null;
        var trigger = holder ? holder.querySelector('.ck-combobox-trigger') : null;
        var invalid = message !== '';

        control.classList.toggle('is-invalid', invalid);
        control.setAttribute('aria-invalid', invalid ? 'true' : 'false');

        if (trigger) {
            trigger.classList.toggle('is-invalid', invalid);
        }

        if (errorNode) {
            errorNode.textContent = message;
            errorNode.hidden = !invalid;
        }
    }

    function activeForm() {
        return document.querySelector('.ck-form[data-form="' + currentMethod + '"]');
    }

    function isFormValid() {
        var form = activeForm();

        if (!form) {
            return false;
        }

        return Array.prototype.every.call(form.querySelectorAll('[data-validate]'), function (control) {
            return fieldMessage(control) === '';
        });
    }

    function refreshPayButton() {
        payButton.disabled = !(currentMethod && isFormValid());
    }

    /* ---------- Máscaras ---------- */

    function maskValue(control) {
        var kind = control.getAttribute('data-validate');

        if (kind === 'card') {
            control.value = control.value.replace(/\D/g, '').slice(0, 19).replace(/(\d{4})(?=\d)/g, '$1 ');
        } else if (kind === 'exp') {
            var digits = control.value.replace(/\D/g, '').slice(0, 4);
            control.value = digits.length > 2 ? digits.slice(0, 2) + '/' + digits.slice(2) : digits;
        } else if (kind === 'cvv' || kind === 'doc') {
            control.value = control.value.replace(kind === 'doc' ? /[^A-Za-z0-9]/g : /\D/g, '');
        } else if (kind === 'phone') {
            var phone = control.value.replace(/\D/g, '').slice(0, 10);
            control.value = phone.replace(/^(\d{3})(\d{0,3})(\d{0,4}).*/, function (m, a, b, c) {
                return [a, b, c].filter(Boolean).join(' ');
            });
        }
    }

    /* ---------- Método elegido ---------- */

    function showMethod(input) {
        var item = input.closest('.ck-method-item');
        var logo = item ? item.querySelector('.ck-method-logo') : null;

        currentMethod = input.value;
        currentLabel = input.getAttribute('data-short') || input.getAttribute('data-name') || '';

        selectedLogo.innerHTML = '';
        if (logo) {
            selectedLogo.appendChild(logo.cloneNode(true));
        }
        selectedName.textContent = input.getAttribute('data-name') || '';
        selectedSub.textContent = input.getAttribute('data-sub') || '';

        Array.prototype.forEach.call(forms, function (form) {
            form.hidden = form.getAttribute('data-form') !== currentMethod;
        });

        picker.hidden = true;
        chosen.hidden = false;
        payButton.textContent = 'Pagar con ' + currentLabel + ' →';
        showError('');
        refreshPayButton();

        if (window.matchMedia('(max-width: 860px)').matches) {
            content.scrollIntoView({ block: 'start', behavior: 'smooth' });
        }
    }

    function resetMethod() {
        Array.prototype.forEach.call(methodInputs, function (input) { input.checked = false; });
        Array.prototype.forEach.call(document.querySelectorAll('.ck-input.is-invalid'), function (control) {
            showFieldError(control, '');
        });

        currentMethod = '';
        chosen.hidden = true;
        picker.hidden = false;
        payButton.textContent = 'Pagar ahora';
        payButton.disabled = true;
        showError('');
    }

    /* ---------- Resumen y pago ---------- */

    function renderSummary() {
        itemsList.innerHTML = items.map(function (item, index) {
            return [
                '<li class="ck-item">',
                '  <span class="ck-item-avatar" style="background:' + AVATAR_COLORS[index % AVATAR_COLORS.length] + '">' + escapeHtml(initials(item.merchantName)) + '</span>',
                '  <div class="ck-item-info">',
                '    <span class="ck-item-name">' + escapeHtml(item.merchantName) + '</span>',
                item.reference ? '    <span class="ck-item-ref">Ref. ' + escapeHtml(item.reference) + '</span>' : '',
                '  </div>',
                '  <span class="ck-item-amount">' + formatMoney(item.amount) + '</span>',
                '</li>'
            ].join('');
        }).join('');

        countLabel.textContent = String(items.length);
        totalLabel.textContent = formatMoney(cartApi.getTotal(items));
        badge.textContent = String(items.length);
        badge.hidden = items.length === 0;
    }

    function showError(message) {
        errorBox.textContent = message;
        errorBox.hidden = !message;
    }

    function collectPayer() {
        var payer = {};
        var form = activeForm();

        Array.prototype.forEach.call(form.querySelectorAll('[data-validate]'), function (control) {
            if (SENSITIVE_FIELDS.indexOf(control.name) !== -1) {
                return;
            }
            payer[control.name] = control.value.trim();
        });

        var cardNumber = form.querySelector('[name="cardNumber"]');
        if (cardNumber) {
            payer.cardLast4 = cardNumber.value.replace(/\D/g, '').slice(-4);
        }

        return payer;
    }

    function orderItems() {
        return items.map(function (item) {
            return {
                merchantId: item.merchantId,
                merchantName: item.merchantName,
                reference: item.reference,
                amount: item.amount
            };
        });
    }

    function saveCheckoutOrder(extra) {
        var order = {
            method: currentMethod,
            methodLabel: selectedName.textContent,
            total: cartApi.getTotal(items),
            createdAt: Date.now(),
            items: orderItems()
        };

        for (var key in extra) {
            if (Object.prototype.hasOwnProperty.call(extra, key)) {
                order[key] = extra[key];
            }
        }

        try {
            window.sessionStorage.setItem(CHECKOUT_KEY, JSON.stringify(order));
        } catch (error) {
            // El pedido ya se envió a la pasarela; si no se puede guardar en sessionStorage
            // (modo privado, cuota llena) seguimos igual con la redirección/flujo.
        }

        return order;
    }

    function submitPse(payer) {
        payButton.disabled = true;
        payButton.textContent = 'Conectando con PSE...';

        fetch('api/pse_charge.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                amount: cartApi.getTotal(items),
                bankCode: payer.bank,
                personType: payer.personType,
                docType: payer.docType,
                docNumber: payer.docNumber,
                fullName: payer.fullName,
                email: payer.email,
                phone: payer.phone
            })
        }).then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (data) {
                return { ok: response.ok, data: data };
            });
        }).then(function (result) {
            if (!result.ok || !result.data || !result.data.success || !result.data.redirectUrl) {
                throw new Error((result.data && result.data.message) || 'No pudimos iniciar tu pago PSE.');
            }

            saveCheckoutOrder({ payer: payer, reference: result.data.reference });
            window.location.href = result.data.redirectUrl;
        }).catch(function (error) {
            showError(error.message || 'No pudimos iniciar tu pago PSE. Intenta de nuevo.');
            payButton.disabled = false;
            payButton.textContent = 'Pagar con ' + currentLabel + ' →';
        });
    }

    function pay() {
        if (!currentMethod || items.length === 0) {
            return;
        }

        var form = activeForm();
        var firstInvalid = null;

        Array.prototype.forEach.call(form.querySelectorAll('[data-validate]'), function (control) {
            var message = fieldMessage(control);
            showFieldError(control, message);
            if (message && !firstInvalid) { firstInvalid = control; }
        });

        if (firstInvalid) {
            firstInvalid.focus();
            return;
        }

        showError('');

        var payer = collectPayer();

        if (currentMethod === 'pse') {
            submitPse(payer);
            return;
        }

        saveCheckoutOrder({ payer: payer });

        payButton.disabled = true;
        payButton.textContent = 'Procesando...';

        // TODO: integrar bancolombia / daviplata / nequi / tarjeta con su pasarela respectiva.
        window.dispatchEvent(new CustomEvent('wepago:checkout-submit', { detail: { method: currentMethod, payer: payer } }));
    }

    /* ---------- Combobox (buscador de banco) ---------- */

    function normalizeText(value) {
        return String(value).normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }

    function initCombobox(root) {
        var trigger = root.querySelector('.ck-combobox-trigger');
        var hidden = root.querySelector('input[type="hidden"]');
        var panel = root.querySelector('.ck-combobox-panel');
        var search = root.querySelector('.ck-combobox-search');
        var options = Array.prototype.slice.call(root.querySelectorAll('.ck-combobox-option'));
        var empty = root.querySelector('.ck-combobox-empty');
        var valueLabel = root.querySelector('.ck-combobox-value');
        var activeIndex = -1;
        var touched = false;

        function visibleOptions() {
            return options.filter(function (opt) { return !opt.hidden; });
        }

        function setActive(index) {
            var visible = visibleOptions();

            options.forEach(function (opt) { opt.classList.remove('is-active'); });
            activeIndex = index;

            if (visible[index]) {
                visible[index].classList.add('is-active');
                visible[index].scrollIntoView({ block: 'nearest' });
            }
        }

        function filter() {
            var query = normalizeText(search.value.trim());
            var anyVisible = false;

            options.forEach(function (opt) {
                var match = query === '' || normalizeText(opt.textContent).indexOf(query) !== -1;
                opt.hidden = !match;
                if (match) { anyVisible = true; }
            });

            empty.hidden = anyVisible;
            setActive(anyVisible ? 0 : -1);
        }

        function isOpen() { return !panel.hidden; }

        function onOutsideClick(event) {
            if (!root.contains(event.target)) { close(); }
        }

        function open() {
            root.setAttribute('data-open', '');
            trigger.setAttribute('aria-expanded', 'true');
            panel.hidden = false;
            search.value = '';
            filter();
            touched = true;
            window.setTimeout(function () { search.focus(); }, 0);
            document.addEventListener('click', onOutsideClick, true);
        }

        function close(opts) {
            opts = opts || {};
            root.removeAttribute('data-open');
            trigger.setAttribute('aria-expanded', 'false');
            panel.hidden = true;
            document.removeEventListener('click', onOutsideClick, true);

            if (opts.focusTrigger) { trigger.focus(); }
            if (touched) { showFieldError(hidden, fieldMessage(hidden)); }
        }

        function select(option) {
            hidden.value = option.getAttribute('data-value');
            valueLabel.textContent = option.textContent;
            valueLabel.classList.remove('is-placeholder');
            options.forEach(function (opt) { opt.classList.toggle('is-selected', opt === option); });
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
            close({ focusTrigger: true });
        }

        trigger.addEventListener('click', function () {
            if (isOpen()) { close({ focusTrigger: true }); } else { open(); }
        });

        trigger.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                if (!isOpen()) { open(); }
            } else if (event.key === 'Escape' && isOpen()) {
                close({ focusTrigger: true });
            }
        });

        search.addEventListener('input', filter);

        search.addEventListener('keydown', function (event) {
            var visible = visibleOptions();

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setActive(Math.min(activeIndex + 1, visible.length - 1));
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                setActive(Math.max(activeIndex - 1, 0));
            } else if (event.key === 'Enter') {
                event.preventDefault();
                if (visible[activeIndex]) { select(visible[activeIndex]); }
            } else if (event.key === 'Escape') {
                close({ focusTrigger: true });
            }
        });

        options.forEach(function (opt) {
            opt.addEventListener('click', function () { select(opt); });
        });
    }

    if (!cartApi || items.length === 0) {
        emptyState.hidden = false;
        return;
    }

    content.hidden = false;
    renderSummary();

    Array.prototype.forEach.call(document.querySelectorAll('[data-combobox]'), initCombobox);

    Array.prototype.forEach.call(methodInputs, function (input) {
        input.addEventListener('change', function () { showMethod(input); });
    });

    Array.prototype.forEach.call(forms, function (form) {
        form.addEventListener('input', function (event) {
            var control = event.target;
            if (!control.hasAttribute('data-validate')) { return; }

            maskValue(control);
            if (control.classList.contains('is-invalid')) {
                showFieldError(control, fieldMessage(control));
            }
            refreshPayButton();
        });

        form.addEventListener('change', function (event) {
            if (event.target.hasAttribute('data-validate')) { refreshPayButton(); }
        });

        form.addEventListener('focusout', function (event) {
            var control = event.target;
            if (control.hasAttribute('data-validate') && control.value !== '') {
                showFieldError(control, fieldMessage(control));
            }
        });
    });

    changeButton.addEventListener('click', resetMethod);
    payButton.addEventListener('click', pay);
}());
