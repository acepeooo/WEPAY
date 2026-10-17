<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/merchant_helpers.php';
require_once __DIR__ . '/../includes/search_service.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $search = trim((string)($_GET['q'] ?? ''));
    $cursor = trim((string)($_GET['cursor'] ?? ''));
    $limit = (int)($_GET['limit'] ?? 25);
    $limit = max(1, min(25, $limit));

    if (mb_strlen($search) < 2) {
        echo json_encode([
            'success' => true,
            'items' => [],
            'count' => 0,
            'pageCount' => 0,
            'cursor' => null,
            'hasMore' => false,
            'limit' => $limit,
            'message' => 'Escribe al menos 2 caracteres.',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $page = wepagoFetchPage($search, $cursor !== '' ? $cursor : null, $limit);
    $results = array_map('normalizeMerchant', $page['data'] ?? []);

    echo json_encode([
        'success' => true,
        'items' => $results,
        'count' => count($results),
        'pageCount' => count($results),
        'cursor' => $page['cursor'] ?? null,
        'hasMore' => (bool)($page['hasMore'] ?? false),
        'limit' => $limit,
        'message' => count($results) > 0 ? null : 'No encontramos comercios para tu búsqueda.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'items' => [],
        'count' => 0,
        'pageCount' => 0,
        'cursor' => null,
        'hasMore' => false,
        'message' => 'No fue posible consultar la API en este momento.',
        'error' => $exception->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
