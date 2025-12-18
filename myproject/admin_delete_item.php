<?php
session_start();
require "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT image FROM menu WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($item) {

    if (!empty($item['image']) && file_exists("uploads/" . $item['image'])) {
        unlink("uploads/" . $item['image']);
    }

    $delete = $conn->prepare("DELETE FROM menu WHERE id = ?");
    $delete->execute([$id]);
}

header("Location: admin.php");
exit();
