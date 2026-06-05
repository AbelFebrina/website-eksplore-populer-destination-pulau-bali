<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login UI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            height: 100vh;
            background-image: url("background.bali.png");
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 30px;
            color: white;
            cursor: pointer;
        }

        .card {
            width: 480px;
            padding: 40px 30px;
            border-radius: 20px;
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(4px);
            position: relative;
            text-align: center;
            color: white;
        }

        .logo-top {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 70px;
            opacity: 0.9;
        }

        h2 {
            font-size: 32px;
            margin-bottom: 25px;
            margin-top: 20px;
        }

        .input-box {
            width: 100%;
            margin: 10px 0;
        }

        .input-box input {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: none;
            outline: none;
            background: rgba(255, 255, 255, 0.75);
            font-size: 16px;
        }

        .btn-row {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 120px;
        }

        .btn-light {
            background: white;
            color: black;
        }

        .btn-dark {
            background: #0c726c;
            color: white;
        }

        .error {
            background: rgba(255, 0, 0, 0.3);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .success {
            background: rgba(0, 200, 0, 0.3);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="back-btn" onclick="history.back()">&#8592;</div>

    <div class="card">
        <img src="Logo.img.png" class="logo-top">
        <h2>Login</h2>

        <!-- Pesan error/sukses -->
        <?php if(isset($_GET['error']) && $_GET['error'] == 1): ?>
        <div class="error">Email atau password salah!</div>
        <?php endif; ?>
        <?php if(isset($_GET['sukses']) && $_GET['sukses'] == 1): ?>
        <div class="success">Registrasi berhasil! Silakan login.</div>
        <?php endif; ?>

        <!-- FORM LOGIN → kirim ke proseslogin.php -->
        <form action="../backend/proseslogin.php" method="POST">
            <div class="input-box">
                <input type="email" name="email" placeholder="Enter email" required>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="btn-row">
                <a href="signup.php"><button type="button" class="btn btn-light">Sign Up</button></a>
                <button type="submit" class="btn btn-dark">Login</button>
            </div>
        </form>
    </div>

</body>

</html>