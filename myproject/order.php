    <?php
    session_start();
    require "db.php";

    if (!isset($_POST['order_data'])) {
        die("No order received.");
    }

    $orderData = json_decode($_POST['order_data'], true);

    if (!$orderData || count($orderData) === 0) {
        die("Cart is empty.");
    }

    $userEmail = $_SESSION['email'];
    $subtotal = 0;

    foreach ($orderData as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }

    $stmt = $conn->prepare("INSERT INTO orders (user_email, subtotal, status) VALUES (?, ?, 'placed')");
    $stmt->execute([$userEmail, $subtotal]);
    $orderId = $conn->lastInsertId();

    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, item_name, price, qty) VALUES (?, ?, ?, ?, ?)");

    foreach ($orderData as $item) {
        $itemStmt->execute([
            $orderId,
            $item['id'],
            $item['name'],
            $item['price'],
            $item['qty']
        ]);
    }

header("Location: payment.php?order_id=" . $orderId);
exit();

    ?>
