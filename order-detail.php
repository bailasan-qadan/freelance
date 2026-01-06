<?php
session_start();
require 'db.php.inc';

/* ===== AUTH ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Freelancer') {
    header("Location: login.php");
    exit;
}

$freelancerId = $_SESSION['user_id'];
$orderId = $_GET['id'] ?? null;

if (!$orderId) {
    header("Location: freelancer-orders.php");
    exit;
}

/* ===== FETCH ORDER ===== */
$stmt = $pdo->prepare("
    SELECT 
        o.*,
        s.title AS service_title,
        u.first_name,
        u.last_name
    FROM orders o
    JOIN services s ON o.service_id = s.service_id
    JOIN users u ON o.client_id = u.user_id
    WHERE o.order_id = ? AND o.freelancer_id = ?
");
$stmt->execute([$orderId, $freelancerId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: freelancer-orders.php");
    exit;
}

$success = '';
$error = '';
/* ===== FETCH REVISION REQUESTS ===== */
$stmt = $pdo->prepare("
    SELECT *
    FROM revision_requests
    WHERE order_id = ?
    ORDER BY request_date DESC
");
$stmt->execute([$orderId]);
$revisions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$revisionSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['revision_id'])) {

    $revisionId = $_POST['revision_id'];
    $response = trim($_POST['freelancer_response']);
    $decision = $_POST['decision']; // Accepted or Rejected
    $deliverablePath = null;

    /* ===== UPLOAD REVISED FILE ===== */
    if (!empty($_FILES['deliverable']['name'])) {

        $uploadDir = 'uploads/deliverables/'.$orderId.'/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time().'_'.basename($_FILES['deliverable']['name']);
        $targetPath = $uploadDir.$fileName;

        if (move_uploaded_file($_FILES['deliverable']['tmp_name'], $targetPath)) {
            $deliverablePath = $targetPath;

            /* Save deliverable */
            $stmt = $pdo->prepare("
                INSERT INTO file_attachments
                (order_id, file_path, original_filename, file_size, file_type)
                VALUES (?, ?, ?, ?, 'deliverable')
            ");
            $stmt->execute([
                $orderId,
                $deliverablePath,
                $_FILES['deliverable']['name'],
                $_FILES['deliverable']['size']
            ]);
        }
    }

    /* ===== UPDATE REVISION REQUEST ===== */
    $stmt = $pdo->prepare("
        UPDATE revision_requests
        SET 
            request_status = ?,
            freelancer_response = ?,
            response_date = NOW()
        WHERE revision_id = ?
    ");
    $stmt->execute([$decision, $response, $revisionId]);

    /* ===== UPDATE ORDER STATUS ===== */
    if ($decision === 'Accepted') {
        $stmt = $pdo->prepare("
            UPDATE orders SET status = 'Delivered' WHERE order_id = ?
        ");
        $stmt->execute([$orderId]);
    }

    $revisionSuccess = "Revision handled successfully.";
}

/* ===== HANDLE FILE UPLOAD ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['deliverable'])) {

    if ($_FILES['deliverable']['error'] === 0) {

        $uploadDir = "uploads/deliverables/$orderId/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['deliverable']['name']);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['deliverable']['tmp_name'], $filePath)) {

            $stmt = $pdo->prepare("
                INSERT INTO file_attachments 
                (order_id, file_path, original_filename, file_size, file_type)
                VALUES (?, ?, ?, ?, 'deliverable')
            ");
            $stmt->execute([
                $orderId,
                $filePath,
                $_FILES['deliverable']['name'],
                $_FILES['deliverable']['size']
            ]);

            // Auto set status to Delivered
            $stmt = $pdo->prepare("
                UPDATE orders SET status='Delivered' WHERE order_id=?
            ");
            $stmt->execute([$orderId]);

            $order['status'] = 'Delivered';
            $success = "Deliverable uploaded successfully.";

        } else {
            $error = "File upload failed.";
        }

    } else {
        $error = "Please select a valid file.";
    }
}

/* ===== UPDATE STATUS MANUALLY ===== */
if (isset($_POST['status'])) {
    $newStatus = $_POST['status'];

    $stmt = $pdo->prepare("
        UPDATE orders SET status=? WHERE order_id=? AND freelancer_id=?
    ");
    $stmt->execute([$newStatus, $orderId, $freelancerId]);

    $order['status'] = $newStatus;
    $success = "Order status updated.";
}

/* ===== FETCH FILES ===== */
$stmt = $pdo->prepare("
    SELECT * FROM file_attachments
    WHERE order_id = ? AND file_type='deliverable'
    ORDER BY upload_timestamp DESC
");
$stmt->execute([$orderId]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== FETCH USER FOR HEADER ===== */
$stmt = $pdo->prepare("SELECT first_name, last_name, profile_photo FROM users WHERE user_id=?");
$stmt->execute([$freelancerId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$userName = $user['first_name'] . ' ' . $user['last_name'];
$profilePhoto = $user['profile_photo'] ?: 'images/default_picture.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?= $orderId ?> | SkillUp</title>
    <link rel="stylesheet" href="freelancer.css">
</head>
<body>

<div class="page-container">

<div class="app-header">
    <div class="brand">
        <img src="images/logo.png">
        <span>SkillUp</span>
    </div>

    <nav class="main-nav">
        <a href="freelancer-home.php">Dashboard</a>
        <a href="create-service.php">Create Service</a>
        <a href="freelancer-orders.php" class="active">Orders</a>
        <a href="profile.php" class="profile-link">
            <img src="<?= $profilePhoto ?>" class="profile-pic">
            <span><?= htmlspecialchars($userName) ?></span>
        </a>
    </nav>

    <button onclick="location.href='logout.php'" class="btn-primary">Logout</button>
</div>

<main class="container card form-container">

<h2>Order Details</h2>

<?php if ($success): ?><div class="success-message"><?= $success ?></div><?php endif; ?>
<?php if ($error): ?><div class="error-message"><?= $error ?></div><?php endif; ?>

<div class="order-info">
    <p><strong>Service:</strong> <?= htmlspecialchars($order['service_title']) ?></p>
    <p><strong>Client:</strong> <?= htmlspecialchars($order['first_name'].' '.$order['last_name']) ?></p>
    <p><strong>Price:</strong> $<?= number_format($order['price'],2) ?></p>
    <p><strong>Status:</strong> <?= $order['status'] ?></p>
</div>

<hr style="margin:25px 0;">

<h3>Upload Deliverable</h3>
<form method="POST" enctype="multipart/form-data" class="styled-form">
    <input type="file" name="deliverable" required>
    <button class="btn-primary full-width">Upload File</button>
</form>

<hr style="margin:25px 0;">

<h3>Delivered Files</h3>

<?php if (!$files): ?>
    <p style="color:#777;">No files uploaded yet.</p>
<?php else: ?>
    <ul>
        <?php foreach ($files as $file): ?>
            <li>
                <a href="<?= $file['file_path'] ?>" target="_blank">
                    <?= htmlspecialchars($file['original_filename']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<hr style="margin:25px 0;">
<h2>Revision Requests</h2>


<?php if (empty($revisions)): ?>

    <p style="text-align:center; color:#666;">
        No revision requests.
    </p>

<?php else: ?>

    <?php foreach ($revisions as $rev): ?>

        <div class="card" style="margin-bottom:20px;">

            <p><strong>Requested on:</strong>
                <?= date('F j, Y', strtotime($rev['request_date'])) ?>
            </p>

            <p><strong>Notes:</strong><br>
                <?= nl2br(htmlspecialchars($rev['revision_notes'])) ?>
            </p>

            <?php if (!empty($rev['revision_file'])): ?>
                <p>
                    <a href="<?= htmlspecialchars($rev['revision_file']) ?>" 
                       class="btn-primary" 
                       style="padding:6px 14px; font-size:13px;"
                       download>
                        Download Attachment
                    </a>
                </p>
            <?php endif; ?>

            <p><strong>Status:</strong> <?= $rev['request_status'] ?></p>

            <?php if ($rev['request_status'] === 'Pending'): ?>

                <form method="POST" enctype="multipart/form-data" class="styled-form">

                    <input type="hidden" name="revision_id" value="<?= $rev['revision_id'] ?>">

                    <div class="form-group">
                        <label>Your Response</label>
                        <textarea 
                            name="freelancer_response" 
                            class="input-field"
                            rows="4"
                            required
                        ></textarea>
                    </div>

                    <div class="form-group">
                        <label>Upload Revised File</label>
                        <input type="file" name="deliverable">
                    </div>

                    <div class="form-row">
                        <button name="decision" value="Accepted" class="btn-primary">
                            Accept & Deliver
                        </button>

                        <button name="decision" value="Rejected" class="btn-primary" style="background:#777;">
                            Reject
                        </button>
                    </div>

                </form>

            <?php else: ?>

                <p><strong>Freelancer Response:</strong><br>
                    <?= nl2br(htmlspecialchars($rev['freelancer_response'])) ?>
                </p>

            <?php endif; ?>

        </div>

    <?php endforeach; ?>

<?php endif; ?>


<form method="POST" class="styled-form">
    <label>Update Status</label>
    <select name="status" class="input-field">
        <option value="In Progress" <?= $order['status']=='In Progress'?'selected':'' ?>>In Progress</option>
        <option value="Delivered" <?= $order['status']=='Delivered'?'selected':'' ?>>Delivered</option>
        <option value="Completed" <?= $order['status']=='Completed'?'selected':'' ?>>Completed</option>
    </select>
    <button class="btn-primary full-width">Update Status</button>
</form>

</main>
</div>
</body>
</html>
