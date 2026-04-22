<?php
require_once 'config.php';

$errmsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'express-stk.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RevisionVault Pro – Subscribe</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 500px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .price h1 { color: #1a2980; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
        button { background: #25d366; color: white; border: none; padding: 12px 20px; font-size: 18px; border-radius: 5px; cursor: pointer; width: 100%; }
        button:hover { background: #128C7E; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-top: 20px; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <div class="price">
        <h1>Activate Full Access</h1>
        <p>Choose your plan (KES 50 for 2 months / KES 100 for 4 months)</p>
    </div>

    <?php if ($errmsg): ?>
        <div class="error"><?php echo htmlspecialchars($errmsg); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Select Plan</label>
            <select name="amount" required>
                <option value="50">2 Months Access - KES 50</option>
                <option value="100">4 Months Access - KES 100</option>
            </select>
        </div>
        <div class="form-group">
            <label>M-Pesa Phone Number (e.g., 0712345678)</label>
            <input type="text" name="phone_number" placeholder="0712345678" required>
        </div>
        <div class="form-group">
            <label>Your Email (for receipt)</label>
            <input type="email" name="email" placeholder="you@example.com" required>
        </div>
        <button type="submit">Pay with M-Pesa</button>
    </form>
    <div class="info">
        <strong>How it works:</strong><br>
        1. Enter your Safaricom phone number and email.<br>
        2. Click "Pay with M-Pesa".<br>
        3. You'll receive an STK push on your phone – enter your PIN.<br>
        4. Your account will be activated immediately.
    </div>
</div>
</body>
</html>