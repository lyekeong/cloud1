<?php
session_start();
require "db.php"; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM orders ORDER BY id DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusStyle($status) {
    switch ($status) {
        case 'placed':
            return 'background:#fef3c7; color:#b45309; padding:4px 8px; border-radius:4px; font-weight:600;';
        case 'paid':
            return 'background:#d1fae5; color:#065f46; padding:4px 8px; border-radius:4px; font-weight:600;';
        case 'completed':
            return 'background:#eff6ff; color:#1e40af; padding:4px 8px; border-radius:4px; font-weight:600;';
        case 'cancelled':
            return 'background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:4px; font-weight:600;';
        default:
            return '';
    }
}
?>
<!doctype html>
<html>
<head>
<title>Admin — View Orders</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body{
        margin:0;
        font-family:Inter,Arial;
        background:#f3f4f6;
    }
    * {
    box-sizing: border-box; /* Ensures padding/border is included in the element's total width */
    }
    header{
        display:flex; justify-content:space-between; align-items:center;
        padding:18px 28px;
        background:white; 
        box-shadow:0 2px 8px rgba(0,0,0,0.08);
    }
    .logo-box{display:flex; align-items:center; gap:12px}
    .logo{
        width:44px; height:44px;
        display:flex; align-items:center; justify-content:center;
        font-weight:700; border-radius:8px;
    }
    .logo-img {
    width: 55px;
    height: 55px;
    object-fit: cover;
    border-radius: 8px;
    }
    .admin-nav a {
        color: #000000ff;
        text-decoration: none;
        font-weight: 500;
        padding: 8px 10px;
    }
    .admin-nav a:hover {
        background: #f1f4ff;
    }
    .header-logout-btn{
        color:#000000ff;
        border:none;
        padding: 8px 12px; 
        border-radius:6px;
        cursor:pointer;
        font-size:14px;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.2s;
    }
    .header-logout-btn:hover {
        background: #f1f4ff;
    }
    .container{
        max-width:1000px; margin:40px auto; 
        background:white; padding:24px;
        border-radius:12px; 
        box-shadow:0 10px 20px rgba(0,0,0,0.06);
    }
    table{
        width:100%; border-collapse:collapse;
    }
    table th{
        background:#6c5ce7; color:white; padding:12px;
        text-align:left; font-size:14px;
    }
    table td{
        padding:12px; border-bottom:1px solid #e5e7eb;
    }
    .btn{
        padding:6px 12px; border:none; cursor:pointer; 
        border-radius:6px; font-size:14px;
    }
    .view{background:#3b82f6; color:white;}
    .status-change{background:#10b981; color:white;}
    @media (max-width: 430px) {
        header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .logo-box {
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }

        .admin-nav {
            width: 100%;
            display: flex;
            gap: 16px;
            margin-top: 6px;
        }

        .header-logout-btn {
            align-self: flex-start;
            margin-top: 6px;
            padding: 6px 10px;
            font-size: 13px;
        }
    }
    @media (max-width: 900px) {
        .container {
            margin: 20px 12px;
            padding: 16px;
        }
    }

    @media (max-width: 768px) {

        .container {
            overflow-x: auto;
        }

        table {
            min-width: 900px; 
        }

        table th,
        table td {
            padding: 8px;
            font-size: 13px;
            white-space: nowrap;
        }

        h2 {
            font-size: 18px;
        }
    }

    @media (max-width: 480px) {

        table th,
        table td {
            font-size: 12px;
            padding: 6px;
        }

        .container {
            padding: 14px;
        }
    }
</style>
</head>
<body>

<header>
    <div class="logo-box"><div class="logo"><img src="img/logo.jpg" class="logo-img"> </div>
        <div>
            <div style="font-weight:700;">Admin Panel</div>
            <div style="font-size:13px; color:#6b7280;">Menu Items</div>
        </div>
    <div class="admin-nav">
            <a href="admin.php" class="active">Home</a>
            <a href="admin_view_orders.php">Orders</a>
        </div>
    </div>

    <button onclick="location.href='logout.php'" class="header-logout-btn">
        Logout
    </button>
</header>

<div class="container">
    <h2 style="margin:0">Customer Orders</h2>
    <table>
        <tr>
        <th width="60">ID</th>
            <th width="100">Order No.</th>
            <th>Customer Email</th>
            <th width="100">Total (RM)</th>
            <th width="120">Date Placed</th>
            <th width="100">Method</th>
            <th width="120">Status</th>
        </tr>

        <?php if (count($orders) === 0): ?>
        <tr>
            <td colspan="9" style="text-align:center; padding:20px; color:#6b7280;">
                No orders found.
            </td>
        </tr>
        <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['order_no'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($order['user_email']) ?></td>
                <td><?= number_format($order['subtotal'], 2) ?></td>
                <td><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></td>
                <td><?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></td>
                <td>
                    <span style="<?= getStatusStyle($order['status']) ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </table>
</div>
</body>
</html>