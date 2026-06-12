<?php
namespace BlueHR\Services\Accurate;
use BlueHR\Core\Database;
use BlueHR\Services\Security\Crypto;
class AccurateOAuthService {
    public function authorizationUrl(array $config): string {
        $_SESSION['accurate_oauth_state'] = bin2hex(random_bytes(16));
        return config('accurate.auth_url') . '?' . http_build_query([
            'client_id' => $config['client_id'],
            'response_type' => 'code',
            'redirect_uri' => $config['redirect_uri'],
            'scope' => $config['scope'],
            'state' => $_SESSION['accurate_oauth_state'],
        ]);
    }
    public function exchangeCode(array $connection, string $code): array {
        $secret = Crypto::decrypt($connection['client_secret_encrypted']);
        $ch = curl_init(config('accurate.token_url'));
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Basic ' . base64_encode($connection['client_id'] . ':' . $secret), 'Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query(['grant_type'=>'authorization_code','code'=>$code,'redirect_uri'=>$connection['redirect_uri']]),
            CURLOPT_TIMEOUT => 60,
        ]);
        $body = curl_exec($ch);
        if ($body === false) throw new \RuntimeException(curl_error($ch));
        curl_close($ch);
        $json = json_decode($body, true);
        if (!isset($json['access_token'])) throw new \RuntimeException('Invalid token response: ' . $body);
        Database::exec('UPDATE accurate_connections SET status=?, accurate_user_email=?, connected_at=?, updated_at=? WHERE id=?', ['connected', $json['user']['email'] ?? null, now(), now(), $connection['id']]);
        Database::insert('INSERT INTO accurate_tokens(connection_id, access_token_encrypted, refresh_token_encrypted, token_type, expires_at, last_refreshed_at, created_at) VALUES(?,?,?,?,?,?,?)', [
            $connection['id'], Crypto::encrypt($json['access_token']), Crypto::encrypt($json['refresh_token'] ?? ''), $json['token_type'] ?? 'bearer', date('Y-m-d H:i:s', strtotime('+14 days')), now(), now()
        ]);
        return $json;
    }
}
