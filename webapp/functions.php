<?php
require_once __DIR__.'/config.php';

/** Kết nối MySQL (PDO) */
function db(): PDO {
  static $pdo;
  if (!$pdo) {
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }
  return $pdo;
}

/**
 * Gửi record JSON lên Kafka topic qua REST Proxy.
 * - Nếu $value là JSON hợp lệ -> gửi dạng object/array (đúng spec)
 * - Nếu $value là chuỗi thường   -> gửi nguyên chuỗi
 * Trả về: ['ok'=>bool, 'msg'=>string, 'raw'=>string?]
 */
function send_to_kafka(string $topic, ?string $key, string $value): array {
  $url = rtrim(KAFKA_REST_PROXY, '/') . '/topics/' . rawurlencode($topic);

  // Tự phát hiện JSON để tránh double-encode
  $decoded = json_decode($value, true);
  $isJson  = (json_last_error() === JSON_ERROR_NONE);
  $recordValue = $isJson ? $decoded : $value;

  $payload = [
    'records' => [[
      'key'   => ($key === '' ? null : $key),
      'value' => $recordValue,
    ]],
  ];

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
      // Gửi/nhận JSON kiểu REST Proxy
      'Content-Type: application/vnd.kafka.json.v2+json',
      'Accept: application/vnd.kafka.v2+json',
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT        => 12,
  ]);

  $resp = curl_exec($ch);
  $curlErr = curl_error($ch);
  $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
  curl_close($ch);

  if ($curlErr) {
    return ['ok' => false, 'msg' => "cURL error: $curlErr"];
  }
  if ($code >= 200 && $code < 300) {
    return ['ok' => true, 'msg' => 'sent', 'raw' => $resp];
  }

  // Trả lỗi có kèm body để dễ debug (thường thấy 404/409/422 nếu sai topic/format)
  return ['ok' => false, 'msg' => "HTTP $code: $resp"];
}

/** Gọi GET tới REST Proxy (dùng cho trang admin/list topics) */
function kafka_get(string $path): array {
  $url = rtrim(KAFKA_REST_PROXY, '/') . '/' . ltrim($path, '/');

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPGET        => true,
    CURLOPT_HTTPHEADER     => ['Accept: application/vnd.kafka.v2+json'],
    CURLOPT_TIMEOUT        => 8,
  ]);

  $resp = curl_exec($ch);
  $curlErr = curl_error($ch);
  $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
  curl_close($ch);

  if ($curlErr) {
    return ['ok' => false, 'status' => $code, 'body' => "cURL error: $curlErr"];
  }
  return ['ok' => ($code >= 200 && $code < 300), 'status' => $code, 'body' => $resp];
}
