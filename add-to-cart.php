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

/* ===== INIT CART ===== */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* ===== PREVENT DUPLICATES ===== */
if (!in_array($serviceId, $_SESSION['cart'])) {
    $_SESSION['cart'][] = $serviceId;
}

/* ===== REDIRECT ===== */
header("Location: cart.php");
exit;
