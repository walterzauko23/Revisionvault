<?php
// callback.php
require_once 'config.php';

// Log raw callback
$callback_json = file_get_contents('php://input');
file_put_contents('mpesa_callback_log.txt', date('Y-m-d H:i:s') . ' - ' . $callback_json . PHP_EOL, FILE_APPEND);

$callback_data = json_decode($callback_json, true);

if (isset($callback_data['Body']['stkCallback'])) {
    $stkCallback = $callback_data['Body']['stkCallback'];
    $resultCode = $stkCallback['ResultCode'];
    $resultDesc = $stkCallback['ResultDesc'];
    $checkoutRequestID = $stkCallback['CheckoutRequestID'];
    $merchantRequestID = $stkCallback['MerchantRequestID'];

    if ($resultCode == 0) {
        // Successful payment
        $metadata = $stkCallback['CallbackMetadata']['Item'];
        $mpesaReceipt = '';
        $transactionDate = '';
        $amount = '';
        foreach ($metadata as $item) {
            if ($item['Name'] == 'MpesaReceiptNumber') $mpesaReceipt = $item['Value'];
            if ($item['Name'] == 'TransactionDate') $transactionDate = $item['Value'];
            if ($item['Name'] == 'Amount') $amount = $item['Value'];
        }

        // Update order in MySQL
        $stmt = $conn->prepare("UPDATE orders SET ResultCode = ?, ResultDesc = ?, MpesaReceiptNumber = ?, TransactionDate = ?, status = 'success' WHERE CheckoutRequestID = ?");
        $stmt->bind_param("issss", $resultCode, $resultDesc, $mpesaReceipt, $transactionDate, $checkoutRequestID);
        $stmt->execute();
        $stmt->close();

        // Retrieve the order to get the phone number and amount
        $order_stmt = $conn->prepare("SELECT phone, amount FROM orders WHERE CheckoutRequestID = ?");
        $order_stmt->bind_param("s", $checkoutRequestID);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        $order = $order_result->fetch_assoc();
        $order_stmt->close();

        if ($order) {
            $phone = $order['phone'];
            $paid_amount = $order['amount'];
            $plan_months = ($paid_amount == 50) ? 2 : 4;
            $expiry_date = date('Y-m-d H:i:s', strtotime("+$plan_months months"));

            // Update Firebase subscription for this user
            $firebase_url = $GLOBALS['firebase_db_url'] . '/approvedUsers.json?orderBy="phone"&equalTo="' . urlencode($phone) . '"';
            $user_data = file_get_contents($firebase_url);
            $users = json_decode($user_data, true);
            if ($users) {
                $userId = array_key_first($users);
                $update_payload = [
                    'subscription' => [
                        'active' => true,
                        'endDate' => $expiry_date,
                        'plan' => $plan_months . ' months',
                        'amount' => $paid_amount,
                        'receipt' => $mpesaReceipt,
                        'transactionId' => $checkoutRequestID
                    ],
                    'accountStatus' => 'active'
                ];
                $ch = curl_init($GLOBALS['firebase_db_url'] . '/approvedUsers/' . $userId . '.json');
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_payload));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);
                curl_close($ch);
            }
        }
    } else {
        // Payment failed or cancelled
        $stmt = $conn->prepare("UPDATE orders SET ResultCode = ?, ResultDesc = ?, status = 'cancelled' WHERE CheckoutRequestID = ?");
        $stmt->bind_param("iss", $resultCode, $resultDesc, $checkoutRequestID);
        $stmt->execute();
        $stmt->close();
    }
}

// Acknowledge receipt to Safaricom
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
?>