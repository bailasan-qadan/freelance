<?php
session_start();
require 'db.php.inc';

/* AUTH */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Client') {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$clientId = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    foreach ($_SESSION['cart'] as $serviceId) {

        $stmt = $pdo->prepare("
            SELECT s.*, u.user_id AS freelancer_id
            FROM services s
            JOIN users u ON s.freelancer_id = u.user_id
            WHERE s.service_id = ?
        ");
        $stmt->execute([$serviceId]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$service) {
            throw new Exception("Service not found");
        }

        $expectedDelivery = date('Y-m-d', strtotime("+{$service['delivery_time']} days"));

        $stmt = $pdo->prepare("
            INSERT INTO orders (
                order_id, client_id, freelancer_id, service_id,
                service_title, price, delivery_time,
                revisions_included, requirements,
                status, payment_method, expected_delivery
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Credit Card', ?
            )
        ");

        $orderId = 'O' . str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);

        $stmt->execute([
            $orderId,
            $clientId,
            $service['freelancer_id'],
            $service['service_id'],
            $service['title'],
            $service['price'],
            $service['delivery_time'],
            $service['revisions_included'],
            'Client requirements will be added later',
            $expectedDelivery
        ]);
    }

    $pdo->commit();
    unset($_SESSION['cart']);

    header("Location: my-orders.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Checkout failed: " . $e->getMessage());
}
