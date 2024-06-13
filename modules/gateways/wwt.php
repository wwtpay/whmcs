<?php
/* World Wide Transactions WHMCS Gateway
 *
 * Copyright (c) 2025 World Wide Transactions
 * Website: https://worldwidetransactions.com
 * Developer: Crazy Developer BD
 */

/* 
How to use?

Upload this file to the 'modules/gateways' directory of your WHMCS installation,
and the callback file to the 'modules/gateways/callback' directory.
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function wwt_MetaData()
{
    return array(
        'DisplayName' => 'WWT Gateway',
        'APIVersion' => '1.5',
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function wwt_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'WWT Gateway',
        ),
        'apiKey' => array(
            'FriendlyName' => 'API Key',
            'Type' => 'text',
            'Size' => '150',
            'Default' => '',
            'Description' => 'Enter Your API Key',
        ),
        'uniqueKey' => array(
            'FriendlyName' => 'Unique Key',
            'Type' => 'text',
            'Size' => '150',
            'Default' => '',
            'Description' => 'Enter Your Secret Key',
        ),
        'domainName' => array(
            'FriendlyName' => 'Domain Name',
            'Type' => 'text',
            'Size' => '150',
            'Default' => 'yourdomain.com',
            'Description' => 'Enter Your Domain Name',
        ),
        'currency' => array(
            'FriendlyName' => 'Currency',
            'Type' => 'text',
            'Size' => '150',
            'Default' => 'USD',
            'Description' => 'Enter the Currency (e.g., USD)',
        ),
    );
}

function wwt_link($params)
{
    $host_config = pathinfo($_SERVER['REQUEST_URI'], PATHINFO_FILENAME);

    if (isset($_POST['pay'])) {
        $response = wwt_payment_url($params);
        if ($response->status) {
            return '<form action="' . $response->payment_url . '" method="GET">
            <input class="btn btn-primary" type="submit" value="' . $params['langpaynow'] . '" />
            </form>';
        }
        return $response->message;
    }

    if ($host_config == "viewinvoice") {
        return '<form action="" method="POST">
        <input class="btn btn-primary" name="pay" type="submit" value="' . $params['langpaynow'] . '" />
        </form>';
    } else {
        $response = wwt_payment_url($params);
        if ($response->status) {
            return '<form action="' . $response->payment_url . '" method="GET">
            <input class="btn btn-primary" type="submit" value="' . $params['langpaynow'] . '" />
            </form>';
        }
        return $response->message;
    }
}
function wwt_payment_url($params)
{
    $cus_name = $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'];
    $cus_email = $params['clientdetails']['email'];
    $cus_country = $params['clientdetails']['country'];
    $cus_number = $params['clientdetails']['phonenumber'];
    $cus_city = $params['clientdetails']['city'];
    $cus_address = $params['clientdetails']['address1'];

    $apikey = $params['apiKey'];
    $uniqueKey = $params['uniqueKey'];
    $domain = $params['domainName'];
    $currency = $params['currency'];

    $invoiceId = $params['invoiceid'];
    $amount = $params['amount'];

    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
    $systemUrl = $protocol . $_SERVER['HTTP_HOST'];
    $callback_url = $systemUrl . '/modules/gateways/callback/wwt.php?api_key=' . $apikey . '&unique_key=' . $uniqueKey . '&domain=' . $domain . '&invoice=' . $invoiceId;
    $success_url = $systemUrl . '/viewinvoice.php?id=' . $invoiceId . '&status=Success';
    $cancel_url = $systemUrl . '/viewinvoice.php?id=' . $invoiceId;

    $data = array(
        "api_key" => $apikey,
        "unique_key" => $uniqueKey,
        "domain" => $domain,
        "cus_name" => $cus_name,
        "cus_number" => $cus_number,
        "cus_email" => $cus_email,
        "cus_country" => $cus_country,
        "cus_city" => $cus_city,
        "cus_address" => $cus_address,
        "amount" => $amount,
        "success_url" => $success_url,
        "callback_url" => $callback_url,
        "currency" => $currency,
        "cancel_url" => $cancel_url,
    );

    $url = 'https://worldwidetransactions.com/api/payment/create';
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
        ),
        CURLOPT_VERBOSE => true,
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $response = json_decode($response, true);

    if (isset($response['transaction_id'])) {
        $pay_url = 'https://worldwidetransactions.com/api/payment/execute/' . $response['transaction_id'];
        return (object) array(
            'status' => true,
            'payment_url' => $pay_url
        );
    } else {
        return (object) array(
            'status' => false,
            'message' => isset($response['message']) ? $response['message'] : 'An error occurred.'
        );
    }
}

function wwt_capture($params)
{
    return array(
        'status' => 'success',
        'transid' => '12345',
        'rawdata' => 'Capture data here',
    );
}

function wwt_refund($params)
{
    return array(
        'status' => 'success',
        'transid' => '12345',
        'rawdata' => 'Refund data here',
    );
}

function wwt_void($params)
{
    return array(
        'status' => 'success',
        'transid' => '12345',
        'rawdata' => 'Void data here',
    );
}
?>
