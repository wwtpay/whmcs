<?php
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

use WHMCS\Config\Setting;

$invoiceId = $_REQUEST['invoice'];
$transactionId = $_POST['transaction_id'];
$paymentAmount = $_REQUEST['amount'];
$paymentFee = $_REQUEST['charge'];
$gatewayModuleName = "wwt";

$api_key = $_REQUEST['api_key'];
$unique_key = $_REQUEST['unique_key'];
$domain = $_REQUEST['domain'];

$verifyUrl = 'https://worldwidetransactions.com/api/payment/verify';
$data = [
    "transactionId" => $transactionId,
    "api_key" => $api_key,
    "unique_key" => $unique_key,
    "domain" => $domain,
];

$curl = curl_init($verifyUrl);

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
    ],
]);

$response = curl_exec($curl);

if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    curl_close($curl);
    die('cURL error: ' . $error_msg);
}

curl_close($curl);

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'JSON error: ' . json_last_error_msg() . '<br>';
    echo 'Raw response: ' . htmlspecialchars($response);
    exit;
}

if ($data['status'] == 'Completed') {
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );

    echo 'Payment Success';
} else {
    echo 'Failed. Id Not Match<br>';
    echo 'Response Data: ' . print_r($data, true) . '<br>';
    echo 'Request Data: ' . json_encode($_POST);
}
?>
