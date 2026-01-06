<?php
session_start();
require 'db.php.inc';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';
/* ===== NAV USER DATA ===== */
$navUserName = 'My Profile';
$navPhoto = 'images/default_picture.png';

$stmt = $pdo->prepare("SELECT first_name, last_name, profile_photo FROM users WHERE user_id=?");
$stmt->execute([$userId]);
$navUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($navUser) {
    $navUserName = $navUser['first_name'] . ' ' . $navUser['last_name'];

    if (!empty($navUser['profile_photo']) && file_exists($navUser['profile_photo'])) {
        $navPhoto = $navUser['profile_photo'];
    }
}

/* ===============================
   HANDLE POST REQUESTS
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ===== PROFILE PHOTO UPLOAD ===== */
    if (!empty($_FILES['profile_photo']['name'])) {

        $file = $_FILES['profile_photo'];
        $allowedTypes = ['image/jpeg', 'image/png'];

        if (!in_array($file['type'], $allowedTypes)) {
            $error = "Only JPG and PNG images are allowed.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = "Image must be less than 2MB.";
        } else {
            $uploadDir = "uploads/profiles/$userId/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            move_uploaded_file(
                $file['tmp_name'],
                $uploadDir . "profile_photo.jpg"
            );

            $pdo->prepare("UPDATE users SET profile_photo=? WHERE user_id=?")
                ->execute([$uploadDir . "profile_photo.jpg", $userId]);

            $success = "Profile photo updated successfully.";
        }
    }

    /* ===== PROFILE DATA UPDATE ===== */
    elseif (isset($_POST['first_name'])) {

        $firstName = trim($_POST['first_name']);
        $lastName  = trim($_POST['last_name']);
        $phone     = trim($_POST['phone']);
        $email     = trim($_POST['email']);
        $age       = (int)$_POST['age'];
        $city      = trim($_POST['city']);
        $bio       = trim($_POST['bio']);

        /* EMAIL VALIDATION */
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email=? AND user_id!=?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $error = "This email is already in use.";
            }
        }

        /* PASSWORD CHANGE (OPTIONAL) */
        if (!$error && !empty($_POST['current_password'])) {

            $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id=?");
            $stmt->execute([$userId]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($_POST['current_password'], $hash)) {
                $error = "Current password is incorrect.";
            } elseif (strlen($_POST['new_password']) < 8) {
                $error = "New password must be at least 8 characters.";
            } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
                $error = "Passwords do not match.";
            } else {
                $pdo->prepare("UPDATE users SET password=? WHERE user_id=?")
                    ->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $userId]);
            }
        }

        /* UPDATE PROFILE DATA */
        if (!$error) {
            $stmt = $pdo->prepare("
                UPDATE users SET
                    first_name=?, last_name=?, phone=?, email=?,
                    age=?, city=?, bio=?,
                    professional_title=?, professional_bio=?, skills=?, experience_years=?
                WHERE user_id=?
            ");
            $stmt->execute([
                $firstName,
                $lastName,
                $phone,
                $email,
                $age,
                $city,
                $bio,
                $_POST['professional_title'] ?? null,
                $_POST['professional_bio'] ?? null,
                $_POST['skills'] ?? null,
                $_POST['experience_years'] ?? null,
                $userId
            ]);

            $_SESSION['email'] = $email;
            $success = "Profile updated successfully.";
            $navUserName = $firstName . ' ' . $lastName;
        }
    }
}

/* ===============================
   FETCH USER DATA
================================ */
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$isFreelancer = ($user['role'] === 'Freelancer');

/* ===============================
   FREELANCER STATS
================================ */
$stats = ['total'=>0,'active'=>0,'featured'=>0,'orders'=>0];

