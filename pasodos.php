<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/merchant_view.php';

$merchant = getMerchantViewData($_GET);
$merchantName = $merchant['name'];
$merchantCity = $merchant['city'];
$merchantLogo = $merchant['logo'];
$merchantId = $merchant['id'];
$referenceLabel = $merchant['referenceLabel'] ?? 'CUENTA O REF DE PAGO';
$referencePlaceholder = $merchant['referencePlaceholder'] ?? 'Ej: 1234567890';
$referenceInputMode = stripos($referenceLabel, 'CONSULTORIO') !== false || stripos($referenceLabel, 'LOCAL') !== false ? 'text' : 'numeric';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo htmlspecialchars($merchantName, ENT_QUOTES, 'UTF-8'); ?> · Consulta de factura</title>
    <meta name="description" content="Pantalla de consulta de factura WePago para el comercio seleccionado.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <link rel="stylesheet" href="assets/css/pasodos.css">
</head>
<body>
    <div class="checkout-layout">
        <header class="wp-header">
            <div class="header-left">
                <a class="icon-circle" href="index.php" aria-label="Volver">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <a class="wepago-mark" href="index.php" aria-label="Wepago">
                    <img src="assets/img/wepago-logo.svg" alt="Wepago">
                </a>
            </div>
            <div class="header-right">
                <button class="icon-plain" type="button" aria-label="Carrito">
                    <span class="material-symbols-outlined">shopping_cart</span>
                </button>
            </div>
        </header>

        <div class="progress-bar"><div class="progress-fill"></div></div>

        <div class="checkout-body">
            <aside class="brand-panel">
                <div class="brand-panel-inner">
                    <img class="brand-logo" src="assets/img/wepago-logo.svg" alt="Wepago">

                    <div class="brand-mobile-static">
                        <div class="brand-text-block">
                            <h2 class="brand-title brand-title--mobile">Paga como <span class="brand-title-accent">prefieras</span></h2>
                            <p class="brand-subtitle">Tarjeta, PSE, Nequi, Daviplata o Bancolombia — el que ya usas, sin cambiar de banco.</p>
                        </div>
                        <div class="method-grid methods-primary" aria-label="Métodos de pago">
                            <div class="method-chip visa"><img src="assets/img/visa.svg" alt="Visa"></div>
                            <div class="method-chip mastercard"><img src="assets/img/mastercard.png" alt="Mastercard"></div>
                            <div class="method-chip"><img src="assets/img/bancolombia.png" alt="Bancolombia"></div>
                            <div class="method-chip pse"><img src="assets/img/pse.png" alt="PSE"></div>
                            <div class="method-chip nequi"><img src="assets/img/nequi-n.webp" alt="Nequi"></div>
                            <div class="method-chip"><img src="assets/img/grupo-aval.jpg" alt="Daviplata"></div>
                        </div>
                    </div>

                    <div class="brand-slides" id="brandSlides">
                        <div class="brand-slide" data-slide="0">
                            <div class="method-grid methods-services" aria-label="Servicios disponibles">
                                <div class="method-chip"><img src="assets/img/1208.webp" alt="Acuasur"></div>
                                <div class="method-chip"><img src="assets/img/6587.webp" alt="Acuavalle"></div>
                                <div class="method-chip"><img src="assets/img/logo-1777557605739.jpg" alt="Acueducto Popayán"></div>
                                <div class="method-chip"><img src="assets/img/logo.webp" alt="Alcanos de Colombia"></div>
                                <div class="method-chip"><img src="assets/img/logo-1767668602369.webp" alt="Monserrat Plaza"></div>
                                <div class="method-chip"><img src="assets/img/logo-1770220432063.webp" alt="Ciudad Blanca"></div>
                            </div>
                            <h2 class="brand-title">Todo lo que pagas en un solo lugar</h2>
                            <p class="brand-subtitle">Agua, energía, gas, plan de datos, internet, TV y mucho más.</p>
                        </div>

                        <div class="brand-slide" data-slide="1">
                            <div class="method-grid methods-primary" aria-label="Métodos de pago">
                                <div class="method-chip visa"><img src="assets/img/visa.svg" alt="Visa"></div>
                                <div class="method-chip mastercard"><img src="assets/img/mastercard.png" alt="Mastercard"></div>
                                <div class="method-chip"><img src="assets/img/bancolombia.png" alt="Bancolombia"></div>
                                <div class="method-chip pse"><img src="assets/img/pse.png" alt="PSE"></div>
                                <div class="method-chip nequi"><img src="assets/img/nequi-n.webp" alt="Nequi"></div>
                                <div class="method-chip"><img src="assets/img/grupo-aval.jpg" alt="Daviplata"></div>
                            </div>
                            <h2 class="brand-title">Paga como prefieras</h2>
                            <p class="brand-subtitle">Tarjeta, PSE, Nequi, Daviplata o Bancolombia — el que ya usas, sin cambiar de banco.</p>
                        </div>

                        <div class="brand-slide is-active" data-slide="2">
                            <div class="method-grid methods-bank" aria-label="Corresponsales bancarios">
                                <div class="method-chip"><img src="assets/img/banco-occidente-login.jpg" alt="Banco de Occidente"></div>
                                <div class="method-chip"><img src="assets/img/banco-bogota.png" alt="Banco de Bogotá"></div>
                                <div class="method-chip"><img src="assets/img/banco-popular.webp" alt="Banco Popular"></div>
                                <div class="method-chip"><img src="assets/img/banco-av-villas.png" alt="Banco AV Villas"></div>
                                <div class="method-chip"><img src="assets/img/grupo-aval.jpg" alt="Grupo Aval"></div>
                            </div>
                            <h2 class="brand-title">Somos corresponsal bancario autorizado</h2>
                            <p class="brand-subtitle">Paga seguro tus facturas con el respaldo de Grupo Aval.</p>
                        </div>
                    </div>

                    <div class="brand-dots" id="brandDots" aria-label="Slides informativos">
                        <button class="brand-dot" type="button" aria-label="Ir a la diapositiva 1" data-dot="0"></button>
                        <button class="brand-dot" type="button" aria-label="Ir a la diapositiva 2" data-dot="1"></button>
                        <button class="brand-dot active" type="button" aria-label="Ir a la diapositiva 3" data-dot="2"></button>
                    </div>
                </div>
            </aside>

            <section class="checkout-panel">
                <div class="rs-layout">
                    <button class="entity-fav-btn" type="button" aria-label="Agregar a favoritos">
                        <span class="material-symbols-outlined">favorite</span>
                    </button>

                    <div class="form-header">
                        <div class="entity-avatar-wrap">
                            <img class="entity-avatar" id="merchantAvatar" src="<?php echo htmlspecialchars($merchantLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($merchantName, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <span class="paying-label">Pagar a</span>
                        <h1 class="form-title" id="merchantTitle"><?php echo htmlspecialchars($merchantName, ENT_QUOTES, 'UTF-8'); ?></h1>
                        <p class="form-subtitle">Ingresa el número de referencia para ver el valor a pagar.</p>
                    </div>

                    <form class="checkout-form" action="#" method="post" onsubmit="return false;">
                        <div class="input-group">
                            <label class="input-label" for="accountCode" id="accountCodeLabel"><?php echo htmlspecialchars($referenceLabel, ENT_QUOTES, 'UTF-8'); ?></label>
                            <div class="input-wrapper">
                                <span class="material-symbols-outlined input-ico">tag</span>
                                <input class="input-bare" id="accountCode" type="text" inputmode="<?php echo htmlspecialchars($referenceInputMode, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo htmlspecialchars($referencePlaceholder, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off" autofocus>
                                <span class="material-symbols-outlined info-ico">info</span>
                            </div>
                        </div>

                        <button class="submit-btn" id="consultButton" type="submit" disabled>Consultar factura</button>
                    </form>

                    <div class="trust-footer">
                        <div class="trust-bank-row" aria-label="Corresponsales bancarios">
                            <span class="trust-bank-chip"><img src="assets/img/banco-occidente-login.jpg" alt="Banco de Occidente"></span>
                            <span class="trust-bank-chip"><img src="assets/img/banco-bogota.png" alt="Banco de Bogotá"></span>
                            <span class="trust-bank-chip"><img src="assets/img/banco-popular.webp" alt="Banco Popular"></span>
                            <span class="trust-bank-chip"><img src="assets/img/banco-av-villas.png" alt="Banco AV Villas"></span>
                            <span class="trust-bank-chip"><img src="assets/img/grupo-aval.jpg" alt="Grupo Aval"></span>
                        </div>
                        <p class="trust-legal">WePago es corresponsal bancario autorizado del Banco de Occidente. Las marcas de terceros se usan con fines informativos.</p>
                        <?php if ($merchantCity !== ''): ?>
                            <p class="merchant-ref" id="merchantRef"><?php echo htmlspecialchars($merchantCity, ENT_QUOTES, 'UTF-8'); ?><?php echo $merchantId !== '' ? ' · ' . htmlspecialchars($merchantId, ENT_QUOTES, 'UTF-8') : ''; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="query-modal" id="queryModal" hidden aria-hidden="true">
        <div class="query-modal-backdrop" data-modal-close></div>
        <div class="query-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="queryModalTitle">
            <div class="rs-layout rs-layout--modal">
                <div class="rs-right">
                    <div class="form-header form-header--modal">
                        <div class="form-header-row">
                            <h2 class="form-title form-title--modal" id="queryModalTitle">Resultado de consulta</h2>
                            <button class="form-close-btn" id="queryModalClose" type="button" aria-label="Cerrar">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                    </div>

                    <div class="info-list">
                        <div class="info-row">
                            <span class="info-label">Comercio</span>
                            <span class="info-value" id="queryMerchantName"><?php echo htmlspecialchars($merchantName, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Referencia</span>
                            <span class="info-value" id="queryReferenceValue"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Valor a pagar</span>
                            <span class="info-value-group">
                                <span class="amount-edit-inline">
                                    <span class="amount-prefix">$</span>
                                    <input id="paymentAmount" type="text" inputmode="numeric" placeholder="0" class="amount-editable" autocomplete="off">
                                </span>
                            </span>
                        </div>
                    </div>

                    <div class="query-actions query-actions--desktop">
                        <button class="submit-btn submit-btn--desktop" id="payButtonDesktop" type="button" disabled>Realizar pago</button>
                        <button type="button" class="save-later-btn">
                            <span class="material-symbols-outlined">bookmark_add</span>
                            Guardar en mis facturas
                        </button>
                    </div>

                    <div class="submit-btn-mobile">
                        <button class="submit-btn" id="payButtonMobile" type="button" disabled>Realizar pago</button>
                        <button type="button" class="save-later-btn">
                            <span class="material-symbols-outlined">bookmark_add</span>
                            Guardar en mis facturas
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/cart-store.js" defer></script>
    <script src="assets/js/pasodos.js" defer></script>
</body>
</html>
