<?php
session_start();
require "db.php";

if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    die("Invalid order.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $payment_method = $_POST['payment_method'];

    if ($payment_method === 'Card') {
        if (!preg_match('/^\d{16}$/', $_POST['card_number'])) {
            die("Invalid card number.");
        }
    }

    if ($payment_method === 'E-Wallet') {
        if (!preg_match('/^01\d{8,9}$/', $_POST['phone_number'])) {
            die("Invalid phone number.");
        }
    }

    $order_no = rand(1, 1000);

    $stmt = $conn->prepare("
        UPDATE orders 
        SET status='paid',
            order_no=?,
            payment_method=?
        WHERE id=?
    ");
    $stmt->execute([$order_no, $payment_method, $order_id]);

    header("Location: receipt.php?order_id=$order_id");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}

$stmt_items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($items as $i) {
    $total += $i['price'] * $i['qty'];
}
?>
<!doctype html>
<html>
<head>
<title>Payment</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{font-family:Arial;background:#f8fafc;margin:0}
.container{max-width:600px;margin:50px auto;background:white;padding:25px;border-radius:10px}
h2{text-align:center}
.item{display:flex;justify-content:space-between;margin-bottom:8px}
.total{font-weight:bold;font-size:18px;margin:15px 0}
.pay-box{border:1px solid #e5e7eb;padding:12px;border-radius:8px;margin-bottom:10px}
.btn{width:100%;padding:12px;background:#6c5ce7;color:white;border:none;border-radius:8px;cursor:pointer}
label{display:flex;gap:10px;cursor:pointer}
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:16px 28px;
    background:white;
    box-shadow:0 2px 8px rgba(0,0,0,0.08);
    position:sticky;
    top:0;
    z-index:10;
}

.brand{
    display:flex;
    align-items:center;
    gap:12px;
}

.logo-img{
    width:50px;
    height:50px;
    border-radius:8px;
    object-fit:cover;
}

.brand-text .title{
    font-weight:700;
    font-size:16px;
}

.brand-text .subtitle{
    font-size:13px;
    color:#6b7280;
}


</style>
</head>
<body>
<header class="header">
    <div class="brand">
        <img src="img/logo.jpg" class="logo-img">
        <div class="brand-text">
            <div class="title">TAR UMT Cafeteria</div>
            <div class="subtitle">Online Ordering System</div>
        </div>
    </div>

</header>

<div class="container">
    <h2>Payment</h2>

<?php foreach($items as $item): ?>
    <?php 
    if (empty(trim($item['item_name']))) {
        continue; 
    }
    ?>
    <div class="item">
        <div><?= htmlspecialchars($item['item_name']) ?> x <?= $item['qty'] ?></div>
        <div>RM <?= number_format($item['price'] * $item['qty'],2) ?></div>
    </div>
<?php endforeach; ?>

    <div class="total">Total: RM <?= number_format($total,2) ?></div>

    <form method="POST" onsubmit="return validatePayment()">

        <h3>Select Payment Method</h3>

        <div class="pay-box">
            <label>
                <input type="radio" name="payment_method" value="Card" required onclick="showInput('card')">
                💳 Debit / Credit Card
            </label>
            <input type="text" name="card_number" id="card_number"
                   placeholder="Card Number (16 digits)"
                   style="display:none; margin-top:8px; width:100%; padding:10px;">
        </div>

        <div class="pay-box">
            <label>
                <input type="radio" name="payment_method" value="E-Wallet" onclick="showInput('wallet')">
                📱 E-Wallet
            </label>
            <input type="text" name="phone_number" id="phone_number"
                   placeholder="Phone Number (e.g. 0123456789)"
                   style="display:none; margin-top:8px; width:100%; padding:10px;">
        </div>

        <div class="pay-box">
            <label>
                <input type="radio" name="payment_method" value="Cash" onclick="showInput('cash')">
                💵 Cash (Pay at Counter)
            </label>
        </div>

        <button class="btn" type="submit">Pay Now</button>
    </form>
</div>

<script>
function showInput(type) {
    document.getElementById('card_number').style.display = 'none';
    document.getElementById('phone_number').style.display = 'none';

    document.getElementById('card_number').required = false;
    document.getElementById('phone_number').required = false;

    if (type === 'card') {
        document.getElementById('card_number').style.display = 'block';
        document.getElementById('card_number').required = true;
    }

    if (type === 'wallet') {
        document.getElementById('phone_number').style.display = 'block';
        document.getElementById('phone_number').required = true;
    }
}

function validatePayment() {
    const method = document.querySelector('input[name="payment_method"]:checked').value;

    if (method === 'Card') {
        if (!/^\d{16}$/.test(card_number.value)) {
            alert("Please enter a valid 16-digit card number.");
            return false;
        }
    }

    if (method === 'E-Wallet') {
        if (!/^01\d{8,9}$/.test(phone_number.value)) {
            alert("Please enter a valid Malaysian phone number.");
            return false;
        }
    }

    return true;
}
</script>

</body>
</html>
