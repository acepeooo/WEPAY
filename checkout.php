<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helppiupay.php';

$daviplataLogo = is_file(__DIR__ . '/assets/img/daviplata.webp') ? 'assets/img/daviplata.webp' : '';

$docOptions = ['CC' => 'CC', 'CE' => 'CE', 'NIT' => 'NIT', 'PP' => 'PP', 'TI' => 'TI'];
$pseBanks = pseBankCodes();
asort($pseBanks, SORT_LOCALE_STRING);

$fName = ['name' => 'fullName', 'type' => 'text', 'validate' => 'name', 'icon' => 'person', 'placeholder' => 'Nombre completo', 'autocomplete' => 'name'];
$fEmail = ['name' => 'email', 'type' => 'email', 'validate' => 'email', 'icon' => 'mail', 'placeholder' => 'Correo electrónico', 'inputmode' => 'email', 'autocomplete' => 'email'];
$fDocType = ['name' => 'docType', 'type' => 'select', 'validate' => 'select', 'narrow' => true, 'placeholder' => 'Tipo', 'options' => $docOptions, 'default' => 'CC', 'label' => 'Tipo de documento'];
$fDocNumber = ['name' => 'docNumber', 'type' => 'tel', 'validate' => 'doc', 'icon' => 'badge', 'placeholder' => 'Número de documento', 'inputmode' => 'numeric', 'maxlength' => 15];
$docRow = [$fDocType, $fDocNumber];
$fPhone = ['name' => 'phone', 'type' => 'tel', 'validate' => 'phone', 'icon' => 'phone_iphone', 'placeholder' => 'Celular', 'inputmode' => 'tel', 'autocomplete' => 'tel-national', 'maxlength' => 13];

$methods = [
    'bancolombia' => [
        'name' => 'Botón Bancolombia',
        'short' => 'Bancolombia',
        'sub' => 'Transferencia desde tu cuenta Bancolombia',
        'logo' => '<span class="ck-method-logo ck-logo-bancolombia"><img src="assets/img/bancolombia.png" alt=""></span>',
        'groups' => [['title' => 'Tus datos', 'rows' => [[$fName], [$fEmail], $docRow]]],
    ],
    'pse' => [
        'name' => 'PSE',
        'short' => 'PSE',
        'sub' => 'Débito a cuenta bancaria',
        'logo' => '<span class="ck-method-logo ck-logo-pse"><img src="assets/img/pse.png" alt=""></span>',
        'groups' => [
            ['title' => 'Información bancaria', 'rows' => [
                [['name' => 'personType', 'type' => 'select', 'validate' => 'select', 'icon' => 'person', 'placeholder' => 'Tipo de usuario', 'label' => 'Tipo de usuario', 'default' => '0', 'options' => ['0' => 'Persona Natural', '1' => 'Persona Jurídica']]],
                [['name' => 'bank', 'type' => 'combobox', 'validate' => 'select', 'icon' => 'account_balance', 'placeholder' => 'Banco', 'label' => 'Banco', 'options' => $pseBanks]],
            ]],
            ['title' => 'Tus datos', 'rows' => [[$fName], [$fEmail], $docRow, [$fPhone]]],
        ],
    ],
    'daviplata' => [
        'name' => 'Daviplata',
        'short' => 'Daviplata',
        'sub' => 'Aprobación desde tu app Daviplata',
        'logo' => $daviplataLogo !== ''
            ? '<span class="ck-method-logo ck-logo-davi-img"><img src="' . $daviplataLogo . '" alt=""></span>'
            : '<span class="ck-method-logo ck-logo-davi"><span>davi<br>plata</span></span>',
        'groups' => [['title' => 'Tus datos', 'rows' => [
            [$fName], [$fEmail], $docRow,
            [['name' => 'phone', 'type' => 'tel', 'validate' => 'phone', 'icon' => 'phone_iphone', 'placeholder' => 'Celular Daviplata', 'inputmode' => 'tel', 'autocomplete' => 'tel-national', 'maxlength' => 13]],
        ]]],
    ],
    'nequi' => [
        'name' => 'Botón Nequi',
        'short' => 'Nequi',
        'sub' => 'Aprobación desde tu app Nequi',
        'logo' => '<span class="ck-method-logo ck-logo-nequi"><img src="assets/img/nequi-n.webp" alt=""></span>',
        'groups' => [['title' => 'Tus datos', 'rows' => [
            [['name' => 'phone', 'type' => 'tel', 'validate' => 'phone', 'icon' => 'phone_iphone', 'placeholder' => 'Celular Nequi', 'inputmode' => 'tel', 'autocomplete' => 'tel-national', 'maxlength' => 13]],
            [$fName], [$fEmail],
        ]]],
    ],
    'card' => [
        'name' => 'Tarjeta Crédito o Débito',
        'short' => 'tarjeta',
        'sub' => 'Visa, Mastercard y más',
        'logo' => '<span class="ck-method-logo ck-logo-card"><img src="assets/img/mastercard.png" alt=""></span>',
        'groups' => [
            ['title' => 'Datos de la tarjeta', 'rows' => [
                [['name' => 'cardNumber', 'type' => 'text', 'validate' => 'card', 'icon' => 'credit_card', 'placeholder' => 'Número de tarjeta', 'inputmode' => 'numeric', 'autocomplete' => 'cc-number', 'maxlength' => 23]],
                [['name' => 'cardName', 'type' => 'text', 'validate' => 'name', 'icon' => 'person', 'placeholder' => 'Nombre en la tarjeta', 'autocomplete' => 'cc-name']],
                [
                    ['name' => 'cardExp', 'type' => 'text', 'validate' => 'exp', 'icon' => 'calendar_month', 'placeholder' => 'MM/AA', 'inputmode' => 'numeric', 'autocomplete' => 'cc-exp', 'maxlength' => 5],
                    ['name' => 'cardCvv', 'type' => 'password', 'validate' => 'cvv', 'icon' => 'lock', 'placeholder' => 'CVV', 'inputmode' => 'numeric', 'autocomplete' => 'cc-csc', 'maxlength' => 4],
                ],
                [['name' => 'installments', 'type' => 'select', 'validate' => 'select', 'icon' => 'receipt_long', 'placeholder' => 'Cuotas', 'label' => 'Cuotas', 'default' => '1', 'options' => ['1' => '1 cuota', '3' => '3 cuotas', '6' => '6 cuotas', '12' => '12 cuotas', '24' => '24 cuotas']]],
            ]],
            ['title' => 'Tus datos', 'rows' => [[$fEmail], $docRow]],
        ],
    ],
];

