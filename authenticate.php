<?php
session_start();
require 'db.php.inc';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

/* ===============================
   FETCH USER
================================ */
$stmt = $pdo->prepare("
    SELECT user_id, password, status, role,
           failed_attempts, last_failed_attempt, lock_until
    FROM users
    WHERE email = ?
");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* ===============================
   USER NOT FOUND
================================ */
if (!$user) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: login.php");
    exit;
}

/* ===============================
   PERMANENT ACCOUNT STATUS CHECK
================================ */
if ($user['status'] !== 'Active') {
    $_SESSION['error'] = "Account is not active.";
    header("Location: login.php");
    exit;
}

/* ===============================
   TEMPORARY LOCK CHECK
================================ */
if ($user['lock_until'] && strtotime($user['lock_until']) > time()) {
    $minutes = ceil((strtotime($user['lock_until']) - time()) / 60);
    $_SESSION['error'] = "Account locked. Try again in {$minutes} minutes.";
    header("Location: login.php");
    exit;
}

/* ===============================
   PASSWORD CHECK
================================ */
if (!password_verify($password, $user['password'])) {

    $now = date('Y-m-d H:i:s');
    $failed = $user['failed_attempts'] + 1;

    // Reset counter if last failure was more than 15 minutes ago
    if ($user['last_failed_attempt'] &&
        strtotime($user['last_failed_attempt']) < time() - (15 * 60)) {
        $failed = 1;
    }

    // Lock account after 5 failed attempts
    if ($failed >= 5) {
        $lockUntil = date('Y-m-d H:i:s', time() + (30 * 60));

        $stmt = $pdo->prepare("
            UPDATE users
            SET failed_attempts = 5,
                last_failed_attempt = ?,
                lock_until = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$now, $lockUntil, $user['user_id']]);

        $_SESSION['error'] = "Account locked for 30 minutes.";
        header("Location: login.php");
        exit;
    }

    // Update failed attempts
    $stmt = $pdo->prepare("
        UPDATE users
        SET failed_attempts = ?,
            last_failed_attempt = ?
        WHERE user_id = ?
    ");
    $stmt->execute([$failed, $now, $user['user_id']]);

    // User-friendly error messages
    if ($failed >= 3) {
        $remaining = 5 - $failed;
        $_SESSION['error'] = "Invalid password. {$remaining} attempts remaining.";
    } else {
        $_SESSION['error'] = "Invalid email or password.";
    }

    header("Location: login.php");
    exit;
}

/* ===============================
   SUCCESSFUL LOGIN
   → RESET SECURITY COUNTERS
================================ */
$stmt = $pdo->prepare("
    UPDATE users
    SET failed_attempts = 0,
        last_failed_attempt = NULL,
        lock_until = NULL
    WHERE user_id = ?
");
$stmt->execute([$user['user_id']]);

// Set session variables
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role'] = $user['role'];       // <-- Fixed: store role
$_SESSION['last_activity'] = time();


// Redirect based on role
if ($_SESSION['role'] === 'Client') {
    header("Location: client-home.php");
} else {
    header("Location: freelancer-home.php");
}
exit;
?>
