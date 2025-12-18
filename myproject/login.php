<?php
session_start();
require "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM account WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin.php");
            exit();
        } else {
            header("Location: home.php");
            exit();
        }

    } else {
        echo "<script>alert('Invalid email or password');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial;
            background-color: #5192b0ff;

        }

        .login-box {
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

        .controls {
            display:flex; 
            gap:12px; 
            align-items:center
        }
        @media (max-width: 480px) {

            body {
                background-color: #5192b0ff;
            }

            .login-box {
                width: 90%;
                padding: 30px 20px;
                border-radius: 12px;
            }

            h2 {
                font-size: 22px;
                margin-bottom: 20px;
            }

            input {
                width: 100%;
                font-size: 15px;
                padding: 12px 8px;
            }

            .button {
                width: 100%;
                font-size: 15px;
                padding: 12px 8px;
            }

            p {
                font-size: 14px;
            }
        }

    </style>
</head>

<body>

<div class="login-box">
    <h2>Login</h2>

    <?php if (!empty($error)) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required ><br><br>

        <input type="password" name="password" placeholder="Password" required><br><br>
        
        <button class="button">Log In</button>
    </form>

    <p style="margin-top:20px;">Don’t have an account?
        <a href="register.php">Register</a>
    </p>
</div>
</body>
</html>
