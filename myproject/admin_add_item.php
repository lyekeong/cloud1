<?php
session_start();
require "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];

    $imgName = "";
    if (!empty($_FILES['image']['name'])) {
        $imgName = time() . "_" . basename($_FILES["image"]["name"]);
        $target = "uploads/" . $imgName;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target);
    }

    $sql = "INSERT INTO menu (name, category, price, image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([$name, $category, $price, $imgName]);
        header("Location: admin.php?success=1");
        exit();
    } catch (Exception $e) {
        $msg = "Error adding item!";
    }
}

?>
<!doctype html>
<html>
<head>
<title>Admin — Add Menu Item</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body{
        margin:0;
        font-family:Inter,Arial;
        background:linear-gradient(180deg,#eef2ff,#f8fafc);
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
        padding: 8px 12px; /* Slight adjustment for better visual balance */
        border-radius:6px; /* Reduced to 6px to match navigation links hover radius */
        cursor:pointer;
        font-size:14px;
        font-weight: 500;
        text-decoration: none; /* Ensure it looks like a clean button */
        transition: background 0.2s;
    }
    .header-logout-btn:hover {
        background: #f1f4ff; /* Slightly darker shade on hover */
    }
    .container{
        max-width:600px; margin:40px auto; background:white;
        padding:28px; border-radius:12px;
        box-shadow:0 10px 20px rgba(0,0,0,0.06);
    }
    .input-field{
        display:flex; flex-direction:column; margin-bottom:18px;
    }
    label{
        font-size:14px; color:#6b7280; margin-bottom:6px;
    }
    input, select{
        padding:10px; border:1px solid #d1d5db;
        border-radius:8px; font-size:15px;
    }
    .btn{
        background:#6c5ce7; color:white;
        padding:12px; border:none; width:100%;
        border-radius:8px; cursor:pointer; margin-top:10px;
    }
    .msg{
        padding:10px; text-align:center; margin-bottom:10px;
        border-radius:6px; font-weight:600;
    }
    .success{background:#d1fae5; color:#065f46;}
    .error{background:#fee2e2; color:#991b1b;}
    @media (max-width: 768px) {

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
    <h2 style="margin-bottom:10px;">Add New Menu Item</h2>
    <p style="color:#6b7280;">Fill in the details below to add a new food or drink item.</p>    

    <form method="POST" enctype="multipart/form-data">
        
        <div class="input-field">
            <label>Item Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="input-field">
            <label>Category</label>
            <select name="category" required>
                <option value="">-- Select Category --</option>
                <option value="Main">Main</option>
                <option value="Drinks">Drinks</option>
                <option value="Dessert">Dessert</option>
            </select>
        </div>

        <div class="input-field">
            <label>Price (RM)</label>
            <input type="number" step="0.01" name="price" required>
        </div>

        <div class="input-field">
            <label>Image</label>
            <input type="file" name="image" required>
        </div>

        <button class="btn" type="submit">Add Item</button>
    </form>
</div>

</body>
</html>
