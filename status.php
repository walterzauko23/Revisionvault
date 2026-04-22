<?php
// status.php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_GET['checkoutRequestID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing request ID']);
    exit;
}

$checkoutRequestID = $_GET['checkoutRequestID'];
$stmt = $conn->prepare("SELECT status FROM orders WHERE CheckoutRequestID = ?");
$stmt->bind_param("s", $checkoutRequestID);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    echo json_encode(['status' => $row['status']]);
} else {
    echo json_encode(['status' => 'pending']);
}
$stmt->close();
?>