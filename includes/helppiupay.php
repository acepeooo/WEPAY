<?php

declare(strict_types=1);

/**
 * Integración con HelppiuPay (cobros PSE).
 *
 * Credenciales del comercio — no sirvas este archivo públicamente ni lo expongas
 * en el frontend. Para producción, considera mover HELPPIUPAY_KEY_ID y
 * HELPPIUPAY_SECRET a variables de entorno en vez de dejarlas en código.
 */
const HELPPIUPAY_KEY_ID = 'hp_sk_live_Z9cxILagdjuaO1wXUCGq1qfS';
const HELPPIUPAY_SECRET = '1P94UirgtSEh44r1ZG1oLBRA1npStKcV4nrtERZLA0XDCJrd';
const HELPPIUPAY_API_BASE = 'https://helppiupay.com/api/v1';

/**
 * Crea un cobro PSE en HelppiuPay.
 *
 * @param array<string, mixed> $payload
 * @return array<string, mixed> Cuerpo decodificado de la respuesta (HTTP 2xx).
 *
 * @throws RuntimeException Si falla la conexión o HelppiuPay rechaza la solicitud.
 */
function helppiupayCreateCharge(array $payload): array
{
    $ch = curl_init(HELPPIUPAY_API_BASE . '/charges');

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . HELPPIUPAY_KEY_ID . ':' . HELPPIUPAY_SECRET,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    ]);

    $raw = curl_exec($ch);

    if ($raw === false) {
        throw new RuntimeException('No se pudo conectar con HelppiuPay: ' . curl_error($ch));
    }

    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    $body = json_decode($raw, true);
    $body = is_array($body) ? $body : [];

    if ($status < 200 || $status >= 300) {
        $message = is_string($body['message'] ?? null) ? $body['message'] : 'HelppiuPay rechazó la solicitud (HTTP ' . $status . ').';
        throw new RuntimeException($message, $status);
    }

    return $body;
}

/**
 * Bancos PSE soportados (code => nombre), según HelppiuPay GET /api/v1/banks/pse.
 * Única fuente de esta lista: la usan tanto el formulario como el endpoint de cobro.
 *
 * @return array<string, string>
 */
function pseBankCodes(): array
{
    return [
        '1001' => 'Banco de Bogotá',
        '1002' => 'Banco Popular',
        '1006' => 'Banco Itaú',
        '1007' => 'Bancolombia',
        '1009' => 'Citibank',
        '1012' => 'GNB Sudameris',
        '1013' => 'BBVA Colombia',
        '1019' => 'DAVIbank S.A. (Scotiabank Colpatria)',
        '1023' => 'Banco de Occidente',
        '1032' => 'Banco Caja Social',
        '1040' => 'Banco Agrario',
        '1047' => 'Banco Mundo Mujer',
        '1059' => 'Bancamia',
        '1060' => 'Banco Pichincha',
        '1061' => 'Bancoomeva',
        '1062' => 'Banco Falabella',
        '1063' => 'Banco Finandina',
        '1065' => 'Banco Santander Colombia',
        '1066' => 'Coopcentral',
        '1069' => 'Banco Serfinanza',
        '1070' => 'Lulo Bank',
        '1071' => 'J.P. Morgan Colombia',
        '1283' => 'CFA Cooperativa Financiera',
        '1286' => 'JFK Cooperativa Financiera',
        '1289' => 'Cotrafa',
        '1292' => 'Confiar Cooperativa Financiera',
        '1303' => 'Banco Unión',
        '1370' => 'Coltefinanciera',
        '1097' => 'Dale',
        '1551' => 'Daviplata',
        '1558' => 'BAN100',
        '1637' => 'Iris',
        '1121' => 'Financiera Juriscoop',
        '1801' => 'Movii',
        '1802' => 'Ding',
        '1803' => 'Powwi',
        '1804' => 'Uala',
        '1808' => 'Bold CF',
        '1809' => 'Nu',
        '1811' => 'RappiPay',
        '1812' => 'Coink',
        '1814' => 'Global66',
        '1815' => 'Alianza Fiduciaria',
        '1816' => 'Crezcamos',
        '1819' => 'Banco Contactar',
        '1824' => 'Paycash',
        '1831' => 'Acción Fiduciaria',
        '1507' => 'Nequi',
        '1051' => 'Davivienda',
        '1052' => 'AV Villas',
    ];
}
