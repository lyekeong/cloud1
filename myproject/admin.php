<?php
session_start();
require "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM menu ORDER BY id DESC");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
<title>Admin — View Items</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body{
        margin:0;
        font-family:Inter,Arial;
        background:#f3f4f6;
    }
    * {
    box-sizing: border-box;
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
        max-width:1000px; width: 100%; margin:40px auto; 
        background:white; padding:24px;
        border-radius:12px; 
        box-shadow:0 10px 20px rgba(0,0,0,0.06);
    }
    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }
    table{
        width:100%; border-collapse:collapse;
        margin-top:20px;
    }
    table th{
        background:#6c5ce7; color:white; padding:12px;
        text-align:left; font-size:14px;
    }
    table td{
        padding:12px; border-bottom:1px solid #e5e7eb;
    }
    .thumb{
        width:70px; height:50px; object-fit:cover; border-radius:6px;
    }
    .btn{
        padding:6px 12px; border:none; cursor:pointer; 
        border-radius:6px; font-size:14px;
    }
    .edit{background:#3b82f6; color:white;}
    .delete{background:#ef4444; color:white;}
    .top-actions{
        display:flex; justify-content:space-between; align-items:center;
    }
    .add-btn{
        background:#6c5ce7; color:white;
        padding:10px 16px; border-radius:8px; cursor:pointer;
        text-decoration:none;
    }
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
    @media (max-width: 600px) {
    table th, table td {
        padding: 8px;
        font-size: 13px;
    }

    .thumb {
        width: 50px;
        height: 40px;
    }

    .btn {
        font-size: 12px;
        padding: 5px 8px;
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

    <div class="top-actions">
        <h2 style="margin:0;">Menu Items</h2>
        <a href="admin_add_item.php" class="add-btn">+ Add New Item</a>
    </div>

    <div class="table-wrapper">
        <table>
            <tr>
                <th width="60">ID</th>
                <th>Image</th>
                <th>Name</th>
                <th width="140">Category</th>
                <th width="120">Price (RM)</th>
                <th width="160">Actions</th>
            </tr>

            <?php if (count($items) === 0): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px; color:#6b7280;">
                        No items found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>

                        <td>
                            <?php if ($row['image']): ?>
                                <img class="thumb" src="uploads/<?= $row['image'] ?>">
                            <?php else: ?>
                                <div class="thumb" style="background:#e5e7eb; display:flex; justify-content:center; align-items:center; color:#6b7280;">
                                    No Image
                                </div>
                            <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= number_format($row['price'], 2) ?></td>

                        <td>
                            <button class="btn edit" onclick="location.href='admin_edit_item.php?id=<?= $row['id'] ?>'">Edit</button>

                            <button class="btn delete"
                                onclick="if(confirm('Delete this item?')) location.href='admin_delete_item.php?id=<?= $row['id'] ?>'">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>

</div>

</body>
</html>
