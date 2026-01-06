<?php
require 'db.php.inc';

$stmt = $pdo->query("
    SELECT service_id, title, category, price, status, created_date
    FROM services
");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Services</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <?php include 'nav.php'; ?>

    <main class="main-content">
        <h2>My Services</h2>

        <table class="services-table">
            <thead>
                <tr>
                    <th>Service ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                <tr>
                    <td><?= htmlspecialchars($service['service_id']) ?></td>
                    <td><?= htmlspecialchars($service['title']) ?></td>
                    <td><?= htmlspecialchars($service['category']) ?></td>
                    <td>$<?= number_format($service['price'], 2) ?></td>
                    <td><?= htmlspecialchars($service['status']) ?></td>
                    <td><?= htmlspecialchars($service['created_date']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
