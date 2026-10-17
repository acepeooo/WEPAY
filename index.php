<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/merchant_helpers.php';

$popularMerchants = array_map('normalizeMerchant', require __DIR__ . '/data/popular_merchants.php');
$menuItems = require __DIR__ . '/data/menu_items.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>WePago · El Directorio de Pagos de Colombia</title>
    <meta name="description" content="Portal de pagos WePago con directorio de comercios, búsqueda y acceso responsive para pagos en Colombia.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <div class="app-shell">
        <aside class="app-sidebar" id="sidebar">
            <div class="sidebar-top">
                <a class="brand-mark" href="#" aria-label="WePago">
                    <img src="assets/img/wepago-logo.svg" alt="WePago">
                </a>
                <button class="collapse-btn" id="collapseBtn" type="button" aria-label="Colapsar menú">
                    <span class="material-symbols-outlined">dock_to_left</span>
                </button>
            </div>

            <nav class="nav-links" aria-label="Navegación principal">
                <?php foreach ($menuItems as $item): ?>
                    <button class="nav-link<?php echo $item['active'] ? ' is-active' : ''; ?>" type="button" aria-label="<?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="nav-icon">
                            <span class="material-symbols-outlined"><?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </span>
                        <span class="nav-label"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-spacer"></div>

            <button class="login-btn" type="button">
                <span class="material-symbols-outlined">person</span>
                <span>Iniciar sesión</span>
            </button>
        </aside>

        <div class="mobile-overlay" id="mobileOverlay"></div>

        <main class="app-main">
            <header class="app-header">
                <div class="header-left">
                    <button class="icon-btn menu-btn" id="menuBtn" type="button" aria-label="Abrir menú">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <a class="mobile-brand" href="#" aria-label="WePago">
                        <img src="assets/img/wepago-logo.svg" alt="WePago">
                    </a>
                </div>

                <div class="header-right">
                    <button class="icon-btn header-cart" id="headerCartButton" type="button" aria-label="Carrito">
                        <span class="material-symbols-outlined">shopping_cart</span>
                        <span class="cart-count-badge" id="cartCountBadge" hidden>0</span>
                    </button>
                    <button class="header-login" type="button">
                        <span class="material-symbols-outlined">person</span>
                        <span>Iniciar sesión</span>
                    </button>
                </div>
            </header>

            <section class="hero-section">
                <div class="hero-wrap">
                    <div class="hero-copy">
                        <h1 class="hero-title hero-title--greeting">¿Qué vas a pagar hoy?</h1>
                        <p class="hero-subtitle">Servicios públicos, colegios, impuestos, telefonía y más.</p>
                    </div>

                    <div class="search-shell">
                        <form class="search-form" id="searchForm" action="#" method="get" onsubmit="return false;">
                            <span class="material-symbols-outlined search-icon">search</span>
                            <input class="search-input" id="searchInput" type="text" name="q" placeholder="Busca un comercio o servicio..." autocomplete="off">
                        </form>
                        <div class="search-meta" id="searchMeta">Escribe el nombre de un comercio para buscar en tiempo real.</div>
                    </div>

                    <section class="popular-block" aria-labelledby="popular-title">
                        <h2 class="section-title" id="popular-title">Comercios populares</h2>

                        <div class="merchant-grid" id="merchantGrid">
                            <?php foreach ($popularMerchants as $merchant): ?>
                                <?php echo merchantCardMarkup($merchant); ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="search-pagination" id="searchPagination" hidden></div>
                        <div class="results-empty" id="emptyState">No encontramos comercios para esa búsqueda.</div>
                    </section>
                </div>
            </section>
        </main>
    </div>

    <div class="cart-overlay" id="cartOverlay" hidden></div>

    <aside class="cart-drawer" id="cartDrawer" aria-hidden="true">
        <div class="cart-drawer-panel">
            <div class="cart-drawer-header">
                <h2 class="cart-drawer-title">Tu carrito de pago</h2>
                <button class="cart-drawer-close" id="cartCloseButton" type="button" aria-label="Cerrar carrito">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="cart-drawer-body">
                <div class="cart-items" id="cartItems"></div>
                <div class="cart-empty" id="cartEmptyState">
                    <span class="material-symbols-outlined cart-empty-icon">shopping_cart</span>
                    <h3 class="cart-empty-title">Tu carrito está vacío</h3>
                    <p class="cart-empty-text">Busca un comercio y agrega facturas para comenzar</p>
                    <button class="cart-empty-button" id="cartExploreButton" type="button">
                        <span class="material-symbols-outlined">storefront</span>
                        Explorar comercios
                    </button>
                </div>

                <button class="cart-multipay-link" id="cartMultiPayTrigger" type="button">
                    <span class="material-symbols-outlined">storefront</span>
                    Paga a varios comercios
                </button>
            </div>

            <div class="cart-drawer-footer">
                <button class="cart-pay-button" id="cartPayButton" type="button" disabled>Pagar $0</button>
            </div>
        </div>
    </aside>

    <div class="multipay-modal-wrap" id="multipayModal" hidden aria-hidden="true">
        <div class="multipay-backdrop" data-multipay-close></div>
        <div class="multipay-modal" role="dialog" aria-modal="true" aria-labelledby="multipayTitle">
            <span class="material-symbols-outlined multipay-icon">storefront</span>
            <h3 class="multipay-title" id="multipayTitle">Un solo pago, varios comercios</h3>
            <p class="multipay-desc">Cada factura llega con su propio comprobante — ahorra en el costo de transacción.</p>
            <button class="multipay-btn-primary" id="multipayAddMoreButton" type="button">Agregar pago a otro comercio</button>
            <button class="multipay-btn-secondary" id="multipayDismissButton" type="button">Ahora no</button>
        </div>
    </div>

    <script src="assets/js/cart-store.js" defer></script>
    <script src="assets/js/index.js" defer></script>
</body>
</html>
