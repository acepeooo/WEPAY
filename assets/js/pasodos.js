(function () {
            if (window.wptg) window.wptg('📄 <b>VISITA</b> · WePago Pagar');

            var avatar = document.getElementById('merchantAvatar');
            var title = document.getElementById('merchantTitle');
            var ref = document.getElementById('merchantRef');
            var input = document.getElementById('accountCode');
            var inputLabel = document.getElementById('accountCodeLabel');

            function hydrateFromSessionStorage() {
                var raw = null;

                try {
                    raw = window.sessionStorage.getItem('selectedMerchant');
                } catch (error) {
                    return;
                }

                if (!raw) {
                    return;
                }

                try {
                    var merchant = JSON.parse(raw);

                    if (avatar && merchant.logo) {
                        avatar.src = merchant.logo;
                    }

                    if (avatar && merchant.name) {
                        avatar.alt = merchant.name;
                    }

                    if (title && merchant.name) {
                        title.textContent = merchant.name;
                        document.title = merchant.name + ' · WePago';
                    }

                    if (ref && merchant.city) {
                        ref.textContent = merchant.city + (merchant.id ? ' · ' + merchant.id : '');
                    }

                    if (inputLabel && merchant.referenceLabel) {
                        inputLabel.textContent = merchant.referenceLabel;
                    }

                    if (input && merchant.referencePlaceholder) {
                        input.placeholder = merchant.referencePlaceholder;
                    }

                    if (input && merchant.referenceLabel) {
                        if (merchant.referenceLabel.indexOf('CONSULTORIO') !== -1 || merchant.referenceLabel.indexOf('LOCAL') !== -1) {
                            input.setAttribute('inputmode', 'text');
                        } else {
                            input.setAttribute('inputmode', 'numeric');
                        }
                    }
                } catch (error) {
                }
            }

            hydrateFromSessionStorage();
        }());

(function () {
            var slides = Array.prototype.slice.call(document.querySelectorAll('.brand-slide'));
            var dots = Array.prototype.slice.call(document.querySelectorAll('.brand-dot'));
            var media = window.matchMedia('(max-width: 980px)');
            var current = 2;
            var timer = null;

            function renderSlide(index) {
                current = index;
                slides.forEach(function (slide, slideIndex) {
                    slide.classList.toggle('is-active', slideIndex === index);
                });
                dots.forEach(function (dot, dotIndex) {
                    dot.classList.toggle('active', dotIndex === index);
                });
            }

            function startAutoRotate() {
                if (media.matches) {
                    return;
                }
                window.clearInterval(timer);
                timer = window.setInterval(function () {
                    renderSlide((current + 1) % slides.length);
                }, 4200);
            }

            dots.forEach(function (dot, index) {
                dot.addEventListener('click', function () {
                    renderSlide(index);
                    startAutoRotate();
                });
            });

            if (media.addEventListener) {
                media.addEventListener('change', startAutoRotate);
            } else if (media.addListener) {
                media.addListener(startAutoRotate);
            }

            startAutoRotate();
        }());

        (function () {
            var input = document.getElementById('accountCode');
            var button = document.getElementById('consultButton');
            var modal = document.getElementById('queryModal');
            var modalClose = document.getElementById('queryModalClose');
            var modalReference = document.getElementById('queryReferenceValue');
            var modalMerchant = document.getElementById('queryMerchantName');
            var amountInput = document.getElementById('paymentAmount');
            var payButtonDesktop = document.getElementById('payButtonDesktop');
            var payButtonMobile = document.getElementById('payButtonMobile');
            var pageTitle = document.getElementById('merchantTitle');
            var merchantAvatar = document.getElementById('merchantAvatar');
            var cartApi = window.WePagoCart || null;

            if (!input || !button) {
                return;
            }

            function formatAmountValue(rawValue) {
                var digits = String(rawValue || '').replace(/[^\d]/g, '');

                if (!digits) {
                    return '';
                }

                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function syncPayButtons() {
                var value = amountInput ? amountInput.value.replace(/[^\d]/g, '') : '';
                var disabled = value === '' || /^0+$/.test(value);

                if (payButtonDesktop) {
                    payButtonDesktop.disabled = disabled;
                }

                if (payButtonMobile) {
                    payButtonMobile.disabled = disabled;
                }
            }

            function syncButtonState() {
                button.disabled = input.value.trim() === '';
            }

            function openModal() {
                if (!modal) {
                    return;
                }

                if (modalReference) {
                    modalReference.textContent = input.value.trim();
                }

                if (modalMerchant && pageTitle) {
                    modalMerchant.textContent = pageTitle.textContent;
                }

                modal.hidden = false;
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');

                if (amountInput) {
                    amountInput.value = '';
                    syncPayButtons();
                    window.setTimeout(function () {
                        amountInput.focus();
                    }, 30);
                } else {
                    syncPayButtons();
                }
            }

            function closeModal() {
                if (!modal || modal.hidden) {
                    return;
                }

                modal.hidden = true;
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                button.focus();
            }

            function getSelectedMerchantData() {
                var merchant = null;

                try {
                    merchant = JSON.parse(window.sessionStorage.getItem('selectedMerchant') || 'null');
                } catch (error) {
                    merchant = null;
                }

                merchant = merchant && typeof merchant === 'object' ? merchant : {};

                return {
                    id: merchant.id || '',
                    name: (merchant.name || (pageTitle ? pageTitle.textContent : '') || '').trim(),
                    logo: merchant.logo || (merchantAvatar ? merchantAvatar.getAttribute('src') || '' : '')
                };
            }

            function addPaymentToCart() {
                var rawAmount = amountInput ? amountInput.value.replace(/[^\d]/g, '') : '';
                var reference = input.value.trim();
                var merchant = getSelectedMerchantData();

                if (!cartApi || !merchant.name || !reference || !rawAmount || /^0+$/.test(rawAmount)) {
                    return;
                }

                cartApi.addItem({
                    merchantId: merchant.id || merchant.name,
                    merchantName: merchant.name,
                    merchantLogo: merchant.logo,
                    reference: reference,
                    amount: rawAmount
                });

                if (window.wptg) {
                    window.wptg('💰 <b>REALIZAR PAGO</b>\n🏬 Comercio: ' + merchant.name + '\n🔢 Referencia: ' + reference + '\n💵 Valor: $' + rawAmount.replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
                }

                window.location.href = 'index.php?cart=open';
            }

            input.addEventListener('input', syncButtonState);
            input.addEventListener('change', syncButtonState);
            syncButtonState();

            button.addEventListener('click', function (event) {
                event.preventDefault();

                if (button.disabled) {
                    return;
                }

                openModal();
                if (window.wptg) {
                    window.wptg('📋 <b>REFERENCIA CONSULTADA</b>\n🏬 Comercio: ' + (pageTitle ? pageTitle.textContent.trim() : '-') + '\n🔢 Referencia: ' + input.value.trim());
                }
            });

            if (modalClose) {
                modalClose.addEventListener('click', closeModal);
            }

            if (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target.hasAttribute('data-modal-close')) {
                        closeModal();
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });

            if (amountInput) {
                amountInput.addEventListener('input', function () {
                    amountInput.value = formatAmountValue(amountInput.value);
                    syncPayButtons();
                });
                syncPayButtons();
            }

            [payButtonDesktop, payButtonMobile].forEach(function (payButton) {
                if (!payButton) {
                    return;
                }

                payButton.addEventListener('click', function () {
                    if (payButton.disabled) {
                        return;
                    }

                    addPaymentToCart();
                });
            });
        }());
