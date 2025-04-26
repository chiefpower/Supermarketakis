<?php
require_once 'db.php';

echo '<div class="card mb-4 shadow">
        <div class="card-body">
          <h5 class="card-title text-primary">Recent Orders</h5>';

try {
    $query = "SELECT order_id, product_id, quantity, price, order_date, supplier_id, warehouse_id, source_id, source_type 
              FROM orders 
              ORDER BY order_date DESC 
              LIMIT 100";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo "<div class='table-responsive'><table class='table table-bordered table-striped table-sm'>";
        echo "<thead><tr>
                <th>Order ID</th><th>Product ID</th><th>Qty</th><th>Price</th><th>Date</th>
                <th>Supplier</th><th>Warehouse</th><th>Source ID</th><th>Source Type</th>
              </tr></thead><tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['order_id']}</td>
                    <td>{$row['product_id']}</td>
                    <td>{$row['quantity']}</td>
                    <td>\${$row['price']}</td>
                    <td>{$row['order_date']}</td>
                    <td>{$row['supplier_id']}</td>
                    <td>{$row['warehouse_id']}</td>
                    <td>{$row['source_id']}</td>
                    <td>{$row['source_type']}</td>
                  </tr>";
        }
        echo "</tbody></table></div>";
    } else {
        echo "<p class='text-muted'>No orders found.</p>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error fetching orders: " . $e->getMessage() . "</div>";
}

echo '</div></div>';
?>