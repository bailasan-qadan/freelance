<?php
session_start();
require 'db.php.inc';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Client') {
    header("Location: login.php");
    exit;
}

/* ===== VALIDATE SERVICE ID ===== */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: client-home.php");
    exit;
}

$serviceId = $_GET['id'];

/* ===== FETCH LOGGED USER (FOR NAV) ===== */
$userName = 'My Profile';
$profilePhoto = 'images/default_picture.png';

$stmt = $pdo->prepare("
    SELECT first_name, last_name, profile_photo
    FROM users
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$navUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($navUser) {
    $userName = $navUser['first_name'] . ' ' . $navUser['last_name'];
    if (!empty($navUser['profile_photo'])) {
        $profilePhoto = $navUser['profile_photo'];
    }
}

/* ===== FETCH SERVICE DETAILS ===== */
$stmt = $pdo->prepare("
    SELECT 
        s.*,
        s.image_1, s.image_2, s.image_3,
        u.first_name,
        u.last_name,
        u.professional_title,
        u.professional_bio,
        u.skills
    FROM services s
    JOIN users u ON s.freelancer_id = u.user_id
    WHERE s.service_id = ? AND s.status = 'Active'
");
$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    header("Location: client-home.php");
    exit;
}

/* ===== MAIN IMAGE ===== */
$mainImage = !empty($service['service_image'])
    ? $service['service_image']
    : 'images/placeholder.png';

/* ===== GALLERY IMAGES (3 freelancer work images) ===== */
$galleryImages = [];

for ($i = 1; $i <= 3; $i++) {
    $imgField = "image_$i";
    if (!empty($service[$imgField])) {
        $galleryImages[] = $service[$imgField];
    }
}

if (empty($galleryImages)) {
    $galleryImages[] = 'images/placeholder.png';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($service['title']) ?> | SkillUp</title>
    <link rel="stylesheet" href="service-view.css">
</head>
<body>

<div class="page-container">

    <!-- ===== HEADER ===== -->
    <div class="app-header">

        <div class="brand">
            <img src="images/logo.png">
            <span>SkillUp</span>
        </div>

        <nav class="main-nav">
            <a href="client-home.php">Browse Services</a>
            <a href="my-orders.php">My Orders</a>

            <a href="cart.php" class="cart-link">
                <img src="images/cart.png" class="cart-icon">
                <?php if (!empty($_SESSION['cart'])): ?>
                    <span class="cart-badge"><?= count($_SESSION['cart']) ?></span>
                <?php endif; ?>
            </a>

            <a href="profile.php" class="profile-link">
                <img src="<?= htmlspecialchars($profilePhoto) ?>" class="profile-pic">
                <span><?= htmlspecialchars($userName) ?></span>
            </a>
        </nav>

        <button onclick="location.href='logout.php'" class="btn-primary">Logout</button>
    </div>

    <!-- ===== SERVICE DETAILS ===== -->
    <div class="card">

        <div class="profile-layout">

            <!-- LEFT COLUMN -->
            <div class="service-card">

                <!-- MAIN IMAGE -->
        <img src="<?= htmlspecialchars($mainImage) ?>" class="service-main-image">
                <h3 style="margin-top:20px;"><?= htmlspecialchars($service['title']) ?></h3>
                <p class="category"><?= htmlspecialchars($service['category']) ?></p>
                <p class="price">$<?= number_format($service['price'], 2) ?></p>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="profile-right">

                <h2>Service Description</h2>
                <p><?= nl2br(htmlspecialchars($service['description'])) ?></p>

                <div class="section-title">Service Details</div>

                <div class="profile-row">
                    <strong>Delivery Time:</strong>
                    <span><?= $service['delivery_time'] ?> days</span>
                </div>

                <div class="profile-row">
                    <strong>Revisions Included:</strong>
                    <span><?= $service['revisions_included'] ?></span>
                </div>

                <div class="profile-row">
                    <strong>Category:</strong>
                    <span><?= htmlspecialchars($service['category']) ?></span>
                </div>

                <div class="profile-row">
                    <strong>Subcategory:</strong>
                    <span><?= htmlspecialchars($service['subcategory']) ?></span>
                </div>
                <div class="section-title">Freelancer Work</div>
                    <div class="freelancer-gallery">
                        <?php foreach ($galleryImages as $img): ?>
                            <img src="<?= htmlspecialchars($img) ?>" alt="Freelancer work">
                        <?php endforeach; ?>
                </div>


                <div class="section-title">About the Freelancer</div>

                <p>
                    <strong>
                        <?= htmlspecialchars($service['first_name'] . ' ' . $service['last_name']) ?>
                    </strong>
                    <?php if (!empty($service['professional_title'])): ?>
                        – <?= htmlspecialchars($service['professional_title']) ?>
                    <?php endif; ?>
                </p>

                <?php if (!empty($service['professional_bio'])): ?>
                    <p><?= nl2br(htmlspecialchars($service['professional_bio'])) ?></p>
                <?php endif; ?>

                <?php if (!empty($service['skills'])): ?>
                    <div class="section-title">Skills</div>
                    <p><?= htmlspecialchars($service['skills']) ?></p>
                <?php endif; ?>

                <br>

                <a href="add-to-cart.php?id=<?= $service['service_id'] ?>" class="btn-primary">
                    Add to Cart
                </a>

            </div>


        </div>

    </div>

</div>

</body>
</html>
