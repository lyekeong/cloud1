<?php
require "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name'];

    $sql = "INSERT INTO account (email, password, name, role) VALUES (?, ?, ?, 'user')";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([$email, $password, $name]);
        echo "<script>alert('Registration successful!'); window.location='login.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Email already exists!');</script>";
    }
}
?>

<html>
    <title>register</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial;
            background-color: #5192b0ff;

        }

        .register-box {
            width: 350px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            text-align: center;
            color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        h2 {
            margin-bottom: 30px;
            font-size: 26px;
        }

        input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            outline: none;
            border-bottom: 1px solid white;
            background: transparent;
            color: white;
            font-size: 16px;
        }

        .button {
            width: 90%;
            padding: 12px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            background: white;
            color: black;
            margin-top: 20px;
        }

        a {
            color: white;
            font-size: 14px;
            text-decoration: none;
        }

        .error {
            color: #ffdddd;
            background: rgba(255,0,0,0.3);
            padding: 5px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
    </style>
<body>
    
<div class="register-box">
    <h2>Register</h2>
    
    <?php if (!empty($error)) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Name" required><br><br>

        <input type="email" name="email" placeholder="email" required><br><br>

        <input type="password" name="password" placeholder="password" required><br><br>

        <button type="submit">Register</button>
    </form>
</div>
</body>
</html>