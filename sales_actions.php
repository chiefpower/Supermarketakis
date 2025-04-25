<?php
// Database connection
include 'db.php'; 

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check connection and handle errors
try {
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to calculate total sales for all stores
function getTotalSales($conn) {
    try {
        $sql = "SELECT 
                    SUM(uoi.quantity * uoi.price) AS total_sales
                FROM 
                    user_order_items uoi
                JOIN 
                    user_orders uo ON uoi.order_id = uo.id
                WHERE 
                    uo.status IN ('confirmed', 'shipped', 'delivered')";
        
        $result = $conn->query($sql);
        return ($result->num_rows > 0) ? $result->fetch_assoc()['total_sales'] : 0;
    } catch (Exception $e) {
        die("Error executing query: " . $e->getMessage());
    }
}

// Function to calculate sales for each store
function getSalesPerStore($conn) {
    try {
        $sql = "
            SELECT 
                up.preferred_store_id AS store_id,
                s.name AS store_name,
                SUM(uo.total_price) AS store_sales
            FROM 
                user_orders uo
            JOIN 
                user_profiles up ON uo.user_id = up.user_id
            JOIN 
                stores s ON up.preferred_store_id = s.store_id
            WHERE 
                uo.status IN ('confirmed', 'shipped', 'delivered')
            GROUP BY 
                up.preferred_store_id, s.name
        ";
        
        $result = $conn->query($sql);
        $sales_per_store = [];

        while ($row = $result->fetch_assoc()) {
            $sales_per_store[] = [
                'store_id' => $row['store_id'],
                'store_name' => $row['store_name'],
                'store_sales' => $row['store_sales']
            ];
        }

        return $sales_per_store;
    } catch (Exception $e) {
        die("Error executing query: " . $e->getMessage());
    }
}

// Get product name
function getProductName($conn, $product_id) {
    try {
        $stmt = $conn->prepare("SELECT name FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($product_name);
        $stmt->fetch();
        $stmt->close();
        return $product_name;
    } catch (Exception $e) {
        return "Unknown Product";
    }
}

// Get store name
function getStoreName($conn, $store_id) {
    try {
        $stmt = $conn->prepare("SELECT name FROM stores WHERE store_id = ?");
        $stmt->bind_param("i", $store_id);
        $stmt->execute();
        $stmt->bind_result($store_name);
        $stmt->fetch();
        $stmt->close();
        return $store_name;
    } catch (Exception $e) {
        return "Unknown Store";
    }
}

// Reduce quantities based on order data
function processOrderReductions($conn) {
    try {
        $sql = "
            SELECT 
                uoi.product_id,
                uoi.quantity AS quantity_ordered,
                up.preferred_store_id AS store_id,
                si.quantity AS current_quantity
            FROM 
                user_order_items uoi
            JOIN 
                user_orders uo ON uoi.order_id = uo.id
            JOIN 
                user_profiles up ON uo.user_id = up.user_id
            JOIN 
                store_inventory si ON si.product_id = uoi.product_id AND si.store_id = up.preferred_store_id
            WHERE 
                uo.status IN ('confirmed', 'shipped', 'delivered')
        ";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $store_id = $row['store_id'];
                $product_id = $row['product_id'];
                $quantity_to_reduce = $row['quantity_ordered'];
                $old_quantity = $row['current_quantity'];

                // Get names
                $product_name = getProductName($conn, $product_id);
                $store_name = getStoreName($conn, $store_id);

                // Reduce the quantity
                $update = $conn->prepare("
                    UPDATE store_inventory
                    SET quantity = quantity - ?
                    WHERE store_id = ? AND product_id = ? AND quantity >= ?
                ");
                $update->bind_param("iiii", $quantity_to_reduce, $store_id, $product_id, $quantity_to_reduce);
                $update->execute();
                $update->close();

                // Echo result
                echo "<p class='text-black'>Reduced quantity (Old Quantity: $old_quantity) for Product '$product_name' in Store '$store_name' by $quantity_to_reduce.</p>";
            }
        } else {
            echo "<p class='text-black'>No order items to process.</p>";
        }
    } catch (Exception $e) {
        echo "<p class='text-black'>Error processing reductions: " . $e->getMessage() . "</p>";
    }
}

// Main logic
try {
    echo '<div class="container-fluid p-4" id="content-area" style="min-height: 20rem; max-height: 80vh;">';

    // Total Sales
    $total_sales = getTotalSales($conn);
    echo '
      <div class="card mb-4 shadow">
        <div class="card-body bg-light">
          <h5 class="card-title text-primary">Total Sales for All Stores</h5>
          <p class="card-text display-6 fw-bold text-dark">$' . number_format((float)$total_sales, 2) . '</p>
        </div>
      </div>
    ';

    // Sales Per Store
    $sales_per_store = getSalesPerStore($conn);
    echo '
      <div class="card mb-4 shadow">
        <div class="card-body">
          <h5 class="card-title text-primary">Sales per Store</h5>
    ';

    if (empty($sales_per_store)) {
        echo '<p class="text-muted">No sales data found per store.</p>';
    } else {
        echo '<ul class="list-group list-group-flush">';
        foreach ($sales_per_store as $store) {
            echo '
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <strong>' . htmlspecialchars($store['store_name']) . ' (ID: ' . $store['store_id'] . ')</strong>
                <span class="badge bg-success fs-6">$' . number_format((float)$store['store_sales'], 2) . '</span>
              </li>';
        }
        echo '</ul>';
    }

    echo '</div></div>';

    // Inventory Reductions
    echo '
      <div class="card shadow">
        <div class="card-body">
          <h5 class="card-title text-primary">Inventory Reductions</h5>
    ';
    processOrderReductions($conn);
    echo '
        </div>
      </div>
    ';

    echo '</div>';
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Fatal error: " . $e->getMessage() . "</div>";
}

$conn->close();
?>