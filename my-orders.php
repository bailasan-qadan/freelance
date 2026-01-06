<?php
session_start();
require 'db.php.inc';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Client') {
    header("Location: login.php");
    exit;
}

/* ===== FETCH USER FOR NAV ===== */
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
    if (!empty($navUser['profile_photo']) && file_exists($navUser['profile_photo'])) {
        $profilePhoto = $navUser['profile_photo'];
    }
}

/* ===== FETCH ORDERS ===== */
$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE client_id = ?
    ORDER BY order_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders | SkillUp</title>
    <link rel="stylesheet" href="app.css">
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
            <a href="client-home.php">Browse Services</a>
            <a href="my-orders.php" class="active">My Orders</a>

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

        <button onclick="location.href='logout.php'" class="btn-primary">
            Logout
        </button>
    </div>

    <!-- ===== ORDERS ===== -->
    <main class="container">
        <h2>My Orders</h2>

        <?php if (empty($orders)): ?>

            <div class="card" style="text-align:center;">
                <p>You have no orders yet.</p>
                <br>
                <a href="client-home.php" class="btn-primary">Browse Services</a>
            </div>

        <?php else: ?>

            <?php foreach ($orders as $order): ?>

                <div class="card" style="margin-bottom:25px;">

                    <div class="profile-row">
                        <strong>Order #<?= htmlspecialchars($order['order_id']) ?></strong>
                        <span><?= date('F j, Y', strtotime($order['order_date'])) ?></span>
                    </div>

                    <div class="profile-row">
                        <strong>Status</strong>
                        <span><?= htmlspecialchars($order['status']) ?></span>
                    </div>

                    <div class="profile-row">
                        <strong>Price</strong>
                        <span>$<?= number_format($order['price'], 2) ?></span>
                    </div>

                    <div class="profile-row">
                        <strong>Service</strong>
                        <span><?= htmlspecialchars($order['service_title']) ?></span>
                    </div>

                    <div class="profile-row">
                        <strong>Expected Delivery</strong>
                        <span><?= htmlspecialchars($order['expected_delivery']) ?></span>
                    </div>
                    <div style="margin-top:15px;">
                        <a href="client-order-detail.php?id=<?= $order['order_id'] ?>" 
                        class="btn-primary" 
                        style="display:inline-block; padding:8px 18px; font-size:14px;">
                            View Details
                        </a>
                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>
    </main>

</div>

</body>
</html>
