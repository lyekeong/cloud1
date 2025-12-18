<?php
session_start();
require "db.php";

// Check login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

$stmt = $conn->prepare("
    SELECT order_no, subtotal, payment_method, status, created_at
    FROM orders
    WHERE user_email = ?
    ORDER BY created_at DESC
");
$stmt->execute([$email]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($s) {
    return htmlspecialchars($s, ENT_QUOTES);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Order History</title>

<style>
    body {
        margin: 0;
        font-family: Inter, Arial;
        background: #f3f4f6;
    }

    header{
        display:flex;
        align-items:center;
        justify-content:space-between;
        padding:18px 28px;
        background:white;
        box-shadow:0 2px 8px rgba(2,6,23,0.06);
        position:sticky; top:0; z-index:10;
    }
    .brand{display:flex; align-items:center; gap:12px}
    .brand .logo {
        width:44px; height:44px;
        display:flex; align-items:center; justify-content:center;
        border-radius:8px; font-weight:700;
    }
    .logo-img {
        width: 55px;
        height: 55px;
        object-fit: cover;
        border-radius: 8px;
    }
    .home-nav a {
        color: #000000ff;
        text-decoration: none;
        font-weight: 500;
        padding: 8px 10px;
    }
    .home-nav a:hover {
        background: #f1f4ff;
    }

    .container {
        max-width: 900px;
        margin: 30px auto;
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.06);
    }

    h2 {
        margin-top: 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #6c5ce7;
        color: white;
        padding: 12px;
        text-align: left;
        font-size: 14px;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    .status {
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
    }

    .placed { background: #fef3c7; color: #92400e; }
    .paid { background: #d1fae5; color: #065f46; }
    .completed { background: #e0e7ff; color: #3730a3; }
    .cancelled { background: #fee2e2; color: #991b1b; }

    .btn {
        display: inline-block;
        margin-top: 20px;
        background: #6c5ce7;
        color: white;
        text-decoration: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
    }

    .empty {
        text-align: center;
        padding: 20px;
        color: #6b7280;
    }

    @media (max-width: 600px) {
        table, thead, tbody, th, td, tr {
            display: block;
        }

        thead {
            display: none;
        }

        tr {
            background: #f9fafb;
            margin-bottom: 12px;
            border-radius: 8px;
            padding: 12px;
        }

        td {
            border: none;
            padding: 6px 0;
        }

        td::before {
            font-weight: 600;
            display: block;
            margin-bottom: 2px;
            color: #6b7280;
        }

        td:nth-child(1)::before { content: "Order No"; }
        td:nth-child(2)::before { content: "Total"; }
        td:nth-child(3)::before { content: "Payment"; }
        td:nth-child(4)::before { content: "Status"; }
        td:nth-child(5)::before { content: "Date"; }
    }
        @media (max-width: 580px) {

        header {
            flex-direction: column;
            align-items: flex-start;
            padding: 12px 16px;
            gap: 10px;
        }

        .brand {
            width: 100%;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }

        .brand > div:not(.logo):not(.home-nav) {
            display: none; 
        }

        .brand .logo {
            width: 70px;
            height: 70px;
        }

        .logo-img {
            width: 70px;
            height: 70px;
        }

        .home-nav {
            width: 100%;
            display: flex;
            justify-content: space-around;
            margin-top: 8px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        .home-nav a {
            font-size: 15px;
            padding: 6px 10px;
        }

        .controls {
            width: 100%;
            display: flex;
            gap: 8px;
        }

        .search {
            flex: 1;
            min-width: 0;
        }

        .btn {
            padding: 8px 12px;
            font-size: 14px;
        }
    }
</style>
</head>
<body>

<header>
    <div class="brand">
        <div class="logo"><img src="img/logo.jpg" class="logo-img"></div>
        <div>
            <div style="font-weight:700">TAR UMT Cafeteria</div>
            <div class="muted" style="font-size:13px">Order History</div>
        </div>
        <div class="home-nav">
            <a href="home.php" class="active">Home</a>
            <a href="orderhistory.php">Orders History</a>
        </div>    
    </div>
</header>

<div class="container">
    <h2>Your Orders</h2>

    <?php if (count($orders) === 0): ?>
        <div class="empty">You have not placed any orders yet.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order No</th>
                    <th>Total (RM)</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= e($o['order_no']) ?></td>
                    <td><?= number_format($o['subtotal'], 2) ?></td>
                    <td><?= e($o['payment_method'] ?? '—') ?></td>
                    <td>
                        <span class="status <?= e($o['status']) ?>">
                            <?= ucfirst(e($o['status'])) ?>
                        </span>
                    </td>
                    <td><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
