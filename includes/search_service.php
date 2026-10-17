<?php

function wepagoFetchPage(string $search, ?string $cursor = null, int $limit = 25): array
{
    $params = ['limit' => $limit, 'search' => $search];
    if ($cursor !== null) {
        $params['cursor'] = $cursor;
    }

    $url = 'https://azqoo42osk.execute-api.us-east-1.amazonaws.com/entities?' . http_build_query($params);
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_TIMEOUT => 30,
    ]);

    $body = curl_exec($ch);
    if ($body === false) {
        $error = curl_error($ch);
        throw new RuntimeException('curl error: ' . $error);
    }

    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $json = json_decode($body, true);

    if ($status >= 400 || !is_array($json) || empty($json['success']) || !isset($json['query']) || !is_array($json['query'])) {
        throw new RuntimeException('Respuesta inválida de la API.');
    }

    return $json['query'];
}
