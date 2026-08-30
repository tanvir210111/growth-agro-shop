<?php

$cookieFile = __DIR__ . '/cc_remote_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

// 1. GET login page
$ch = curl_init('https://captaincrown.com/admin/login');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    CURLOPT_SSL_VERIFYPEER => false,
]);
$loginHtml = curl_exec($ch);
file_put_contents(__DIR__ . '/cc_login_page.html', $loginHtml);
echo "Saved login page (" . strlen($loginHtml) . " bytes)\n";

preg_match('/<input[^>]+name="_token"[^>]+value="([^"]+)"/i', $loginHtml, $m1);
if (empty($m1)) {
    preg_match('/<input[^>]+value="([^"]+)"[^>]+name="_token"/i', $loginHtml, $m1);
}
$token = $m1[1] ?? '';
echo "Extracted Token: $token\n";

if ($token) {
    // 2. POST login
    $ch2 = curl_init('https://captaincrown.com/admin/login');
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            '_token' => $token,
            'email' => 'captaincrown@admin.com',
            'password' => 'Aziz625713',
        ]),
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $dashHtml = curl_exec($ch2);
    $url = curl_getinfo($ch2, CURLINFO_EFFECTIVE_URL);
    echo "Final URL: $url\n";
    file_put_contents(__DIR__ . '/cc_dashboard_raw.html', $dashHtml);
    echo "Saved Dashboard HTML (" . strlen($dashHtml) . " bytes)\n";
}
