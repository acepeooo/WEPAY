<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$payload = json_decode(file_get_contents('php://input') ?: '', true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Payload inválido.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$_SESSION['selected_merchant'] = [
    'id' => trim((string)($payload['id'] ?? '')),
    'name' => trim((string)($payload['name'] ?? '')),
    'city' => trim((string)($payload['city'] ?? '')),
    'logo' => trim((string)($payload['logo'] ?? '')),
    'referenceLabel' => trim((string)($payload['referenceLabel'] ?? '')),
    'referencePlaceholder' => trim((string)($payload['referencePlaceholder'] ?? '')),
];

echo json_encode([
    'success' => true,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
