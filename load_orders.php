<?php
require_once 'db.php';

echo '<div class="card mb-4 shadow">
        <div class="card-body">
          <h5 class="card-title text-primary">Recent Orders</h5>';

try {
    // Get current page and limit from GET (fallback to defaults)
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
   // $limit = isset($_GET['limit']) && $_GET['limit'] !== 'ALL' ? intval($_GET['limit']) : null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;

    // Total row count for pagination
    $countResult = $conn->query("SELECT COUNT(*) as total FROM orders");
    $totalRows = $countResult->fetch_assoc()['total'];
    $totalPages = $limit ? ceil($totalRows / $limit) : 1;

    // Calculate offset
    $offset = $limit ? ($page - 1) * $limit : 0;

    // Main query (with pagination if limit is set)
    $query = "SELECT order_id, product_id, quantity, price, order_date, supplier_id, warehouse_id, source_id, source_type, status 
              FROM orders 
              ORDER BY order_id DESC
              LIMIT $limit OFFSET $offset";
    if ($limit) {
      //  $query .= " LIMIT $limit OFFSET $offset";
    }

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo "
<div class='d-flex justify-content-between align-items-center mb-3 flex-wrap'>
  <h4 class='mb-0'>Contents of Orders</h4>
  <button class='btn btn-sm btn-outline-warning text-black' id='back-to-tables'>
    &larr; Back to Tables
  </button>
  <div class='d-flex align-items-center gap-2 flex-wrap'>

    <a class='btn btn-sm btn-outline-light' href='?page=1&limit=$limit'>&laquo; First</a>
    <a class='btn btn-sm btn-outline-light' href='?page=" . max(1, $page - 1) . "&limit=$limit'>&larr; Prev</a>

    <span class='text-black'>Page <strong>$page</strong> of <strong>$totalPages</strong></span>

    <a class='btn btn-sm btn-outline-light' href='?page=" . min($totalPages, $page + 1) . "&limit=$limit'>Next &rarr;</a>
    <a class='btn btn-sm btn-outline-light' href='?page=$totalPages&limit=$limit'>Last &raquo;</a>

    <span class='text-black ms-3'>Show:</span>
    <div class='btn-group btn-group-sm' role='group'>
      <a href='?page=1&limit=10' class='btn btn-outline-light'>10</a>
      <a href='?page=1&limit=20' class='btn btn-outline-light'>20</a>
      <a href='?page=1&limit=50' class='btn btn-outline-light'>50</a>
      <a href='?page=1&limit=ALL' class='btn btn-outline-light'>ALL</a>
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