<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Explore Bali</title>
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

        .btn-login-full {
            width: 100%;
            margin-top: 22px;
            padding: 14px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: 700;
            background: #009688;
            color: white;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
            letter-spacing: 0.5px;
        }

        .btn-login-full:hover {
            background: #00796b;
            transform: translateY(-2px);
        }

        .signup-link {
            margin-top: 18px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
        }

        .signup-link a {
            color: #4DD0C4;
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 13px;
            font-weight: 500;
        }

        .alert-error   { background: rgba(239, 83, 80, 0.35); border: 1px solid rgba(239, 83, 80, 0.5); }
        .alert-success { background: rgba(76, 175, 80, 0.35);  border: 1px solid rgba(76, 175, 80, 0.5); }
    </style>
</head>
<body>
    <a href="index.php" class="back-btn">&#8592;</a>

    <div class="card">
        <img src="assets/Logo.img.png" class="logo-top"
             onerror="this.style.display='none'" alt="Logo">
        <h2>Login</h2>

        <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
            <div class="alert alert-error">❌ Wrong email or password.</div>
        <?php endif; ?>

        <?php if (isset($_GET['sukses']) && $_GET['sukses'] == 1): ?>
            <div class="alert alert-success">✅ Registration successful! Please login.</div>
        <?php endif; ?>

        <form action="backend/proseslogin.php" method="POST">
            <div class="input-box">
                <input type="email" name="email" placeholder="Email address" required>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn-login-full">Sign in</button>
        </form>

        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign Up</a>
        </div>
    </div>
</body>
</html>