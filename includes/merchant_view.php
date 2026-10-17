<?php

require_once __DIR__ . '/merchant_helpers.php';

function getSelectedMerchantFromSession(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $merchant = $_SESSION['selected_merchant'] ?? null;
    return is_array($merchant) ? $merchant : [];
}

function getMerchantViewPlaceholder(string $merchantName): string
{
    $initial = strtoupper(mb_substr($merchantName !== '' ? $merchantName : 'M', 0, 1, 'UTF-8'));
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80">
  <rect width="80" height="80" rx="24" fill="#1fa9e6"/>
  <text x="40" y="49" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="36" font-weight="700" fill="#ffffff">{$initial}</text>
</svg>
SVG;

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

function getMerchantViewData(array $query): array
{
    $sessionMerchant = getSelectedMerchantFromSession();

    $merchantName = trim((string)($sessionMerchant['name'] ?? $query['name'] ?? 'Movistar'));
    $merchantCity = trim((string)($sessionMerchant['city'] ?? $query['city'] ?? 'Bogotá D.C.'));
    $merchantLogo = trim((string)($sessionMerchant['logo'] ?? $query['logo'] ?? ''));
    $merchantId = trim((string)($sessionMerchant['id'] ?? $query['id'] ?? ''));
    $referenceMeta = inferReferenceMeta([
        'name' => $merchantName,
        'description' => (string)($sessionMerchant['description'] ?? $query['description'] ?? ''),
        'placeholder' => (string)($sessionMerchant['referenceLabel'] ?? $query['referenceLabel'] ?? ''),
        'referencePlaceholder' => (string)($sessionMerchant['referencePlaceholder'] ?? $query['referencePlaceholder'] ?? ''),
    ]);

    if ($merchantLogo === '') {
        $merchantLogo = getMerchantViewPlaceholder($merchantName);
    }

    return [
        'name' => $merchantName,
        'city' => $merchantCity,
        'logo' => $merchantLogo,
        'id' => $merchantId,
        'referenceLabel' => $referenceMeta['referenceLabel'],
        'referencePlaceholder' => $referenceMeta['referencePlaceholder'],
    ];
}
