<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helppiupay.php';

header('Content-Type: application/json; charset=UTF-8');

function pseFail(string $message, int $status = 400): void
{
    http_response_code($status);
    echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    pseFail('Método no permitido.', 405);
}

$input = json_decode((string) file_get_contents('php://input'), true);

if (!is_array($input)) {
    pseFail('Solicitud inválida.');
}

$amount = (int) round((float) ($input['amount'] ?? 0));
$bankCode = trim((string) ($input['bankCode'] ?? ''));
$personType = (string) ($input['personType'] ?? '');
$docType = trim((string) ($input['docType'] ?? ''));
$docNumber = preg_replace('/\D/', '', (string) ($input['docNumber'] ?? '')) ?? '';
$fullName = trim((string) ($input['fullName'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$phone = preg_replace('/\D/', '', (string) ($input['phone'] ?? '')) ?? '';

$bankNames = pseBankCodes();

if ($amount < 1000) {
    pseFail('El valor a pagar no es válido.');
}
if (!isset($bankNames[$bankCode])) {
    pseFail('Selecciona un banco válido.');
}
if (!in_array($personType, ['0', '1'], true)) {
    pseFail('Selecciona el tipo de persona.');
}
if (!in_array($docType, ['CC', 'CE', 'NIT', 'PP', 'TI'], true)) {
    pseFail('Selecciona un tipo de documento válido.');
}
if ($docNumber === '' || strlen($docNumber) < 5 || strlen($docNumber) > 15) {
    pseFail('Ingresa un número de documento válido.');
}
if ($fullName === '' || mb_strlen($fullName) < 3) {
    pseFail('Ingresa tu nombre completo.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    pseFail('Ingresa un correo válido.');
}
if (strlen($phone) !== 10 || $phone[0] !== '3') {
    pseFail('Ingresa un celular colombiano válido.');
}

$reference = 'WP' . date('ymdHis') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

$payload = [
    'reference' => $reference,
    'amount' => $amount,
    'currency' => 'COP',
    'customer' => [
        'name' => $fullName,
        'email' => $email,
        'document' => $docNumber,
        'phone' => $phone,
    ],
    'payment_method' => [
        'type' => 'pse',
        'bank_code' => $bankCode,
        'person_type' => $personType,
        'document_type' => $docType,
    ],
];

try {
    $charge = helppiupayCreateCharge($payload);
    $redirectUrl = $charge['next_action']['redirect_url'] ?? null;

    if (!is_string($redirectUrl) || $redirectUrl === '') {
        throw new RuntimeException('HelppiuPay no devolvió una URL de pago.');
    }

    @file_put_contents(
        __DIR__ . '/../data/pse_orders.log',
        json_encode(['ts' => date('c'), 'reference' => $reference, 'amount' => $amount, 'bank' => $bankCode, 'success' => true], JSON_UNESCAPED_UNICODE) . "\n",
        FILE_APPEND
    );

    echo json_encode([
        'success' => true,
        'reference' => $reference,
        'redirectUrl' => $redirectUrl,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    @file_put_contents(
        __DIR__ . '/../data/pse_orders.log',
        json_encode(['ts' => date('c'), 'reference' => $reference, 'amount' => $amount, 'bank' => $bankCode, 'success' => false, 'error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE) . "\n",
        FILE_APPEND
    );

    $status = $exception->getCode() >= 400 && $exception->getCode() < 600 ? $exception->getCode() : 502;
    pseFail('No pudimos iniciar tu pago PSE. Intenta de nuevo en unos minutos.', $status);
}
