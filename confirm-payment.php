<?php
require_once 'config.php';

if (!isset($_SESSION['CheckoutRequestID'])) {
    header('Location: checkout.php');
    exit;
}

$checkoutRequestID = $_SESSION['CheckoutRequestID'];
$orderNo = $_SESSION['orderNo'];
$phone = $_SESSION['phone'];
$amount = $_SESSION['amount'];
$email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - RevisionVault Pro</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 10px; text-align: center; }
        .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #25d366; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-top: 20px; }
        button { background: #1a2980; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
    </style>
    <script>
        let attempts = 0;
        function checkStatus() {
            fetch('status.php?checkoutRequestID=<?php echo $checkoutRequestID; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        clearInterval(interval);
                        document.getElementById('status').innerHTML = '<h2 class="success">✅ Payment Successful!</h2><p>Your account has been activated. You can now access all study materials.</p><a href="https://revisionvault.pages.dev"><button>Go to Dashboard</button></a>';
                        document.getElementById('spinner').style.display = 'none';
                    } else if (data.status === 'cancelled' || data.status === 'failed') {
                        clearInterval(interval);
                        document.getElementById('status').innerHTML = '<h2 class="error">❌ Payment Failed or Cancelled</h2><p>Please try again. If the issue persists, contact support.</p><a href="checkout.php"><button>Try Again</button></a>';
                        document.getElementById('spinner').style.display = 'none';
                    } else {
                        attempts++;
                        if (attempts > 30) {
                            clearInterval(interval);
                            document.getElementById('status').innerHTML = '<h2 class="error">⏰ Timeout</h2><p>We are still waiting for confirmation. Please check your M-Pesa messages. If you paid, your account will be activated shortly.</p><a href="https://revisionvault.pages.dev"><button>Go to Home</button></a>';
                            document.getElementById('spinner').style.display = 'none';
                        }
                    }
                })
                .catch(err => console.error(err));
        }
        let interval = setInterval(checkStatus, 2000);
        checkStatus();
    </script>
</head>
<body>
<div class="container">
    <h2>Processing your payment</h2>
    <p>Amount: <strong>KES <?php echo $amount; ?></strong><br>Phone: <?php echo $phone; ?></p>
    <div id="spinner" class="spinner"></div>
    <div id="status">
        <p>Waiting for M-Pesa confirmation...<br>Please check your phone and enter your PIN.</p>
    </div>
    <div class="info">
        <strong>Note:</strong> If you already entered your PIN, please wait a few moments. The page will update automatically.
    </div>
</div>
</body>
</html>