<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Explore Bali</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background-image: url("assets/background.bali.png");
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
        }

        .back-btn {
            position: absolute;
            top: 24px;
            left: 24px;
            font-size: 28px;
            color: white;
            cursor: pointer;
            text-decoration: none;
            z-index: 10;
            transition: transform 0.2s;
        }

        .back-btn:hover {
            transform: translateX(-3px);
        }

        .card {
            position: relative;
            z-index: 5;
            width: 460px;
            padding: 44px 36px;
            border-radius: 20px;
            background: rgba(0, 0, 0, 0.42);
            backdrop-filter: blur(10px);
            text-align: center;
            color: white;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        }

        .logo-top {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 60px;
            opacity: 0.9;
        }

        .card h2 {
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 28px;
            margin-top: 16px;
        }

        .input-box {
            width: 100%;
            margin: 10px 0;
        }

        .input-box input {
            width: 100%;
            padding: 13px 16px;
            border-radius: 12px;
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            outline: none;
            background: rgba(255, 255, 255, 0.18);
            color: white;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
        }

        .input-box input::placeholder {
            color: rgba(255, 255, 255, 0.65);
        }

        .input-box input:focus {
            border-color: #4DD0C4;
            background: rgba(255, 255, 255, 0.25);
        }

        .btn-row {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 22px;
        }

        .btn {
            padding: 12px 0;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            width: 130px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .btn-light {
            background: white;
            color: #009688;
        }

        .btn-light:hover {
            background: #e0f2f1;
        }

        .btn-dark {
            background: #009688;
            color: white;
        }

        .btn-dark:hover {
            background: #00796b;
            transform: translateY(-2px);
        }

        .alert {
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 13px;
            font-weight: 500;
        }

        .alert-error { background: rgba(239, 83, 80, 0.35); border: 1px solid rgba(239, 83, 80, 0.5); }
    </style>
</head>
<body>
    <a href="loginWeb.php" class="back-btn">&#8592;</a>

    <div class="card">
        <img src="assets/Logo.img.png" class="logo-top" onerror="this.style.display='none'" alt="Logo">
        <h2>Sign Up</h2>

        <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
            <div class="alert alert-error">❌ Registration failed. Please try again.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] == 2): ?>
            <div class="alert alert-error">❌ Email already registered.</div>
        <?php endif; ?>

        <form action="backend/prosesregister.php" method="POST">
            <div class="input-box">
                <input type="text" name="username" placeholder="Full name" required>
            </div>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email address" required>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password (min. 8 characters)" required minlength="8">
            </div>
            <div class="btn-row">
                <a href="loginWeb.php" class="btn btn-light">Sign in</a>
                <button type="submit" class="btn btn-dark">Register</button>
            </div>
        </form>
    </div>
</body>
</html>