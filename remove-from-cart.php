<?php
session_start();

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Client') {
    header("Location: login.php");
    exit;
}

/* ===== VALIDATE INPUT ===== */
if (!isset($_GET['id'])) {
    header("Location: cart.php");
    exit;
}

$serviceId = $_GET['id'];

/* ===== REMOVE FROM CART ===== */
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {

    $_SESSION['cart'] = array_values(
        array_filter($_SESSION['cart'], function ($id) use ($serviceId) {
            return $id !== $serviceId;
        })
    );
}

/* ===== REDIRECT BACK ===== */
header("Location: cart.php");
exit;
