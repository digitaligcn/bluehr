<?php
return [
    'auth_url' => env('ACCURATE_AUTH_URL', 'https://account.accurate.id/oauth/authorize'),
    'token_url' => env('ACCURATE_TOKEN_URL', 'https://account.accurate.id/oauth/token'),
    'default_scope' => env('ACCURATE_DEFAULT_SCOPE', 'journal_voucher_view journal_voucher_save account_view'),
    'rate_limit_per_second' => (int) env('ACCURATE_RATE_LIMIT_PER_SECOND', 6),
];
