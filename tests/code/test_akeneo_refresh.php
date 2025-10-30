<?php
// test_akeneo_refresh.php

require __DIR__ . '/../../vendor/autoload.php';

$username = '';
$password = '';
$clientId = '';
$secret = '';
$domain = '';

// --- Test the refresh function ---
$account = new \Misery\Component\Akeneo\Client\AkeneoApiClientAccount(
    $username,
    $password,
    $clientId,
    $secret
);
$client = new \Misery\Component\Common\Client\GuzzleApiClient(
    $domain
);

try {
    $client->authorize($account);
    $client->refreshToken();
} catch (Exception $e) {
    echo "Refresh failed: " . $e->getMessage() . "\n";
}