// Ocultos por ahora: solo quedan disponibles PSE y Tarjeta. Quita la clave de
// este arreglo para reactivar un método sin perder su configuración.
$hiddenMethods = ['bancolombia', 'daviplata', 'nequi'];
$methods = array_diff_key($methods, array_flip($hiddenMethods));

function ckField(array $f, string $key): string
{
    $e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    $id = $key . '-' . $f['name'];
    $label = $e($f['label'] ?? $f['placeholder']);
    $icon = isset($f['icon']) ? '<span class="material-symbols-outlined ck-input-icon">' . $e($f['icon']) . '</span>' : '';
    $wrapClass = 'ck-input-wrap' . (!empty($f['narrow']) ? ' ck-input-wrap--narrow' : '');

    if ($f['type'] === 'combobox') {
        $options = '';
        foreach ($f['options'] as $value => $text) {
            $options .= '<li class="ck-combobox-option" role="option" data-value="' . $e($value) . '" tabindex="-1">' . $e($text) . '</li>';
        }

        return '<div class="ck-field">'
            . '<div class="ck-combobox" data-combobox>'
            . '<button type="button" class="ck-input ck-combobox-trigger" id="' . $id . '-trigger" aria-haspopup="listbox" aria-expanded="false" aria-label="' . $label . '">'
            . $icon
            . '<span class="ck-combobox-value is-placeholder" data-placeholder="' . $e($f['placeholder']) . '">' . $e($f['placeholder']) . '</span>'
            . '<span class="material-symbols-outlined ck-input-chevron">expand_more</span>'
            . '</button>'
            . '<input type="hidden" id="' . $id . '" name="' . $e($f['name']) . '" data-validate="' . $f['validate'] . '" aria-label="' . $label . '" required>'
            . '<div class="ck-combobox-panel" hidden>'
            . '<div class="ck-combobox-search-wrap"><span class="material-symbols-outlined">search</span>'
            . '<input type="text" class="ck-combobox-search" placeholder="Busca tu banco" autocomplete="off" aria-label="Buscar banco"></div>'
            . '<ul class="ck-combobox-list" role="listbox" aria-labelledby="' . $id . '-trigger">' . $options . '</ul>'
            . '<p class="ck-combobox-empty" hidden>No encontramos ese banco.</p>'
            . '</div>'
            . '</div>'
            . '<span class="ck-field-error" hidden></span>'
            . '</div>';
    }

    if ($f['type'] === 'select') {
        $default = (string) ($f['default'] ?? '');
        $options = '<option value="">' . $e($f['placeholder']) . '</option>';
        foreach ($f['options'] as $value => $text) {
            $options .= '<option value="' . $e($value) . '"' . ((string) $value === $default ? ' selected' : '') . '>' . $e($text) . '</option>';
        }
        $control = '<select class="ck-input" id="' . $id . '" name="' . $e($f['name']) . '" data-validate="' . $f['validate'] . '" aria-label="' . $label . '" required>' . $options . '</select>'
            . '<span class="material-symbols-outlined ck-input-chevron">expand_more</span>';
    } else {
        $control = '<input class="ck-input" id="' . $id . '" name="' . $e($f['name']) . '" type="' . $f['type'] . '" data-validate="' . $f['validate'] . '"'
            . ' inputmode="' . $e($f['inputmode'] ?? 'text') . '" autocomplete="' . $e($f['autocomplete'] ?? 'off') . '"'
            . ' placeholder="' . $e($f['placeholder']) . '" aria-label="' . $label . '"'
            . (isset($f['maxlength']) ? ' maxlength="' . (int) $f['maxlength'] . '"' : '') . ' required>';
    }

    return '<div class="ck-field' . (!empty($f['narrow']) ? ' ck-field--narrow' : '') . '"><div class="' . $wrapClass . '">' . $icon . $control . '</div><span class="ck-field-error" hidden></span></div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>WePago · Checkout</title>
    <meta name="description" content="Elige el medio de pago de tu preferencia y confirma el resumen de tu pago en WePago.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>
<body>
    <header class="ck-header">
        <div class="ck-header-left">
            <a class="ck-back" href="index.php?cart=open" aria-label="Volver al carrito">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <a class="ck-logo" href="index.php" aria-label="WePago">
                <img src="assets/img/wepago-logo.svg" alt="WePago">
            </a>
        </div>
        <a class="ck-cart" href="index.php?cart=open" aria-label="Carrito">
            <span class="material-symbols-outlined">shopping_cart</span>
            <span class="ck-cart-badge" id="ckCartBadge" hidden>0</span>
        </a>
    </header>

    <div class="ck-progress"><div class="ck-progress-fill"></div></div>

    <main class="ck-main">
        <div class="ck-head">
            <h1 class="ck-title">¿Cómo desea <span>pagar?</span></h1>
            <p class="ck-lead">Elija el medio de pago de su preferencia.</p>
        </div>

        <div class="ck-grid" id="ckContent" hidden>
            <section class="ck-methods" aria-labelledby="ckMethodsTitle">
                <div id="ckPicker">
                    <h2 class="ck-methods-title" id="ckMethodsTitle">
                        <span class="material-symbols-outlined">payments</span>
                        Métodos de pago
                    </h2>

                    <div class="ck-method-list" role="radiogroup" aria-labelledby="ckMethodsTitle">
                        <?php foreach ($methods as $key => $method): ?>
                            <div class="ck-method-item">
                                <label class="ck-method">
                                    <input type="radio" name="paymentMethod" value="<?php echo $key; ?>" data-name="<?php echo htmlspecialchars($method['name'], ENT_QUOTES, 'UTF-8'); ?>" data-short="<?php echo htmlspecialchars($method['short'], ENT_QUOTES, 'UTF-8'); ?>" data-sub="<?php echo htmlspecialchars($method['sub'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo $method['logo']; ?>
                                    <span class="ck-method-name"><?php echo htmlspecialchars($method['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="ck-radio" aria-hidden="true"></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="ckChosen" hidden>
                    <div class="ck-chosen-head">
                        <p class="ck-chosen-title">Método de pago</p>
                        <button class="ck-change" id="ckChange" type="button">Cambiar</button>
                    </div>

                    <div class="ck-selected-card" id="ckSelectedCard">
                        <span id="ckSelectedLogo"></span>
                        <div class="ck-selected-info">
                            <p class="ck-selected-name" id="ckSelectedName"></p>
                            <p class="ck-selected-sub" id="ckSelectedSub"></p>
                        </div>
                        <span class="material-symbols-outlined ck-selected-check">check_circle</span>
                    </div>

                    <?php foreach ($methods as $key => $method): ?>
                        <form class="ck-form" data-form="<?php echo $key; ?>" novalidate hidden autocomplete="on" onsubmit="return false;">
                            <?php foreach ($method['groups'] as $group): ?>
                                <div class="ck-group">
                                    <p class="ck-group-title"><?php echo htmlspecialchars($group['title'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php foreach ($group['rows'] as $row): ?>
                                        <div class="ck-row">
                                            <?php foreach ($row as $field): echo ckField($field, $key); endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </form>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="ck-summary" aria-labelledby="ckSummaryTitle">
                <h2 class="ck-summary-title" id="ckSummaryTitle">Resumen de tu pago (<span id="ckCount">0</span>)</h2>
                <ul class="ck-summary-list" id="ckItems"></ul>
                <div class="ck-total">
                    <span class="ck-total-label">Total</span>
                    <span class="ck-total-value" id="ckTotal">$0</span>
                </div>
                <button class="ck-pay" id="ckPayButton" type="button" disabled>Pagar ahora</button>
                <p class="ck-error" id="ckError" role="alert" hidden></p>
            </aside>
        </div>

        <div class="ck-empty" id="ckEmpty" hidden>
            <span class="material-symbols-outlined">shopping_cart</span>
            <h2>Tu carrito está vacío</h2>
            <p>Agrega facturas para continuar con el pago.</p>
            <a class="ck-empty-btn" href="index.php">Explorar comercios</a>
        </div>
    </main>

    <footer class="ck-footer">Pago seguro · cifrado SSL · © Wepago</footer>

    <script src="assets/js/cart-store.js" defer></script>
    <script src="assets/js/checkout.js" defer></script>
</body>
</html>
