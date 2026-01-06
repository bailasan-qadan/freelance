<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | SkillUp</title>
    <link rel="stylesheet" href="log.css">
    <meta http-equiv="Cache-Control" content="no-store">
</head>
<body>

 <div class="page-container">

    <div class="app-header">
        <div class="brand">
            <img src="images/logo.png" alt="SkillUp Logo">
            <span>SkillUp</span>
        </div>

        <nav class="main-nav">
            <a href="login.php" class="active">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </div>

        <div class="auth-wrapper">
            <div class="auth-form">

        <h2>Login to Your Account</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="authenticate.php" class="form-container" autocomplete="off">

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="input-field" required placeholder="LoremIpsum@gmail.com">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="input-field" required placeholder="..........">
            </div>

            <button type="submit" class="btn-primary">Login</button>
        </form>

        <p class="switch-link">
            Don’t have an account?
            <a href="register.php">Register</a>
        </p>

    </div>
</div>

</body>
</html>
