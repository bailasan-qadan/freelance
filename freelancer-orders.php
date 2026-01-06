<?php
session_start();
require 'db.php.inc';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Freelancer') {
    header("Location: login.php");
    exit;
}

$freelancerId = $_SESSION['user_id'];

/* ===== FETCH ORDERS ===== */
$stmt = $pdo->prepare("
    SELECT
        o.order_id,
        o.status,
        o.order_date,
        o.price,
        s.title AS service_title,
        u.first_name,
        u.last_name
    FROM orders o
    JOIN services s ON o.service_id = s.service_id
    JOIN users u ON o.client_id = u.user_id
    WHERE o.freelancer_id = ?
    ORDER BY o.order_date DESC
");
$stmt->execute([$freelancerId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== FETCH USER FOR HEADER ===== */
$userName = 'Freelancer';
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
    <title>My Orders | SkillUp</title>
    <link rel="stylesheet" href="freelancer.css">
</head>
<body>

<div class="page-container">

    <!-- ===== HEADER ===== -->
    <div class="app-header">

        <div class="brand">
            <img src="images/logo.png" alt="SkillUp">
            <span>SkillUp</span>
        </div>

        <nav class="main-nav">
            <a href="freelancer-home.php">Dashboard</a>
            <a href="create-service.php">Create Service</a>
            <a href="freelancer-orders.php"class="active">Orders</a>
            <a href="profile.php" class="profile-link">
                <img src="<?= htmlspecialchars($profilePhoto) ?>" class="profile-pic">
                <span><?= htmlspecialchars($userName) ?></span>
            </a>
        </nav>

        <button onclick="location.href='logout.php'" class="btn-primary">
            Logout
        </button>

    </div>

    <!-- ===== MAIN ===== -->
    <main class="container">

        <div class="card">

            <h2>My Orders</h2>

            <?php if (empty($orders)): ?>
                <p style="text-align:center; color:#666;">No orders yet.</p>
            <?php else: ?>

                <table class="services-table orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Service</th>
                            <th>Client</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['service_title']) ?></td>
                            <td><?= htmlspecialchars($order['first_name'].' '.$order['last_name']) ?></td>
                            <td>$<?= number_format($order['price'], 2) ?></td>
                            <td>
                                <span class="status-badge <?= strtolower($order['status']) ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </td>
                            <td><?= date('Y-m-d', strtotime($order['order_date'])) ?></td>
                            <td>
                                <a class="action-link" href="order-detail.php?id=<?= $order['order_id'] ?>">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>

            <?php endif; ?>

        </div>

    </main>

</div>

</body>
</html>
