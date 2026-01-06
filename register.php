<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | SkillUp</title>
    <link rel="stylesheet" href="log.css">
</head>
<body>
    <div class="page-container">

    <div class="app-header">
        <div class="brand">
            <img src="images/logo.png" alt="SkillUp Logo">
            <span>SkillUp</span>
        </div>

        <nav class="main-nav">
            <a href="login.php" >Login</a>
            <a href="register.php" class="active">Register</a>
        </nav>
    </div>

        <div class="auth-wrapper">
            <div class="auth-form">

        <h2>Create Your Account</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>

            <script>
                setTimeout(function () {
                    window.location.href = "login.php";
                }, 2000);
            </script>

            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>


        <?php if (!empty($_SESSION['errors'])): ?>
            <div class="error-message">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>

<form method="post" action="process-register.php" class="form-container">

    <div class="form-row">
        <div class="form-group">
            <label>First Name</label>
            <input
                type="text"
                name="first_name"
                class="input-field"
                required
                placeholder="Lorem"
            >
        </div>

        <div class="form-group">
            <label>Last Name</label>
            <input
                type="text"
                name="last_name"
                class="input-field"
                required
                placeholder="Ipsum"
            >
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Email</label>
            <input
                type="email"
                name="email"
                class="input-field"
                required
                placeholder="LoremIpsum@gmail.com"
            >
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input
                type="text"
                name="phone"
                class="input-field"
                required
                placeholder="059-989-3581"
            >
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Age</label>
            <input
                type="number"
                name="age"
                class="input-field"
                required
                placeholder="21"
            >
        </div>

        <div class="form-group">
            <label>City</label>
            <select name="city" class="input-field" required>
                <option value="Jerusalem">Jerusalem</option>
                <option value="Ramallah">Ramallah</option>
                <option value="Al-Bireh">Al-Bireh</option>
                <option value="Nablus">Nablus</option>
                <option value="Jenin">Jenin</option>
                <option value="Tulkarm">Tulkarm</option>
                <option value="Qalqilya">Qalqilya</option>
                <option value="Salfit">Salfit</option>
                <option value="Tubas">Tubas</option>
                <option value="Jericho">Jericho</option>
                <option value="Bethlehem">Bethlehem</option>
                <option value="Beit Jala">Beit Jala</option>
                <option value="Beit Sahour">Beit Sahour</option>
                <option value="Hebron">Hebron</option>

                <option value="Gaza City">Gaza City</option>
                <option value="Jabalia">Jabalia</option>
                <option value="Beit Lahia">Beit Lahia</option>
                <option value="Beit Hanoun">Beit Hanoun</option>
                <option value="Deir al-Balah">Deir al-Balah</option>
                <option value="Khan Younis">Khan Younis</option>
                <option value="Rafah">Rafah</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Password</label>
            <input
                type="password"
                name="password"
                class="input-field"
                required
                placeholder=".........."
            >
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input
                type="password"
                name="confirm_password"
                class="input-field"
                required
                placeholder=".........."
            >
        </div>
    </div>

    <div class="form-group">
        <label>About You</label>
        <textarea
            name="bio"
            class="input-field"
            rows="3"
            required
            placeholder="Tell us about yourself..."
        ></textarea>
    </div>

    <div class="form-group">
        <label>Account Type</label>
        <div class="radio-group">
            <label>
                <input type="radio" name="role" value="client" required> Client
            </label>
            <label>
                <input type="radio" name="role" value="freelancer" required> Freelancer
            </label>
        </div>
    </div>

    <button type="submit" class="btn-primary">Register</button>
</form>


        <p class="switch-link">
            Already have an account?
            <a href="login.php">Login</a>
        </p>

    </div>
</div>

</body>
</html>
