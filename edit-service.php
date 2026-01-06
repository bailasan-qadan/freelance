<?php
session_start();
require 'db.php.inc';

/* ===== AUTH ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Freelancer') {
    header("Location: login.php");
    exit;
}

$freelancerId = $_SESSION['user_id'];
$serviceId = $_GET['id'] ?? null;

if (!$serviceId) {
    header("Location: freelancer-dashboard.php");
    exit;
}

/* ===== UPDATE ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare("
        UPDATE services SET
            title=?,
            category=?,
            subcategory=?,
            description=?,
            price=?,
            delivery_time=?,
            revisions_included=?
        WHERE service_id=? AND freelancer_id=?
    ");

    $stmt->execute([
        $_POST['title'],
        $_POST['category'],
        $_POST['subcategory'],
        $_POST['description'],
        $_POST['price'],
        $_POST['delivery_time'],
        $_POST['revisions'],
        $serviceId,
        $freelancerId
    ]);

    // 🔑 PRG pattern
    header("Location: edit-service.php?id=$serviceId&updated=1");
    exit;
}

/* ===== FETCH SERVICE (ALWAYS FRESH) ===== */
$stmt = $pdo->prepare("SELECT * FROM services WHERE service_id=? AND freelancer_id=?");
$stmt->execute([$serviceId, $freelancerId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    header("Location: freelancer-dashboard.php");
    exit;
}

/* ===== SUCCESS MESSAGE ===== */
$success = isset($_GET['updated']) ? "Service updated successfully." : '';

/* ===== FETCH USER FOR HEADER ===== */
$userName = 'My Profile';
$profilePhoto = 'images/default_picture.png';

$stmt = $pdo->prepare("
    SELECT first_name, last_name, profile_photo
    FROM users
    WHERE user_id = ?
");
$stmt->execute([$freelancerId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $userName = $user['first_name'] . ' ' . $user['last_name'];
    if (!empty($user['profile_photo'])) {
        $profilePhoto = $user['profile_photo'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Service | SkillUp</title>
    <link rel="stylesheet" href="freelancer.css">
</head>
<body>

<div class="page-container">

    <div class="app-header">
        <div class="brand">
            <img src="images/logo.png" alt="SkillUp">
            <span>SkillUp</span>
        </div>

        <nav class="main-nav">
            <a href="freelancer-home.php">Dashboard</a>
            <a href="create-service.php" class="active">Create Service</a>
            <a href="freelancer-orders.php">Orders</a>
            <a href="profile.php" class="profile-link">
                <img src="<?= htmlspecialchars($profilePhoto) ?>" class="profile-pic">
                <span><?= htmlspecialchars($userName) ?></span>
            </a>
        </nav>

        <button onclick="location.href='logout.php'" class="btn-primary">
            Logout
        </button>
    </div>

    <main class="container card form-container">

        <h2>Edit Service</h2>

        <?php if ($success): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" class="styled-form">

            <div class="form-group">
                <label>Service Title</label>
                <input class="input-field" name="title"
                       value="<?= htmlspecialchars($service['title']) ?>" required>
            </div>

            <div class="form-group">
                <label>Category</label>
                <input class="input-field" name="category"
                       value="<?= htmlspecialchars($service['category']) ?>" required>
            </div>

            <div class="form-group">
                <label>Subcategory</label>
                <input class="input-field" name="subcategory"
                       value="<?= htmlspecialchars($service['subcategory']) ?>" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea class="input-field" rows="5"
                          name="description" required><?= htmlspecialchars($service['description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Price</label>
                    <input class="input-field" type="number" name="price"
                           value="<?= $service['price'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Delivery Time</label>
                    <input class="input-field" type="number" name="delivery_time"
                           value="<?= $service['delivery_time'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Revisions</label>
                    <input class="input-field" type="number" name="revisions"
                           value="<?= $service['revisions_included'] ?>" required>
                </div>
            </div>

            <button class="btn-primary full-width">Save Changes</button>

        </form>

    </main>

</div>

</body>
</html>
