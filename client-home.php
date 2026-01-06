<?php
session_start();
require 'db.php.inc';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Client') {
    header("Location: login.php");
    exit;
}

/* ===== FETCH USER FOR HEADER ===== */
$userName = 'My Profile';
$profilePhoto = 'images/default_picture.png';

$stmt = $pdo->prepare("SELECT first_name, last_name, profile_photo FROM users WHERE user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$navUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($navUser) {
    $userName = $navUser['first_name'] . ' ' . $navUser['last_name'];

    if (!empty($navUser['profile_photo']) && file_exists($navUser['profile_photo'])) {
        $profilePhoto = $navUser['profile_photo'];
    }
}

/* ===== FETCH SERVICES (WITH SEARCH) ===== */
$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT s.*, u.first_name, u.last_name
        FROM services s
        JOIN users u ON s.freelancer_id = u.user_id
        WHERE s.status = 'Active'
        AND (
            s.title LIKE :search
            OR s.category LIKE :search
            OR s.subcategory LIKE :search
            OR u.first_name LIKE :search
            OR u.last_name LIKE :search
        )
        ORDER BY s.created_date DESC
    ");
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt = $pdo->prepare("
        SELECT s.*, u.first_name, u.last_name
        FROM services s
        JOIN users u ON s.freelancer_id = u.user_id
        WHERE s.status = 'Active'
        ORDER BY s.created_date DESC
    ");
    $stmt->execute();
}

$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Services | SkillUp</title>
    <link rel="stylesheet" href="app.css">
    <link rel="stylesheet" href="search.css">
</head>
<body>

<div class="page-container">

    <!-- ===== HEADER ===== -->
    <div class="app-header">

        <div class="brand">
            <img src="images/logo.png" alt="SkillUp Logo">
            <span>SkillUp</span>
        </div>

        <nav class="main-nav">

            <a href="client-home.php" class="active">Browse Services</a>
            <a href="my-orders.php">My Orders</a>

            <!-- CART ICON -->
            <a href="cart.php" class="cart-link">
                <img src="images/cart.png" alt="Cart" class="cart-icon">
                <?php if (!empty($_SESSION['cart'])): ?>
                    <span class="cart-badge"><?= count($_SESSION['cart']) ?></span>
                <?php endif; ?>
            </a>

            <!-- PROFILE -->
            <a href="profile.php" class="profile-link">
                <img src="<?= htmlspecialchars($profilePhoto) ?>" class="profile-pic">
                <span><?= htmlspecialchars($userName) ?></span>
            </a>


        </nav>

        <button onclick="location.href='logout.php'" class="btn-primary">
            Logout
        </button>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="container">

        <h2>Available Services</h2>
        <form method="GET" class="search-bar">
            <img src="images/search.png" alt="Search">
            <input
                type="text"
                name="q"
                placeholder="Search for services, categories, or freelancers..."
                value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
            >
            <button type="submit">Search</button>
        </form>

        <div class="services-grid">
            <?php if (empty($services)): ?>
            <p style="text-align:center; color:#666;">
                No services found matching your search.
            </p>
        <?php else: ?>
            <?php foreach ($services as $service): ?>

                <?php
                $image = trim($service['service_image'] ?? '');
                if ($image === '' || !file_exists($image)) {
                    $image = 'images/placeholder.png';
                }
                ?>

                <div class="service-card">

                    <img src="<?= htmlspecialchars($image) ?>" alt="Service Image">

                    <h3><?= htmlspecialchars($service['title']) ?></h3>

                    <p class="freelancer">
                        <?= htmlspecialchars($service['first_name'] . ' ' . $service['last_name']) ?>
                    </p>

                    <p class="category"><?= htmlspecialchars($service['category']) ?></p>

                    <p class="price">$<?= number_format($service['price'], 2) ?></p>

                    <a href="service-detail.php?id=<?= $service['service_id'] ?>" class="btn-primary">
                        View Details
                    </a>

                </div>

            <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>

</div>

</body>
</html>
