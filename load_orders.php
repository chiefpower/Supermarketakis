<?php
require_once 'db.php';

echo '<div class="card mb-4 shadow">
        <div class="card-body">
          <h5 class="card-title text-primary">Recent Orders</h5>';

try {
    $query = "SELECT order_id, product_id, quantity, price, order_date, supplier_id, warehouse_id, source_id, source_type, status 
              FROM orders 
              ORDER BY order_id DESC 
              LIMIT 100";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo "
<div class='d-flex justify-content-between align-items-center mb-3 flex-wrap'>
  <h4 class='mb-0'>Contents of $table</h4>
    <button class='btn btn-sm btn-outline-warning text-black' id='back-to-tables'>
      &larr; Back to Tables
    </button>
  <div class='d-flex align-items-center gap-2 flex-wrap'>

    <button class='btn btn-sm btn-outline-light' id='first-page'>&laquo; First</button>
    <button class='btn btn-sm btn-outline-light' id='prev-page'>&larr; Prev</button>

    <span class='text-black'>Page <strong>$page</strong> of <strong>$totalPages</strong></span>

    <button class='btn btn-sm btn-outline-light' id='next-page'>Next &rarr;</button>
    <button class='btn btn-sm btn-outline-light' id='last-page'>Last &raquo;</button>

    <span class='text-black ms-3'>Show:</span>
    <div class='btn-group btn-group-sm' role='group' aria-label='Row count selector'>
      <button type='button' class='btn btn-outline-light row-limit' data-limit='10'>10</button>
      <button type='button' class='btn btn-outline-light row-limit' data-limit='20'>20</button>
      <button type='button' class='btn btn-outline-light row-limit' data-limit='50'>50</button>
      <button type='button' class='btn btn-outline-light row-limit' data-limit='ALL'>ALL</button>
    </div>
  </div>
</div>";
        echo "<div class='table-responsive'><table class='table table-bordered table-striped table-sm'>";
        echo "<thead><tr>
                <th>Order ID</th><th>Product ID</th><th>Qty</th><th>Price</th><th>Date</th>
                <th>Supplier</th><th>Warehouse</th><th>Source ID</th><th>Source Type</th>
                <th>Status</th>
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
                    <td>{$row['status']}</td>
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