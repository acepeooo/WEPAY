<?php

function getMerchantPlaceholder(): string
{
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
  <defs>
    <linearGradient id="g" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0%" stop-color="#eff4ff"/>
      <stop offset="100%" stop-color="#d9e5ff"/>
    </linearGradient>
  </defs>
  <rect width="64" height="64" rx="18" fill="url(#g)"/>
  <path d="M18 40.5c0-7.46 6.15-13.5 13.74-13.5 3.82 0 7.23 1.37 9.75 3.67v-3.37h10.87V59.4H41.49v-3.1c-2.52 2.3-5.93 3.67-9.75 3.67C24.15 59.97 18 53.93 18 46.47V40.5Z" fill="#2f5bea"/>
  <circle cx="47.5" cy="19.5" r="4.5" fill="#1c3faa"/>
</svg>
SVG;

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

function formatMerchantCity(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return 'Colombia';
    }

    if (function_exists('mb_convert_case')) {
        return mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    return ucwords(strtolower($value));
}

function inferReferenceMeta(array $item): array
{
    $explicitLabel = trim((string)($item['placeholder'] ?? $item['referenceLabel'] ?? ''));
    $explicitPlaceholder = trim((string)($item['referencePlaceholder'] ?? $item['fixedPlaceholder'] ?? ''));

    if ($explicitLabel !== '') {
        return [
            'referenceLabel' => strtoupper($explicitLabel),
            'referencePlaceholder' => $explicitPlaceholder !== '' ? $explicitPlaceholder : 'Ej: 1234567890',
        ];
    }

    $name = mb_strtolower((string)($item['name'] ?? $item['merchantName'] ?? $item['title'] ?? ''), 'UTF-8');
    $description = mb_strtolower((string)($item['description'] ?? ''), 'UTF-8');
    $categoryKey = mb_strtoupper((string)($item['category']['key'] ?? ''), 'UTF-8');
    $haystack = $name . ' ' . $description;

    $hasAny = static function (string $text, array $needles): bool {
        foreach ($needles as $needle) {
            if (mb_strpos($text, $needle) !== false) {
                return true;
            }
        }
        return false;
    };

    if ($categoryKey === 'ADMINISTRATION_FEES' || $hasAny($haystack, ['apartamento', 'urbanizacion', 'urbanización', 'conjunto', 'condominio', 'ph', 'propiedad horizontal'])) {
        return [
            'referenceLabel' => 'NUMERO DE APARTAMENTO',
            'referencePlaceholder' => 'Ej: Torre 2 Apto 304',
        ];
    }

    if ($categoryKey === 'HEALTH' || $hasAny($haystack, ['consultorio', 'clinica', 'clínica', 'ips', 'local'])) {
        return [
            'referenceLabel' => 'CONSULTORIO O LOCAL',
            'referencePlaceholder' => 'Ej: Consultorio 204',
        ];
    }

    if ($hasAny($haystack, ['nit', 'sas', 's.a.s', 'sa', 's.a', 'empresa'])) {
        return [
            'referenceLabel' => 'NIT',
            'referencePlaceholder' => 'Ej: 900123456',
        ];
    }

    return [
        'referenceLabel' => 'CUENTA O REF DE PAGO',
        'referencePlaceholder' => 'Ej: 1234567890',
    ];
}

function normalizeMerchant(array $item): array
{
    $logoFields = [
        'logo', 'logoUrl', 'image', 'imageUrl', 'providerLogo', 'merchantLogo',
        'picture', 'avatar', 'icon', 'thumbnail',
    ];

    $logo = '';
    foreach ($logoFields as $field) {
        if (!empty($item[$field]) && is_string($item[$field])) {
            $logo = trim($item[$field]);
            break;
        }
    }

    if ($logo === '') {
        $logo = getMerchantPlaceholder();
    }

    $referenceMeta = inferReferenceMeta($item);

    return [
        'id' => (string)($item['id'] ?? $item['entityId'] ?? $item['reference'] ?? md5(json_encode($item))),
        'name' => (string)($item['name'] ?? $item['merchantName'] ?? $item['title'] ?? 'Comercio sin nombre'),
        'city' => formatMerchantCity((string)($item['city'] ?? $item['municipality'] ?? $item['location'] ?? $item['department'] ?? 'Colombia')),
        'logo' => $logo,
        'referenceLabel' => $referenceMeta['referenceLabel'],
        'referencePlaceholder' => $referenceMeta['referencePlaceholder'],
    ];
}

function merchantStepTwoUrl(array $merchant): string
{
    return 'pasodos.php';
}

function merchantCardMarkup(array $merchant): string
{
    $id = htmlspecialchars((string)($merchant['id'] ?? ''), ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars($merchant['name'], ENT_QUOTES, 'UTF-8');
    $city = htmlspecialchars($merchant['city'], ENT_QUOTES, 'UTF-8');
    $logo = htmlspecialchars($merchant['logo'], ENT_QUOTES, 'UTF-8');
    $referenceLabel = htmlspecialchars((string)($merchant['referenceLabel'] ?? 'CUENTA O REF DE PAGO'), ENT_QUOTES, 'UTF-8');
    $referencePlaceholder = htmlspecialchars((string)($merchant['referencePlaceholder'] ?? 'Ej: 1234567890'), ENT_QUOTES, 'UTF-8');
    $url = htmlspecialchars(merchantStepTwoUrl($merchant), ENT_QUOTES, 'UTF-8');

    return <<<HTML
<a
    class="merchant-card"
    href="{$url}"
    aria-label="{$name}"
    data-merchant-id="{$id}"
    data-merchant-name="{$name}"
    data-merchant-city="{$city}"
    data-merchant-logo="{$logo}"
    data-merchant-reference-label="{$referenceLabel}"
    data-merchant-reference-placeholder="{$referencePlaceholder}"
>
    <img class="merchant-logo" src="{$logo}" alt="{$name}" loading="lazy">
    <h3 class="merchant-name">{$name}</h3>
    <p class="merchant-city">
        <span class="material-symbols-outlined">location_on</span>
        {$city}
    </p>
</a>
HTML;
}
