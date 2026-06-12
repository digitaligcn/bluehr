<?php
namespace BlueHR\Services\Accurate;
class AccurateApiClient {
    public function __construct(private string $accessToken, private string $baseUrl) {}
    public function post(string $endpoint, array $payload = []): array {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->accessToken, 'Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_TIMEOUT => 90,
        ]);
        $raw = curl_exec($ch);
        if ($raw === false) throw new \RuntimeException(curl_error($ch));
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $headers = substr($raw, 0, $headerSize);
        $body = substr($raw, $headerSize);
        return ['ok'=>$status >= 200 && $status < 300, 'status'=>$status, 'headers'=>$headers, 'body'=>$body, 'json'=>json_decode($body, true)];
    }
}
