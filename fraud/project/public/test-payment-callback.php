<?php
/**
 * Payment Callback Test Script
 * URL: http://localhost/test-payment-callback.php
 */

// Simulate Payment Gateway Callback
$testData = [
    'invoice_id' => 'TEST-' . time(),
    'status' => 'COMPLETED',
    'amount' => 2500,
];

echo "<h2>🧪 Payment Callback Test</h2>";
echo "<p>Simulating payment gateway callback...</p>";

echo "<h3>Test Data:</h3>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Test GET Request
$getUrl = "http://localhost/chatbot-payment/success?invoice_id=" . $testData['invoice_id'];
echo "<h3>GET Request Test:</h3>";
echo "<a href='$getUrl' target='_blank'>$getUrl</a><br><br>";

// Test POST Request
echo "<h3>POST Request Test:</h3>";
echo "<form method='POST' action='/chatbot-payment/success'>";
echo "<input type='hidden' name='invoice_id' value='{$testData['invoice_id']}'>";
echo "<button type='submit'>Send POST Request</button>";
echo "</form>";

echo "<hr>";
echo "<h3>📋 Log Location:</h3>";
echo "<code>C:\\xampp\\htdocs\\project\\storage\\logs\\laravel-" . date('Y-m-d') . ".log</code>";

echo "<hr>";
echo "<h3>✅ CSRF Token Fix Applied:</h3>";
echo "<ul>";
echo "<li>✅ chatbot-payment/success</li>";
echo "<li>✅ chatbot-payment/cancel</li>";
echo "<li>✅ chatbot-payment/webhook</li>";
echo "</ul>";
?>
