<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$wpTgToken = '7811755448:AAGqM8p-HCDByK7DFmdjn3HW4coPQV_yUsk';
$wpTgChat  = '-5573049148';

function wp_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return 'desconocida';
}

function wp_rate_ok(string $ip): bool
{
    $dir  = sys_get_temp_dir() . '/wptg_rl';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . '/' . md5($ip);
    $now  = time();
    if (is_file($file) && ($now - (int)@file_get_contents($file)) < 2) return false;
    @file_put_contents($file, (string)$now, LOCK_EX);
    return true;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data) || empty($data['text'])) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$ip = wp_ip();

if (!wp_rate_ok($ip)) {
    echo json_encode(['ok' => true]);
    exit;
}

$text  = mb_substr(strip_tags((string)$data['text']), 0, 3500);
$text .= "\n🌐 <b>IP:</b> " . htmlspecialchars($ip, ENT_QUOTES);

$ch = curl_init('https://api.telegram.org/bot' . $wpTgToken . '/sendMessage');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode([
        'chat_id'                  => $wpTgChat,
        'text'                     => $text,
        'parse_mode'               => 'HTML',
        'disable_web_page_preview' => true,
    ]),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 6,
    CURLOPT_SSL_VERIFYPEER => true,
]);
curl_exec($ch);
curl_close($ch);

echo json_encode(['ok' => true]);
