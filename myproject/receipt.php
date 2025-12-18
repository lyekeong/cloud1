<?php
session_start();
require "db.php";

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) die("Invalid receipt");

$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt_items = $conn->prepare("SELECT * FROM order_items WHERE order_id=?");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt</title>
<style>
body{font-family:Arial;background:#f1f5f9}
.receipt{max-width:500px;margin:40px auto;background:white;padding:25px;border-radius:10px}
h2{text-align:center}
.row{display:flex;justify-content:space-between;margin-bottom:8px}
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
.home-btn-container {
    padding: 10px 0 0 0;
}
.home-button {
    width: 100%;
    padding: 10px;
    background: #6c5ce7;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
}

@media (max-width: 850px) {

    .receipt {
        max-width: 90%;
        margin: 30px auto;
        padding: 20px;
    }

    .header {
        padding: 14px 20px;
    }

    .brand-text .title {
        font-size: 15px;
    }

    .brand-text .subtitle {
        font-size: 12px;
    }
}

@media (max-width: 580px) {

    body {
        background: #f1f5f9;
    }

    .header {
        flex-direction: row;
        padding: 12px 16px;
    }

    .logo-img {
        width: 65px;
        height: 65px;
    }

    .brand-text {
        display: none;
    }

    .receipt {
        max-width: 95%;
        margin: 20px auto;
        padding: 16px;
        border-radius: 12px;
    }

    h2 {
        font-size: 20px;
    }

    .row {
        font-size: 14px;
    }

    .home-button {
        font-size: 15px;
        padding: 12px;
    }
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

<div class="receipt">
    <h2>Payment Receipt</h2>
    <p><strong>Order No:</strong> <?= htmlspecialchars($order['order_no']) ?></p>
    <p><strong>Date:</strong> <?= $order['created_at'] ?></p>
    <hr>

    <?php $total=0; foreach($items as $i): 
        $sub = $i['price'] * $i['qty'];

        if (empty(trim($i['item_name'])) || $sub == 0) {
            continue;
        }
        
        $total += $sub;
    ?>
    <div class="row">
            <div><?= htmlspecialchars($i['item_name']) ?> x <?= $i['qty'] ?></div>
            <div>RM <?= number_format($sub,2) ?></div>
        </div>
    <?php endforeach; ?>

    <hr>
    <div class="row"><strong>Total</strong><strong>RM <?= number_format($total,2) ?></strong></div>

    <p style="text-align:center;margin-top:20px;">Thank you for your payment!</p>
    <div class="home-btn-container">
        <button class="home-button" onclick="location.href='home.php'">
            Back to Home
        </button>
    </div>
</div>

</body>
</html>
