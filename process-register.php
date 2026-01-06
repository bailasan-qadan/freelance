<?php
session_start();
require 'db.php.inc';

$errors = [];

/* ===============================
   GET INPUT
================================ */
$first   = trim($_POST['first_name'] ?? '');
$last    = trim($_POST['last_name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$pass    = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
$phone   = trim($_POST['phone'] ?? '');
$age     = $_POST['age'] ?? '';
$city    = $_POST['city'] ?? '';
$bio     = trim($_POST['bio'] ?? '');
$role    = $_POST['role'] ?? '';

/* ===============================
   VALIDATION
================================ */
if (
    !$first || !$last || !$email || !$pass || !$confirm ||
    !$phone || !$age || !$city || !$bio || !$role
) {
    $errors[] = "All fields are required.";
}

if ($pass !== $confirm) {
    $errors[] = "Passwords do not match.";
}

if (strlen($pass) < 8) {
    $errors[] = "Password must be at least 8 characters.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}

/* ===============================
   CHECK EMAIL EXISTS
================================ */
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);

if ($stmt->fetch()) {
    $errors[] = "Email already exists.";
}

/* ===============================
   HANDLE ERRORS
================================ */
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: register.php");
    exit;
}

/* ===============================
   INSERT USER
================================ */
$user_id = str_pad(rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
$hashed  = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO users
    (user_id, first_name, last_name, email, password, phone, age, city, bio, country, role)
    VALUES
    (:id, :fn, :ln, :email, :pass, :phone, :age, :city, :bio, 'Palestine', :role)
");

$stmt->execute([
    'id'    => $user_id,
    'fn'    => $first,
    'ln'    => $last,
    'email' => $email,
    'pass'  => $hashed,
    'phone' => $phone,
    'age'   => $age,
    'city'  => $city,
    'bio'   => $bio,
    'role'  => $role
]);

$_SESSION['success'] = "Account created successfully! Please login.";

header("Location: register.php");
exit;
