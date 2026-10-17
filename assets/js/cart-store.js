(function (global) {
    'use strict';

    var STORAGE_KEY = 'wepago_cart_v1';
    var formatter = new Intl.NumberFormat('es-CO', {
        maximumFractionDigits: 0
    });

    function safeParseCart(raw) {
        if (!raw) {
            return [];
        }

        try {
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function formatAmount(amount) {
        var numeric = Number(amount) || 0;
        return formatter.format(Math.max(0, Math.round(numeric)));
    }

    function getItemKey(item) {
        var merchantId = String(item.merchantId || item.id || item.merchantName || '').trim();
        var reference = String(item.reference || '').trim();
        return merchantId + '::' + reference;
    }

    function normalizeItem(item) {
        var amount = Number(String(item.amount || 0).replace(/[^\d]/g, '')) || 0;
        var merchantName = String(item.merchantName || item.name || 'Comercio').trim();
        var reference = String(item.reference || '').trim();
        var merchantId = String(item.merchantId || item.id || merchantName).trim();

        return {
            key: getItemKey({
                merchantId: merchantId,
                reference: reference
            }),
            merchantId: merchantId,
            merchantName: merchantName,
            merchantLogo: String(item.merchantLogo || item.logo || '').trim(),
            reference: reference,
            amount: amount,
            amountFormatted: formatAmount(amount),
            addedAt: Number(item.addedAt) || Date.now()
        };
    }

    function loadCart() {
        try {
            return safeParseCart(global.localStorage.getItem(STORAGE_KEY)).map(normalizeItem);
        } catch (error) {
            return [];
        }
    }

    function saveCart(items) {
        var normalized = Array.isArray(items) ? items.map(normalizeItem) : [];

        try {
            global.localStorage.setItem(STORAGE_KEY, JSON.stringify(normalized));
        } catch (error) {
        }

        return normalized;
    }

    function addItem(item) {
        var normalized = normalizeItem(item);
        var cart = loadCart();
        var existingIndex = cart.findIndex(function (entry) {
            return entry.key === normalized.key;
        });

        if (existingIndex >= 0) {
            cart[existingIndex] = normalized;
        } else {
            cart.push(normalized);
        }

        return saveCart(cart);
    }

    function removeItem(key) {
        return saveCart(loadCart().filter(function (item) {
            return item.key !== key;
        }));
    }

    function clearCart() {
        return saveCart([]);
    }

    function getTotal(items) {
        return (items || loadCart()).reduce(function (sum, item) {
            return sum + (Number(item.amount) || 0);
        }, 0);
    }

    global.WePagoCart = {
        storageKey: STORAGE_KEY,
        formatAmount: formatAmount,
        load: loadCart,
        save: saveCart,
        addItem: addItem,
        removeItem: removeItem,
        clear: clearCart,
        getTotal: getTotal
    };
}(window));
