<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Polibatam</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <img src="logo.png" class="logo" alt="Polibatam Logo">


    <form id="loginForm" method="POST" action="login.php">
        <input type="text" name="nim" id="nim" placeholder="••••••••" required>

        <input type="password" name="password" id="password" placeholder="••••••••" required>

        <button type="submit">Log in</button>

        <a href="lupa_pw.html" class="forgot">Lupa kata sandi?</a>
    </form>
</div>

<script src="script.js"></script>
</body>
</html>
