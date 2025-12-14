<?php
session_start();
require "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM menu WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item not found!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    
    $imageName = $item['image'];

    if (!empty($_FILES['image']['name'])) {
        $img = $_FILES['image'];
        $newName = time() . "_" . basename($img['name']);
        $target = "uploads/" . $newName;

        if (move_uploaded_file($img['tmp_name'], $target)) {

            if (!empty($item['image']) && file_exists("uploads/" . $item['image'])) {
                unlink("uploads/" . $item['image']);
            }

            $imageName = $newName;
        }
    }

    $update = $conn->prepare("UPDATE menu SET name=?, category=?, price=?, image=? WHERE id=?");
    $update->execute([$name, $category, $price, $imageName, $id]);

    header("Location: admin.php?updated=1");
    exit();
}
?>

<!doctype html>
<html>
<head>
<title>Edit Item</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body { font-family: Arial; background:#f3f4f6; margin:0; }
    .container{
        max-width:600px; margin:40px auto; background:white;
        padding:24px; border-radius:12px; box-shadow:0 10px 20px rgba(0,0,0,0.06);
    }
    header{
        display:flex; justify-content:space-between; align-items:center;
        padding:18px 28px;
        background:white; box-shadow:0 2px 8px rgba(0,0,0,0.08);
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
    input, select{
        width:100%; padding:10px; margin:8px 0 20px 0;
        border:1px solid #ccc; border-radius:6px;
    }
    .btn{
        padding:10px 16px; border:none; cursor:pointer;
        border-radius:6px; background:#6c5ce7; color:white; font-size:15px;
    }
    .thumb{
        width:120px; height:90px; object-fit:cover; border-radius:6px; margin-bottom:10px;
    }
    @media (max-width: 430px) {
        header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .logo-bxo {
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }
    }
</style>
</head>
<body>

<header>
    <div class="logo-box"><div class="logo"><img src="img/logo.jpg" class="logo-img"> </div>
        <div>
            <div style="font-weight:700;">Admin Panel</div>
            <div style="font-size:13px; color:#6b7280;">Add Menu Item</div>
        </div>
    </div>

    <div style="display:flex; gap:10px;">
        <button onclick="location.href='admin.php'" 
                class="btn" 
                style="width:auto; padding:8px 12px;">
            ← Back
        </button>

        <button onclick="location.href='logout.php'" 
                class="btn" 
                style="width:auto; padding:8px 12px;">
            Logout
        </button>
    </div>
</header>

<div class="container">

    <h2>Edit Item</h2>

    <form method="POST" enctype="multipart/form-data">

        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>

        <label>Category:</label>
        <input type="text" name="category" value="<?= htmlspecialchars($item['category']) ?>" required>

        <label>Price (RM):</label>
        <input type="number" step="0.01" name="price" value="<?= $item['price'] ?>" required>

        <label>Current Image:</label><br>
        <?php if ($item['image']): ?>
            <img class="thumb" src="uploads/<?= $item['image'] ?>">
        <?php else: ?>
            <p>No image uploaded</p>
        <?php endif; ?>

        <input type="file" name="image" accept="image/*">

        <button class="btn" type="submit">Update Item</button>
    </form>
</div>

</body>
</html>
