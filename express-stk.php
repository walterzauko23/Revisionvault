<?php
// express-stk.php
require_once 'config.php';

$phone = $_POST['phone_number'];
$amount = (int)$_POST['amount'];
$email = $_POST['email'];
$orderNo = 'ORD_' . uniqid();

// Format phone number to 2547XXXXXXXX
$phone = preg_replace('/\s+/', '', $phone);
if (substr($phone, 0, 1) == '+') $phone = substr($phone, 1);
if (substr($phone, 0, 1) == '0') $phone = '254' . substr($phone, 1);
if (substr($phone, 0, 1) == '7') $phone = '254' . $phone;

if (strlen($phone) != 12 || substr($phone, 0, 3) != '254') {
    $errmsg = 'Invalid phone number. Use format 07XXXXXXXX.';
    return;
}

// 1. Get access token
$token_url = ($config['env'] == 'live')
    ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
    : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$credentials = base64_encode($config['key'] . ':' . $config['secret']);
$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$token_data = json_decode($response, true);
$access_token = $token_data['access_token'] ?? '';

if (!$access_token) {
    $errmsg = 'Failed to authenticate with Safaricom. Check credentials.';
    return;
}

// 2. Prepare STK push data
$timestamp = date('YmdHis');
$password = base64_encode($config['BusinessShortCode'] . $config['passkey'] . $timestamp);

$stk_data = [
    'BusinessShortCode' => $config['BusinessShortCode'],
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => $config['TransactionType'],
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => $config['BusinessShortCode'],
    'PhoneNumber' => $phone,
    'CallBackURL' => $config['CallBackURL'],
    'AccountReference' => $orderNo,
    'TransactionDesc' => $config['TransactionDesc']
];

$stk_url = ($config['env'] == 'live')
    ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
    : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

$ch = curl_init($stk_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stk_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$stk_response = curl_exec($ch);
curl_close($ch);
$stk_result = json_decode($stk_response, true);

if (isset($stk_result['ResponseCode']) && $stk_result['ResponseCode'] == '0') {
    $checkoutRequestID = $stk_result['CheckoutRequestID'];
    $merchantRequestID = $stk_result['MerchantRequestID'];

    // Save to database
    $stmt = $conn->prepare("INSERT INTO orders (orderNo, amount, phone, CheckoutRequestID, MerchantRequestID, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("sdsss", $orderNo, $amount, $phone, $checkoutRequestID, $merchantRequestID);
    $stmt->execute();
    $stmt->close();

    $_SESSION['CheckoutRequestID'] = $checkoutRequestID;
    $_SESSION['orderNo'] = $orderNo;
    $_SESSION['phone'] = $phone;
    $_SESSION['amount'] = $amount;
    $_SESSION['email'] = $email;

    header('Location: confirm-payment.php');
    exit;
} else {
    $errmsg = $stk_result['errorMessage'] ?? 'STK push failed. Please try again.';
}
?>