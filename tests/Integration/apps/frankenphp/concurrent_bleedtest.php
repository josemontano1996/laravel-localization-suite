#!/usr/bin/env php
<?php

// Usage: php concurrent_bleedtest.php
// Requires: ext-curl, ext-json

$locales = [
    'en',
    'es',
    'fr',
];

$options = getopt("t:c:", ["total:", "concurrency:"]);
$total_requests = (int) ($options['t'] ?? $options['total'] ?? 100);
$concurrency = (int) ($options['c'] ?? $options['concurrency'] ?? 50);

$delay_ms = 100; // Delay between firing each request
$sleep_ms = $delay_ms * 3; // Time the server will sleep to ensure overlap

// Default port for Sail internal requests is 80
$endpoint = 'http://localhost:80/%s/bleed-test-native-laravel'; 

$results = [];
$handles = [];
$multi = curl_multi_init();

function make_handle($locale, $sleep_ms)
{
    global $endpoint;
    $url = sprintf($endpoint, $locale) . "?sleep=$sleep_ms";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    return $ch;
}

echo "--------------------------------------------------\n";
echo "Starting high-concurrency bleed test...\n";
echo "Endpoint: $endpoint\n";
echo "Delay between requests: {$delay_ms}ms, Server Sleep: {$sleep_ms}ms\n";
echo "Total Requests: $total_requests, Max Concurrency: $concurrency\n";
echo "--------------------------------------------------\n";

$start_time = microtime(true);

// Prepare request list
$pending = [];
for ($i = 0; $i < $total_requests; $i++) {
    $pending[] = $locales[$i % count($locales)];
}

$active = null;
$in_flight = [];

while (count($pending) > 0 || count($in_flight) > 0) {
    // Fill up to concurrency
    while (count($in_flight) < $concurrency && count($pending) > 0) {
        $locale = array_shift($pending);
        $ch = make_handle($locale, $sleep_ms);
        
        curl_multi_add_handle($multi, $ch);
        $in_flight[(int) $ch] = ['locale' => $locale];
        
        // Precise delay between firing requests as requested
        if (count($pending) > 0) {
            usleep($delay_ms * 1000);
        }
    }

    do {
        $status = curl_multi_exec($multi, $active);
    } while ($status === CURLM_CALL_MULTI_PERFORM);

    // Read completed
    while ($info = curl_multi_info_read($multi)) {
        $ch = $info['handle'];
        $key = (int) $ch;
        
        $locale = $in_flight[$key]['locale'];
        $response = curl_multi_getcontent($ch);
        $data = json_decode($response, true);
        
        $results[] = [
            'locale' => $locale,
            'response' => $data,
            'raw' => $response,
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'curl_error' => curl_error($ch),
        ];
        
        curl_multi_remove_handle($multi, $ch);
        curl_close($ch);
        unset($in_flight[$key]);
    }

    if ($active) {
        curl_multi_select($multi, 0.05);
    }
}
curl_multi_close($multi);

$duration = round(microtime(true) - $start_time, 2);

// Analyze results
$bleeds = 0;
$errors = 0;
foreach ($results as $i => $result) {
    $locale = $result['locale'];
    $data = $result['response'];
    $response = $result['raw'];
    $http_code = $result['http_code'];

    if ($data && isset($data['bleeded'])) {
        $requested = $locale;
        $actual = $data['context_locale'] ?? 'unknown';
        
        if ($data['bleeded']) {
            echo "[BLEED] $requested: context_locale=$actual (expected $requested)\n";
            $bleeds++;
        }
    } else {
        $snippet = substr($response, 0, 100);
        $curl_err = $result['curl_error'] ?? 'Unknown curl error';
        echo "[ERROR] Request $i ($locale): Invalid response (HTTP $http_code), Curl Error: $curl_err, Snippet: $snippet...\n";
        $errors++;
    }
}

$total = count($results);
echo "\n--------------------------------------------------\n";
echo "Summary: $total requests, $bleeds bleeds detected, $errors errors.\n";
echo "Total Time: {$duration}s\n";
if ($bleeds === 0 && $errors === 0) {
    echo "SUCCESS: No locale bleed detected.\n";
} else {
    echo "WARNING: Check for bleeding or connection issues!\n";
}
echo "--------------------------------------------------\n";
