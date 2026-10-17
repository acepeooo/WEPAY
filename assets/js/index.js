(function () {
    var body = document.body;
    var menuBtn = document.getElementById('menuBtn');
    var mobileOverlay = document.getElementById('mobileOverlay');
    var collapseBtn = document.getElementById('collapseBtn');
    var mobileBreakpoint = window.matchMedia('(max-width: 980px)');
    var searchForm = document.getElementById('searchForm');
    var searchInput = document.getElementById('searchInput');
    var searchMeta = document.getElementById('searchMeta');
    var merchantGrid = document.getElementById('merchantGrid');
    var searchPagination = document.getElementById('searchPagination');
    var emptyState = document.getElementById('emptyState');
    var title = document.getElementById('popular-title');
    var headerCartButton = document.getElementById('headerCartButton');
    var cartCountBadge = document.getElementById('cartCountBadge');
    var cartOverlay = document.getElementById('cartOverlay');
    var cartDrawer = document.getElementById('cartDrawer');
    var cartCloseButton = document.getElementById('cartCloseButton');
    var cartItems = document.getElementById('cartItems');
    var cartEmptyState = document.getElementById('cartEmptyState');
    var cartExploreButton = document.getElementById('cartExploreButton');
    var cartDrawerBody = document.querySelector('.cart-drawer-body');
    var cartDrawerFooter = document.querySelector('.cart-drawer-footer');
    var cartPayButton = document.getElementById('cartPayButton');
    var cartMultiPayTrigger = document.getElementById('cartMultiPayTrigger');
    var multipayModal = document.getElementById('multipayModal');
    var multipayAddMoreButton = document.getElementById('multipayAddMoreButton');
    var multipayDismissButton = document.getElementById('multipayDismissButton');
    var searchUrl = 'api/search.php';
    var selectMerchantUrl = 'api/select_merchant.php';
    var defaultCardsHtml = merchantGrid ? merchantGrid.innerHTML : '';
    var defaultTitle = title ? title.textContent : '';
    var debounceTimer = null;
    var currentController = null;
    var searchState = createSearchState();
    var cartApi = window.WePagoCart || null;

    function createSearchState() {
        return {
            term: '',
            currentPage: 1,
            pageCache: {},
            pageCursors: { 1: null },
            hasMoreByPage: {}
        };
    }

    function closeMobileNav() {
        body.classList.remove('nav-open');
    }

    function toggleMobileNav() {
        body.classList.toggle('nav-open');
    }

    function handleViewportChange(e) {
        if (!e.matches) {
            closeMobileNav();
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatCityLabel(value) {
        var city = String(value || '').trim();

        if (!city) {
            return 'Colombia';
        }

        return city.toLocaleLowerCase('es-CO').replace(/([A-Za-zÀ-ÖØ-öø-ÿ]+)/g, function (match) {
            return match.charAt(0).toLocaleUpperCase('es-CO') + match.slice(1);
        });
    }

    function getMerchantInitials(name) {
        return String(name || '')
            .trim()
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map(function (part) { return part.charAt(0); })
            .join('')
            .toUpperCase() || 'WP';
    }

    function formatCartAmount(amount) {
        return '$' + (cartApi ? cartApi.formatAmount(amount) : String(amount || 0));
    }

    function renderMerchantCard(item) {
        var city = formatCityLabel(item.city || 'Colombia');
        var referenceLabel = item.referenceLabel || 'CUENTA O REF DE PAGO';
        var referencePlaceholder = item.referencePlaceholder || 'Ej: 1234567890';

        return [
            '<a class="merchant-card" href="' + buildMerchantUrl(item) + '" aria-label="' + escapeHtml(item.name || '') + '" data-merchant-id="' + escapeHtml(item.id || '') + '" data-merchant-name="' + escapeHtml(item.name || '') + '" data-merchant-city="' + escapeHtml(city) + '" data-merchant-logo="' + escapeHtml(item.logo || '') + '" data-merchant-reference-label="' + escapeHtml(referenceLabel) + '" data-merchant-reference-placeholder="' + escapeHtml(referencePlaceholder) + '">',
            '    <img class="merchant-logo" src="' + escapeHtml(item.logo || '') + '" alt="' + escapeHtml(item.name || '') + '" loading="lazy">',
            '    <h3 class="merchant-name">' + escapeHtml(item.name || '') + '</h3>',
            '    <p class="merchant-city"><span class="material-symbols-outlined">location_on</span>' + escapeHtml(city) + '</p>',
            '</a>'
        ].join('');
    }

    function buildMerchantUrl() {
        return 'pasodos.php';
    }

    function getMerchantFromElement(element) {
        if (!element) {
            return null;
        }

        return {
            id: element.getAttribute('data-merchant-id') || '',
            name: element.getAttribute('data-merchant-name') || '',
            city: element.getAttribute('data-merchant-city') || '',
            logo: element.getAttribute('data-merchant-logo') || '',
            referenceLabel: element.getAttribute('data-merchant-reference-label') || '',
            referencePlaceholder: element.getAttribute('data-merchant-reference-placeholder') || ''
        };
    }

    function persistMerchantSelection(merchant) {
        if (!merchant || !merchant.name) {
            return Promise.resolve();
        }

        try {
            window.sessionStorage.setItem('selectedMerchant', JSON.stringify(merchant));
        } catch (error) {
        }

        return fetch(selectMerchantUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(merchant)
        }).catch(function () {
        });
    }

    function setLoadingState(isLoading) {
        if (!searchForm) {
            return;
        }
        searchForm.classList.toggle('is-loading', isLoading);
    }

    function renderPagination() {
        if (!searchPagination) {
            return;
        }

        var knownPages = Object.keys(searchState.pageCache)
            .map(function (value) { return parseInt(value, 10); })
            .filter(function (value) { return !isNaN(value); })
            .sort(function (a, b) { return a - b; });

        var hasPrevious = searchState.currentPage > 1;
        var hasNext = !!searchState.hasMoreByPage[searchState.currentPage] || !!searchState.pageCache[searchState.currentPage + 1];

        if (searchState.term.length < 2 || knownPages.length === 0) {
            searchPagination.hidden = true;
            searchPagination.innerHTML = '';
            return;
        }

        var buttons = [];
        buttons.push('<button class="page-btn page-arrow" type="button" data-page-action="prev"' + (hasPrevious ? '' : ' disabled') + '><span class="material-symbols-outlined">chevron_left</span></button>');

        knownPages.forEach(function (page) {
            buttons.push('<button class="page-btn' + (page === searchState.currentPage ? ' is-active' : '') + '" type="button" data-page="' + page + '">' + page + '</button>');
        });

        if (searchState.hasMoreByPage[knownPages[knownPages.length - 1]]) {
            buttons.push('<button class="page-btn page-btn--ghost" type="button" data-page="' + (knownPages[knownPages.length - 1] + 1) + '">' + (knownPages[knownPages.length - 1] + 1) + '</button>');
        }

        buttons.push('<button class="page-btn page-arrow" type="button" data-page-action="next"' + (hasNext ? '' : ' disabled') + '><span class="material-symbols-outlined">chevron_right</span></button>');

        searchPagination.hidden = false;
        searchPagination.innerHTML = buttons.join('');
    }

    function restorePopularState() {
        if (currentController) {
            currentController.abort();
            currentController = null;
        }

        searchState = createSearchState();

        if (merchantGrid) {
            merchantGrid.classList.remove('is-search-results');
            merchantGrid.innerHTML = defaultCardsHtml;
        }
        if (title) {
            title.textContent = defaultTitle;
        }
        if (searchMeta) {
            searchMeta.textContent = 'Escribe el nombre de un comercio para buscar en tiempo real.';
        }
        if (emptyState) {
            emptyState.classList.remove('is-visible');
        }
        if (searchPagination) {
            searchPagination.hidden = true;
            searchPagination.innerHTML = '';
        }
        setLoadingState(false);
    }

    function applySearchResults(term, page, payload) {
        var items = Array.isArray(payload.items) ? payload.items : [];

        searchState.term = term;
        searchState.currentPage = page;
        searchState.pageCache[page] = payload;
        searchState.hasMoreByPage[page] = !!payload.hasMore;

        if (payload.cursor) {
            searchState.pageCursors[page + 1] = payload.cursor;
        } else if (!payload.hasMore) {
            delete searchState.pageCursors[page + 1];
        }

        if (title) {
            title.textContent = 'Resultados de búsqueda';
        }

        if (searchMeta) {
            if (items.length > 0) {
                searchMeta.textContent = 'Mostrando ' + items.length + ' resultado(s) en la página ' + page + ' para "' + term + '".';
            } else {
                searchMeta.textContent = payload.message || 'No encontramos coincidencias.';
            }
        }

        if (merchantGrid) {
            merchantGrid.classList.add('is-search-results');
            merchantGrid.innerHTML = items.map(renderMerchantCard).join('');
        }

        if (emptyState) {
            emptyState.classList.toggle('is-visible', items.length === 0);
        }

        renderPagination();
    }

    function loadSearchPage(term, page) {
        if (!merchantGrid || !searchMeta) {
            return;
        }

        if (term.length < 2) {
            restorePopularState();
            return;
        }

        if (currentController) {
            currentController.abort();
        }

        if (searchState.term !== term) {
            searchState = createSearchState();
            searchState.term = term;
        }

        if (searchState.pageCache[page]) {
            applySearchResults(term, page, searchState.pageCache[page]);
            return;
        }

        var cursor = searchState.pageCursors[page];
        if (page > 1 && typeof cursor === 'undefined') {
            return;
        }

        currentController = new AbortController();
        setLoadingState(true);
        searchMeta.textContent = 'Buscando comercios...';

        var requestUrl = new URL(searchUrl, window.location.href);
        requestUrl.searchParams.set('q', term);
        requestUrl.searchParams.set('limit', '25');
        if (cursor) {
            requestUrl.searchParams.set('cursor', cursor);
        }

        fetch(requestUrl.toString(), {
            headers: { 'Accept': 'application/json' },
            signal: currentController.signal
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload.success) {
                    throw new Error(payload.message || 'Error consultando la API');
                }
                applySearchResults(term, page, payload);
            })
            .catch(function (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                if (title) {
                    title.textContent = 'Resultados de búsqueda';
                }
                if (merchantGrid) {
                    merchantGrid.innerHTML = '';
                }
                if (emptyState) {
                    emptyState.textContent = 'No fue posible consultar la API. Intenta nuevamente.';
                    emptyState.classList.add('is-visible');
                }
                searchMeta.textContent = 'Error consultando la API.';
            })
            .finally(function () {
                setLoadingState(false);
            });
    }

    function renderCartItem(item) {
        return [
            '<article class="cart-item" data-cart-key="' + escapeHtml(item.key || '') + '">',
            '    <span class="cart-item-avatar">' + escapeHtml(getMerchantInitials(item.merchantName)) + '</span>',
            '    <div class="cart-item-content">',
            '        <h3 class="cart-item-title">' + escapeHtml(item.merchantName || 'Comercio') + '</h3>',
            '        <span class="cart-item-ref">Ref. ' + escapeHtml(item.reference || '-') + '</span>',
            '        <div class="cart-item-amount">' + escapeHtml(formatCartAmount(item.amount || 0)) + '</div>',
            '    </div>',
            '    <button class="cart-item-remove" type="button" aria-label="Eliminar del carrito" data-cart-remove="' + escapeHtml(item.key || '') + '">',
            '        <span class="material-symbols-outlined">delete</span>',
            '    </button>',
            '</article>'
        ].join('');
    }

    function updateCartUI() {
        if (!cartApi) {
            return;
        }

        var items = cartApi.load();
        var total = cartApi.getTotal(items);
        var itemCount = items.length;

        if (cartItems) {
            cartItems.innerHTML = items.map(renderCartItem).join('');
        }

        if (cartEmptyState) {
            cartEmptyState.classList.toggle('is-visible', itemCount === 0);
        }

        if (cartDrawerBody) {
            cartDrawerBody.classList.toggle('is-empty', itemCount === 0);
        }

        if (cartDrawerFooter) {
            cartDrawerFooter.hidden = itemCount === 0;
        }

        if (cartCountBadge) {
            cartCountBadge.hidden = itemCount === 0;
            cartCountBadge.textContent = String(itemCount);
        }

        if (cartPayButton) {
            cartPayButton.disabled = itemCount === 0;
            cartPayButton.textContent = 'Pagar ' + formatCartAmount(total);
        }

        if (cartMultiPayTrigger) {
            cartMultiPayTrigger.hidden = itemCount === 0;
            cartMultiPayTrigger.disabled = itemCount === 0;
        }

        if (itemCount === 0) {
            closeMultipayModal();
        }
    }

    function openCartDrawer() {
        body.classList.add('cart-open');
        closeMobileNav();

        if (cartOverlay) {
            cartOverlay.hidden = false;
        }

        if (cartDrawer) {
            cartDrawer.setAttribute('aria-hidden', 'false');
        }
    }

    function closeCartDrawer() {
        body.classList.remove('cart-open');

        if (cartOverlay) {
            cartOverlay.hidden = true;
        }

        if (cartDrawer) {
            cartDrawer.setAttribute('aria-hidden', 'true');
        }
    }

    function openMultipayModal() {
        if (!cartApi || cartApi.load().length === 0 || !multipayModal) {
            return;
        }

        multipayModal.hidden = false;
        multipayModal.setAttribute('aria-hidden', 'false');
        body.classList.add('multipay-open');
    }

    function closeMultipayModal() {
        if (!multipayModal) {
            return;
        }

        multipayModal.hidden = true;
        multipayModal.setAttribute('aria-hidden', 'true');
        body.classList.remove('multipay-open');
    }

    function removeCartQueryParam() {
        var url = new URL(window.location.href);

        if (!url.searchParams.has('cart')) {
            return;
        }

        url.searchParams.delete('cart');
        window.history.replaceState({}, '', url.pathname + (url.search ? url.search : '') + url.hash);
    }

    if (menuBtn) {
        menuBtn.addEventListener('click', toggleMobileNav);
    }

    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMobileNav);
    }

    if (collapseBtn) {
        collapseBtn.addEventListener('click', function () {
            if (mobileBreakpoint.matches) {
                closeMobileNav();
                return;
            }
            body.classList.toggle('sidebar-collapsed');
        });
    }

    if (mobileBreakpoint.addEventListener) {
        mobileBreakpoint.addEventListener('change', handleViewportChange);
    } else if (mobileBreakpoint.addListener) {
        mobileBreakpoint.addListener(handleViewportChange);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMobileNav();
            closeMultipayModal();
            closeCartDrawer();
        }
    });

    document.addEventListener('click', function (event) {
        var card = event.target.closest('.merchant-card');
        var pageButton = event.target.closest('[data-page], [data-page-action]');

        if (pageButton) {
            event.preventDefault();

            if (pageButton.hasAttribute('disabled')) {
                return;
            }

            if (pageButton.dataset.pageAction === 'prev' && searchState.currentPage > 1) {
                loadSearchPage(searchState.term, searchState.currentPage - 1);
                return;
            }

            if (pageButton.dataset.pageAction === 'next') {
                loadSearchPage(searchState.term, searchState.currentPage + 1);
                return;
            }

            if (pageButton.dataset.page) {
                loadSearchPage(searchState.term, parseInt(pageButton.dataset.page, 10));
                return;
            }
        }

        if (!card) {
            return;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
            return;
        }

        event.preventDefault();

        var merchant = getMerchantFromElement(card);
        var href = card.getAttribute('href') || 'pasodos.php';

        persistMerchantSelection(merchant).finally(function () {
            window.location.href = href;
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var term = searchInput.value.trim();

            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(function () {
                loadSearchPage(term, 1);
            }, 320);
        });
    }

    if (headerCartButton) {
        headerCartButton.addEventListener('click', function () {
            updateCartUI();
            openCartDrawer();
        });
    }

    if (cartCloseButton) {
        cartCloseButton.addEventListener('click', closeCartDrawer);
    }

    if (cartOverlay) {
        cartOverlay.addEventListener('click', closeCartDrawer);
    }

    if (cartItems) {
        cartItems.addEventListener('click', function (event) {
            var removeButton = event.target.closest('[data-cart-remove]');

            if (!removeButton || !cartApi) {
                return;
            }

            cartApi.removeItem(removeButton.getAttribute('data-cart-remove') || '');
            updateCartUI();
        });
    }

    if (cartPayButton) {
        cartPayButton.addEventListener('click', function () {
            if (cartApi && cartApi.load().length > 0) {
                window.location.href = 'checkout.php';
            }
        });
    }

    if (cartMultiPayTrigger) {
        cartMultiPayTrigger.addEventListener('click', function () {
            openMultipayModal();
        });
    }

    if (cartExploreButton) {
        cartExploreButton.addEventListener('click', function () {
            closeCartDrawer();
            restorePopularState();
            if (searchInput) {
                searchInput.focus();
            }
        });
    }

    if (multipayModal) {
        multipayModal.addEventListener('click', function (event) {
            if (event.target.hasAttribute('data-multipay-close')) {
                closeMultipayModal();
            }
        });
    }

    if (multipayDismissButton) {
        multipayDismissButton.addEventListener('click', function () {
            closeMultipayModal();
        });
    }

    if (multipayAddMoreButton) {
        multipayAddMoreButton.addEventListener('click', function () {
            closeMultipayModal();
            closeCartDrawer();
            if (searchInput) {
                searchInput.focus();
            }
        });
    }

    updateCartUI();

    if (new URLSearchParams(window.location.search).get('cart') === 'open' && cartApi && cartApi.load().length > 0) {
        openCartDrawer();
        removeCartQueryParam();
    }
}());
