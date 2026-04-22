<?php
// config.php
session_start();

// Database connection (MySQL)
$db_host = 'localhost';
$db_user = 'your_db_user';
$db_pass = 'your_db_password';
$db_name = 'revisionvault';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Daraja live credentials (use environment variables in production)
$config = [
    'env'                => 'live',                    // 'sandbox' or 'live'
    'BusinessShortCode'  => '5574313',                 // Your Till Number
    'key'                => 'Ko7bFUP7WWmVfYNqCnyuFvfMoCiHWdn1N3BOZlkG7A4RYyZa',
    'secret'             => 'zIrKYH065tCvOpTcytgDL4XPA1bEmIJdIATvHhgG2IMMUjaL2oQmgkIEbfR4VuKA',
    'passkey'            => '6b8463dd4140558418530571d4baecb1b6e8cca5c1402b053dfb54797640bc09',
    'TransactionType'    => 'CustomerBuyGoodsOnline',  // Use Till
    'CallBackURL'        => 'https://yourdomain.com/callback.php', // CHANGE THIS!
    'AccountReference'   => 'RevisionVault',
    'TransactionDesc'    => 'Subscription Payment'
];

// Firebase REST API URL (for updating user subscription)
$firebase_db_url = 'https://revisionvault-pro-83002-default-rtdb.firebaseio.com';
?>