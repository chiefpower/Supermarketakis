<?php
// Connect to DB
require 'db.php'; // include your DB connection script

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo "<div class='alert alert-danger'>No order ID provided.</div>";
    exit;
}

// Step 1: Get user_id from user_orders
$stmt = $conn->prepare("SELECT user_id, created_at FROM user_orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "<div class='alert alert-danger'>Order not found.</div>";
    exit;
}

$user_id = $order['user_id'];
$order_date = $order['created_at'];

// Step 2: Get order items
$stmt = $conn->prepare("
    SELECT product_id, quantity, price 
    FROM user_order_items 
    WHERE order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Step 3: Get user profile
$stmt = $conn->prepare("SELECT first_name, last_name, phone, address_line_1, 
                            address_line_2, country, zip_code, notes, preferred_store_id 
                            FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile_result = $stmt->get_result();
$user_profile = $profile_result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Confirmation</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
  <h2 class="mb-4">Order Confirmation</h2>

  <!-- Order Info -->
  <div class="card mb-4">
    <div class="card-header"><strong>Order Summary</strong></div>
    <div class="card-body">
      <p><strong>Order ID:</strong> <?= htmlspecialchars($order_id) ?></p>
      <p><strong>Order Date:</strong> <?= htmlspecialchars($order_date) ?></p>
    </div>
  </div>

  <!-- User Info -->
  <div class="card mb-4">
    <div class="card-header"><strong>Customer Information</strong></div>
    <div class="card-body">
      <p><strong>Name:</strong> <?= htmlspecialchars($user_profile['name']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($user_profile['email']) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($user_profile['phone']) ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($user_profile['address']) ?></p>
    </div>
  </div>

  <!-- Order Items -->
  <div class="card mb-4">
    <div class="card-header"><strong>Items in Your Order</strong></div>
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>Product ID</th>
            <th>Quantity</th>
            <th>Price (€)</th>
            <th>Total (€)</th>
          </tr>
        </thead>
        <tbody>
        <?php $grand_total = 0; ?>
        <?php foreach ($order_items as $item): ?>
          <?php
            $total = $item['quantity'] * $item['price'];
            $grand_total += $total;
          ?>
          <tr>
            <td><?= htmlspecialchars($item['product_id']) ?></td>
            <td><?= htmlspecialchars($item['quantity']) ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td><?= number_format($total, 2) ?></td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <th colspan="3" class="text-end">Grand Total</th>
          <th>€<?= number_format($grand_total, 2) ?></th>
        </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="alert alert-success text-center mt-4">
    Thank you for your order! A confirmation email will be sent shortly.
  </div>
</div>
</body>
</html>