if ($isFreelancer) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE freelancer_id=?");
    $stmt->execute([$userId]);
    $stats['total'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE freelancer_id=? AND status='Active'");
    $stmt->execute([$userId]);
    $stats['active'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE freelancer_id=? AND featured_status='Yes'");
    $stmt->execute([$userId]);
    $stats['featured'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE freelancer_id=? AND status='Completed'");
    $stmt->execute([$userId]);
    $stats['orders'] = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile | SkillUp</title>
<link rel="stylesheet" href="app.css">
</head>
<body>

<div class="page-container">

<div class="app-header">

    <div class="brand">
        <img src="images/logo.png" alt="SkillUp Logo">
        <span>SkillUp</span>
    </div>

    <nav class="main-nav">

        <?php if ($user['role'] === 'Client'): ?>
            <a href="client-home.php">Browse Services</a>
            <a href="my-orders.php">My Orders</a>

            <a href="cart.php" class="cart-link">
                <img src="images/cart.png" class="cart-icon" alt="Cart">
                <?php if (!empty($_SESSION['cart'])): ?>
                    <span class="cart-badge"><?= count($_SESSION['cart']) ?></span>
                <?php endif; ?>
            </a>
        <?php else: ?>
            <a href="freelancer-home.php">Dashboard</a>
            <a href="create-service.php">Create Service</a>
            <a href="freelancer-orders.php">Orders</a>
        <?php endif; ?>

        <a href="profile.php" class="profile-link active">
            <img src="<?= htmlspecialchars($navPhoto) ?>" class="profile-pic">
            <span><?= htmlspecialchars($navUserName) ?></span>
        </a>

    </nav>

    <button onclick="location.href='logout.php'" class="btn-primary">
        Logout
    </button>

</div>

<div class="profile-layout">

<!-- LEFT COLUMN -->
<div class="profile-left">
    <div class="profile-card">

        <form method="post" enctype="multipart/form-data">
            <div class="avatar-wrapper">
                <?php
                $photoPath = (!empty($user['profile_photo']) && file_exists($user['profile_photo']))
                    ? $user['profile_photo']
                    : 'images/default_picture.png';
                ?>
                <img src="<?= htmlspecialchars($photoPath) ?>" class="profile-avatar">

                <label for="profile_photo" class="camera-icon">
                    <img src="images/camera.png">
                </label>

                <input type="file" name="profile_photo" id="profile_photo"
                       accept="image/png,image/jpeg" hidden
                       onchange="this.form.submit()">
            </div>
        </form>

        <h3><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></h3>
        <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>

        <span class="role-badge <?= strtolower($user['role']) ?>">
            <?= ucfirst($user['role']) ?>
        </span>
    </div>

    <?php if ($isFreelancer): ?>
    <div class="stats-card">
        <div class="stat"><h4><?= $stats['total'] ?></h4><span>Total Services</span></div>
        <div class="stat green"><h4><?= $stats['active'] ?></h4><span>Active</span></div>
        <div class="stat gold"><h4><?= $stats['featured'] ?>/3</h4><span>Featured</span></div>
        <div class="stat"><h4><?= $stats['orders'] ?></h4><span>Orders</span></div>
    </div>
    <?php endif; ?>
</div>

<!-- RIGHT COLUMN -->
<div class="profile-right">
<div class="card">

<h2>Edit Profile</h2>

<?php if ($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
<?php if ($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>

<form method="post">
<div class="form-row">
    <div>
        <label>First Name</label>
        <input name="first_name" class="input-field" value="<?= htmlspecialchars($user['first_name']) ?>" required>
    </div>
    <div>
        <label>Last Name</label>
        <input name="last_name" class="input-field" value="<?= htmlspecialchars($user['last_name']) ?>" required>
    </div>
</div>

<div class="form-row">
    <div>
        <label>Email</label>
        <input name="email" class="input-field" value="<?= htmlspecialchars($user['email']) ?>" required>
    </div>
    <div>
        <label>Phone</label>
        <input name="phone" class="input-field" value="<?= htmlspecialchars($user['phone']) ?>" required>
    </div>
</div>

<div class="form-row">
    <div>
        <label>Age</label>
        <input type="number" name="age" class="input-field" value="<?= htmlspecialchars($user['age']) ?>" required>
    </div>
    <div>
        <label>City</label>
        <input name="city" class="input-field" value="<?= htmlspecialchars($user['city']) ?>" required>
    </div>
</div>

<div class="form-group">
    <label>About You</label>
    <textarea name="bio" class="input-field" rows="3"><?= htmlspecialchars($user['bio']) ?></textarea>
</div>

<?php if ($isFreelancer): ?>
<h4 class="section-title">Professional Information</h4>

<div class="form-group">
    <label>Professional Title</label>
    <input name="professional_title" class="input-field"
           value="<?= htmlspecialchars($user['professional_title']) ?>">
</div>

<div class="form-group">
    <label>Professional Bio</label>
    <textarea name="professional_bio" class="input-field" rows="4"><?= htmlspecialchars($user['professional_bio']) ?></textarea>
</div>

<div class="form-group">
    <label>Skills</label>
    <input name="skills" class="input-field" value="<?= htmlspecialchars($user['skills']) ?>">
</div>

<div class="form-group">
    <label>Years of Experience</label>
    <input type="number" name="experience_years" class="input-field"
           value="<?= htmlspecialchars($user['experience_years']) ?>">
</div>
<?php endif; ?>

<h4 class="section-title">Change Password</h4>

<div class="form-row">
    <input type="password" name="current_password" class="input-field" placeholder="Current password">
    <input type="password" name="new_password" class="input-field" placeholder="New password">
</div>

<input type="password" name="confirm_password" class="input-field" placeholder="Confirm new password">

<button class="btn-primary">Save Changes</button>

</form>
</div>
</div>

</div>
</div>

</body>
</html>
