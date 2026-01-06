<?php
session_start();
require 'db.php.inc';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Freelancer') {
    header("Location: login.php");
    exit;
}

$freelancerId = $_SESSION['user_id'];
$errors = [];
$success = '';

/* ===== HANDLE SUBMISSION ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim($_POST['title']);
    $category    = $_POST['category'];
    $subcategory = trim($_POST['subcategory']);
    $description = trim($_POST['description']);
    $price       = $_POST['price'];
    $delivery    = $_POST['delivery_time'];
    $revisions   = $_POST['revisions'];

    if ($title === '' || $description === '') {
        $errors[] = "Title and description are required.";
    }

    /* ===== UPLOAD IMAGES ===== */
    $uploadDir = "uploads/services/$freelancerId/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    function uploadImage($file, $prefix, $dir) {
        if (!empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $name = $prefix . '_' . time() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $dir . $name);
            return $dir . $name;
        }
        return null;
    }

    $serviceImage = uploadImage($_FILES['service_image'], 'main', $uploadDir);
    $img1 = uploadImage($_FILES['image_1'], 'work1', $uploadDir);
    $img2 = uploadImage($_FILES['image_2'], 'work2', $uploadDir);
    $img3 = uploadImage($_FILES['image_3'], 'work3', $uploadDir);

    if (!$serviceImage) {
        $errors[] = "Main service image is required.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO services
            (service_id, freelancer_id, title, category, subcategory, description,
             price, delivery_time, revisions_included,
             service_image, image_1, image_2, image_3,
             status, featured_status)
            VALUES
            (UUID_SHORT(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', 'No')
        ");

        $stmt->execute([
            $freelancerId,
            $title,
            $category,
            $subcategory,
            $description,
            $price,
            $delivery,
            $revisions,
            $serviceImage,
            $img1,
            $img2,
            $img3
        ]);

        $success = "Service created successfully.";
    }
}
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
    <title>Create Service | SkillUp</title>
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
            <a href="freelancer-home.php" >Dashboard</a>
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

        <h2>Create New Service</h2>

        <?php if ($errors): ?>
            <div class="error-message">
                <?php foreach ($errors as $e): ?><p><?= $e ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="styled-form">

            <div class="form-group">
                <label>Service Title</label>
                <input class="input-field" name="title" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <select class="input-field" name="category">
                        <option>Graphic Design</option>
                        <option>Web Development</option>
                        <option>Writing & Translation</option>
                        <option>Digital Marketing</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Subcategory</label>
                    <input class="input-field" name="subcategory">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea class="input-field" rows="5" name="description"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Price ($)</label>
                    <input class="input-field" type="number" name="price" required>
                </div>

                <div class="form-group">
                    <label>Delivery Time (days)</label>
                    <input class="input-field" type="number" name="delivery_time" required>
                </div>

                <div class="form-group">
                    <label>Revisions</label>
                    <input class="input-field" type="number" name="revisions" required>
                </div>
            </div>

            <div class="form-group">
                <label>Main Service Image</label>
                <input type="file" name="service_image" required>
            </div>

            <div class="form-group">
                <label>Portfolio Images</label>
                <input type="file" name="image_1">
                <input type="file" name="image_2">
                <input type="file" name="image_3">
            </div>

            <button class="btn-primary full-width">Create Service</button>

        </form>

    </main>

</div>

</body>
</html>
