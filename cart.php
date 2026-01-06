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
    if (!empty($navUser['profile_photo'])) {
        $profilePhoto = $navUser['profile_photo'];
    }
}

/* ===== FETCH CART SERVICES ===== */
$cartServices = [];
$total = 0;

if (!empty($_SESSION['cart'])) {

    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));

    $stmt = $pdo->prepare("
        SELECT service_id, title, price, service_image
        FROM services
        WHERE service_id IN ($placeholders)
    ");
    $stmt->execute($_SESSION['cart']);
    $cartServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cartServices as $s) {
        $total += $s['price'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart | SkillUp</title>
    <link rel="stylesheet" href="app.css">
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

            <a href="cart.php" class="cart-link active">
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

    <!-- ===== CART CONTENT ===== -->
    <main class="container">

        <h2>My Cart</h2>

        <?php if (empty($cartServices)): ?>

            <div class="card" style="text-align:center;">
                <p>Your cart is empty.</p>
                <br>
                <a href="client-home.php" class="btn-primary">Browse Services</a>
            </div>

        <?php else: ?>

            <?php foreach ($cartServices as $service): ?>

                <?php
                $img = !empty($service['service_image'])
                    ? $service['service_image']
                    : 'images/placeholder.png';
                ?>

                <div class="card" style="margin-bottom:20px;">

                    <div class="profile-row">
                        <img src="<?= htmlspecialchars($img) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
                        <div>
                            <h3><?= htmlspecialchars($service['title']) ?></h3>
                            <p class="price">$<?= number_format($service['price'], 2) ?></p>

                            <a href="remove-from-cart.php?id=<?= $service['service_id'] ?>"
                               style="color:#e74c3c; font-size:14px;">
                                Remove
                            </a>
                        </div>
                    </div>

                </div>

            <?php endforeach; ?>

            <div class="card">
                <h3>Total: $<?= number_format($total, 2) ?></h3>
                <br>
                <a href="place-order.php" class="btn-primary">Proceed to Checkout</a>
            </div>

        <?php endif; ?>

    </main>

</div>

</body>
</html>
