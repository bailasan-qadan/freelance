<?php
session_start();
require 'db.php.inc';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Client') {
    header("Location: login.php");
    exit;
}

$clientId = $_SESSION['user_id'];
$orderId = $_GET['id'] ?? null;

if (!$orderId) {
    header("Location: my-orders.php");
    exit;
}

/* ===== FETCH USER FOR HEADER ===== */
$userName = 'My Profile';
$profilePhoto = 'images/default_picture.png';

$stmt = $pdo->prepare("
    SELECT first_name, last_name, profile_photo
    FROM users
    WHERE user_id = ?
");
$stmt->execute([$clientId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $userName = $user['first_name'].' '.$user['last_name'];
    if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])) {
        $profilePhoto = $user['profile_photo'];
    }
}

/* ===== FETCH ORDER ===== */
$stmt = $pdo->prepare("
    SELECT 
        o.*,
        s.title AS service_title,
        u.first_name AS freelancer_first,
        u.last_name AS freelancer_last
    FROM orders o
    JOIN services s ON o.service_id = s.service_id
    JOIN users u ON o.freelancer_id = u.user_id
    WHERE o.order_id = ? AND o.client_id = ?
");
$stmt->execute([$orderId, $clientId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: my-orders.php");
    exit;
}
$revisionSuccess = '';
$revisionError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['revision_notes'])) {

    $notes = trim($_POST['revision_notes']);
    $revisionFilePath = null;

    if ($notes === '') {
        $revisionError = "Revision notes cannot be empty.";
    } else {

        /* ===== HANDLE FILE UPLOAD (OPTIONAL) ===== */
        if (!empty($_FILES['revision_file']['name'])) {

            $uploadDir = 'uploads/revisions/'.$orderId.'/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time().'_'.basename($_FILES['revision_file']['name']);
            $targetPath = $uploadDir.$fileName;

            if (move_uploaded_file($_FILES['revision_file']['tmp_name'], $targetPath)) {
                $revisionFilePath = $targetPath;
            }
        }

        /* ===== INSERT REVISION REQUEST ===== */
        $stmt = $pdo->prepare("
            INSERT INTO revision_requests 
            (order_id, revision_notes, revision_file, request_status)
            VALUES (?, ?, ?, 'Pending')
        ");
        $stmt->execute([$orderId, $notes, $revisionFilePath]);

        $revisionSuccess = "Revision request sent successfully.";
    }
}
/* ===== FETCH DELIVERED FILES ===== */
$stmt = $pdo->prepare("
    SELECT *
    FROM file_attachments
    WHERE order_id = ? AND file_type = 'deliverable'
    ORDER BY upload_timestamp DESC
");
$stmt->execute([$orderId]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?= htmlspecialchars($orderId) ?> | SkillUp</title>
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

    <!-- ===== ORDER DETAILS ===== -->
    <main class="container">

        <h2>Order Details</h2>

        <div class="card" style="margin-bottom:30px;">

            <div class="profile-row">
                <strong>Order ID</strong>
                <span>#<?= htmlspecialchars($order['order_id']) ?></span>
            </div>

            <div class="profile-row">
                <strong>Service</strong>
                <span><?= htmlspecialchars($order['service_title']) ?></span>
            </div>

            <div class="profile-row">
                <strong>Freelancer</strong>
                <span><?= htmlspecialchars($order['freelancer_first'].' '.$order['freelancer_last']) ?></span>
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
                <strong>Order Date</strong>
                <span><?= date('F j, Y', strtotime($order['order_date'])) ?></span>
            </div>

            <div class="profile-row">
                <strong>Expected Delivery</strong>
                <span><?= htmlspecialchars($order['expected_delivery']) ?></span>
            </div>

        </div>

        <!-- ===== DELIVERED FILES ===== -->
        <h2>Delivered Files</h2>

        <div class="card">

            <?php if (empty($files)): ?>
                <p style="text-align:center; color:#666;">
                    No files delivered yet.
                </p>
            <?php else: ?>

                <?php foreach ($files as $file): ?>
                    <div class="profile-row">
                        <span><?= htmlspecialchars($file['original_filename']) ?></span>
                        <a 
                            href="<?= htmlspecialchars($file['file_path']) ?>" 
                            class="btn-primary"
                            download
                        >
                            Download
                        </a>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
        <h2>Request Revision</h2>

<div class="card">

    <?php if ($revisionSuccess): ?>
        <div class="success-message"><?= $revisionSuccess ?></div>
    <?php endif; ?>

    <?php if ($revisionError): ?>
        <div class="error-message"><?= $revisionError ?></div>
    <?php endif; ?>

    <?php if ($order['status'] === 'Delivered'): ?>

        <form method="POST" enctype="multipart/form-data" class="styled-form">

            <div class="form-group">
                <label>Revision Notes</label>
                <textarea 
                    name="revision_notes" 
                    class="input-field" 
                    rows="5"
                    placeholder="Describe what you want to be changed..."
                    required
                ></textarea>
            </div>

            <div class="form-group">
                <label>Attach File (optional)</label>
                <input type="file" name="revision_file">
            </div>

            <button class="btn-primary full-width">
                Submit Revision Request
            </button>

        </form>

    <?php else: ?>
        <p style="text-align:center; color:#666;">
            Revision requests are available after delivery.
        </p>
    <?php endif; ?>

</div>


    </main>

</div>

</body>
</html>
