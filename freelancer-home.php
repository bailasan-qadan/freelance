<?php
session_start();
require 'db.php.inc';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Freelancer') {
    header("Location: login.php");
    exit;
}

$freelancerId = $_SESSION['user_id'];

/* ===== FETCH USER FOR HEADER ===== */
$userName = 'My Profile';
$profilePhoto = 'images/default_picture.png';

$stmt = $pdo->prepare("SELECT first_name, last_name, profile_photo FROM users WHERE user_id = ?");
$stmt->execute([$freelancerId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $userName = $user['first_name'] . ' ' . $user['last_name'];
    if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])) {
        $profilePhoto = $user['profile_photo'];
    }
}

/* ===== STATS ===== */
$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'Active') AS active,
        SUM(featured_status = 'Yes') AS featured
    FROM services
    WHERE freelancer_id = ?
");
$stmt->execute([$freelancerId]);
$stats = $stmt->fetch();

/* ===== SERVICES ===== */
$stmt = $pdo->prepare("
    SELECT *
    FROM services
    WHERE freelancer_id = ?
    ORDER BY created_date DESC
");
$stmt->execute([$freelancerId]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Freelancer Dashboard | SkillUp</title>
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
            <a href="freelancer-home.php" class="active">Dashboard</a>
            <a href="create-service.php">Create Service</a>
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

    <!-- ===== MAIN CONTENT ===== -->
    <main class="container dashboard-page">

        <h2>My Services Overview</h2>

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $stats['total'] ?></h3>
                <p>Total Services</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['active'] ?></h3>
                <p>Active</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['featured'] ?>/3</h3>
                <p>Featured</p>
            </div>
        </div>

        <!-- SERVICES TABLE -->
        <div class="card">
            <table class="services-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= htmlspecialchars($service['title']) ?></td>
                        <td><?= htmlspecialchars($service['category']) ?></td>
                        <td>$<?= number_format($service['price'], 2) ?></td>
                        <td><?= $service['status'] ?></td>
                        <td><?= $service['featured_status'] ?></td>
                        <td>
                            <a class="action-link" href="edit-service.php?id=<?= $service['service_id'] ?>">
                                Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>

</div>

</body>
</html>
