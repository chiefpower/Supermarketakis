<?php
// Database connection
include 'db.php'; 

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

//$selectedDate = isset($_POST['selected_date']) ? $_POST['selected_date'] : date('Y-m-d'); // fallback for safety
// Handle selected date
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_date'])) {
    $inputDate = $_POST['selected_date'];

    $dateObj = DateTime::createFromFormat('d/m/Y', $inputDate);
    if (!$dateObj) {
        die("<div class='alert alert-danger'>Invalid date format.</div>");
    }

    $selectedDate = $dateObj->format('Y-m-d');
} else {
    $selectedDate = null;
}
$today = date('Y-m-d');

// Helper: Check if reduction already run
function reductionAlreadyRun($conn, $date) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM inventory_reductions WHERE reduction_date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

// Check connection and handle errors
try {
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to calculate total sales for all stores
function getTotalSales($conn, $selectedDate) {
    try {
        $sql = "
            SELECT 
                SUM(uoi.quantity * uoi.price) AS total_sales
            FROM 
                user_order_items uoi
            JOIN 
                user_orders uo ON uoi.order_id = uo.id
            WHERE 
                uo.status IN ('confirmed', 'shipped', 'delivered') AND
                DATE(uo.created_at) = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $selectedDate); // Format: 'Y-m-d'
        $stmt->execute();
        $stmt->bind_result($total_sales);
        $stmt->fetch();
        $stmt->close();

        return $total_sales ?? 0;
    } catch (Exception $e) {
        die("Error executing query: " . $e->getMessage());
    }
}

// Function to calculate sales for each store
function getSalesPerStore($conn, $selectedDate) {
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
                uo.status IN ('confirmed', 'shipped', 'delivered') AND
                DATE(uo.created_at) = ?
            GROUP BY 
                up.preferred_store_id, s.name
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $selectedDate); // date should be in 'Y-m-d' format
        $stmt->execute();
        $result = $stmt->get_result();

        $sales_per_store = [];
        while ($row = $result->fetch_assoc()) {
            $sales_per_store[] = [
                'store_id' => $row['store_id'],
                'store_name' => $row['store_name'],
                'store_sales' => $row['store_sales']
            ];
        }

        $stmt->close();
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
function processOrderReductions($conn, $selectedDate) {
    try {
        // Check if the reduction has already been processed for this date
    //    if (reductionAlreadyRun($conn, $selectedDate)) {
    //        echo "<p class='text-black'>Inventory reduction already processed for " . htmlspecialchars($selectedDate) . "</p>";
     //       return;
   //     }

        // Start a transaction to ensure that either all reductions are processed or none
        $conn->begin_transaction();

        // Query to get the order items to be processed for the specific selectedDate
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
                AND DATE(uo.created_at) = ?  -- Filtering by the selected date
        ";

        // Prepare the statement and bind the selectedDate
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $selectedDate);
        $stmt->execute();
        $result = $stmt->get_result();

        // Flag to track if any reductions were performed
        $reductionsPerformed = false;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $store_id = $row['store_id'];
                $product_id = $row['product_id'];
                $quantity_to_reduce = $row['quantity_ordered'];
                $old_quantity = $row['current_quantity'];

                // Get names
                $product_name = getProductName($conn, $product_id);
                $store_name = getStoreName($conn, $store_id);

                // Reduce the quantity in the inventory
                $update = $conn->prepare("
                    UPDATE store_inventory
                    SET quantity = quantity - ?
                    WHERE store_id = ? AND product_id = ? AND quantity >= ?
                ");
                $update->bind_param("iiii", $quantity_to_reduce, $store_id, $product_id, $quantity_to_reduce);
            
                if ($update->execute()) {
                    $update->close();
            
                    // Get the warehouse ID for the store
                    $warehouse_id = null;
                    $stmt = $conn->prepare("SELECT warehouse_id FROM stores WHERE store_id = ?");
                    $stmt->bind_param("i", $store_id);
                    $stmt->execute();
                    $stmt->bind_result($warehouse_id);
                    $stmt->fetch();
                    $stmt->close();
            
                    if ($warehouse_id !== null) {
                        // Call for store
                        $sourceType = 'store';
                        $stmt = mysqli_prepare($conn, "CALL PlaceOrderForLowInventory(?, ?, ?)");
                        mysqli_stmt_bind_param($stmt, "iis", $product_id, $store_id, $sourceType);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
            
                        // Call for warehouse
                        $sourceType = 'warehouse';
                        $stmt = mysqli_prepare($conn, "CALL PlaceOrderForLowInventory(?, ?, ?)");
                        mysqli_stmt_bind_param($stmt, "iis", $product_id, $warehouse_id, $sourceType);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    } else {
                        echo "<p class='text-danger'>Warehouse ID not found for Store ID $store_id.</p>";
                    }
                } else {
                    echo "<p class='text-danger'>Inventory update failed: " . $update->error . "</p>";
                    $update->close();
                }
            
                // Echo result
                echo "<p class='text-black'>Reduced quantity (Old Quantity: $old_quantity) for Product '$product_name' in Store '$store_name' by $quantity_to_reduce.</p>";

                // Set the flag to indicate that reductions were performed
                $reductionsPerformed = true;
            }
        } else {
            echo "<p class='text-black'>No orders found for the selected date: " . htmlspecialchars($selectedDate) . "</p>";
        }

        // If reductions were performed, insert the reduction entry
        if (!$reductionsPerformed) {
            $stmt = $conn->prepare("INSERT INTO inventory_reductions (reduction_date) VALUES (?)");
            $stmt->bind_param("s", $selectedDate);
            $stmt->execute();
            $stmt->close();
        }

        // Commit the transaction if everything was successful
        $conn->commit();

    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        echo "<p class='text-black'>Error processing reductions: " . $e->getMessage() . "</p>";
    }
}

// Main logic
try {
    $todayFormatted = date('d/m/Y');
    echo '<div class="container-fluid p-4" id="content-area" style="min-height: 20rem; max-height: 80vh;">';
    echo "
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css'>
        <script src='https://cdn.jsdelivr.net/npm/flatpickr'></script>

        <div class='container-fluid p-4'>
          <div class='card mb-4'>
            <div class='card-body'>
              <strong>Select a date to view Sales</strong>
              <input type='text' id='sales-date' class='form-control w-50 mb-3' placeholder='Select a date to view sales' value='$todayFormatted' />
              <button id='load-sales' class='btn btn-primary'>Load Sales</button>
              
              <button id='graph-actions' class='btn btn-primary'>Load Sales Graph</button>
            </div>
          </div>

          <div id='message'></div>
          <div id='content-area'></div>
        </div>

        <script>
          flatpickr('#sales-date', {
            dateFormat: 'd/m/Y',
            maxDate: 'today'
          });
        </script>
    ";

    // Total Sales
    if ($selectedDate) {
        try {
            // Total Sales
            $total_sales = getTotalSales($conn, $selectedDate);
            echo '
              <div class="card mb-4 shadow">
                <div class="card-body bg-light">
                  <h5 class="card-title text-primary">Total Sales for All Stores on ' . htmlspecialchars($selectedDate) . '</h5>
                  <p class="card-text display-6 fw-bold text-dark">€' . number_format((float)$total_sales, 2) . '</p>
                </div>
              </div>
            ';
    
            // Sales Per Store
            $sales_per_store = getSalesPerStore($conn, $selectedDate);
            echo '
              <div class="card mb-4 shadow">
                <div class="card-body">
                  <h5 class="card-title text-primary">Sales per Store</h5>';
    
            if (empty($sales_per_store)) {
                echo '<p class="text-muted">No sales data found per store.</p>';
            } else {
                echo '<ul class="list-group list-group-flush">';
                foreach ($sales_per_store as $store) {
                    echo '
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong>' . htmlspecialchars($store['store_name']) . ' (ID: ' . $store['store_id'] . ')</strong>
                        <span class="badge bg-success fs-6">€' . number_format((float)$store['store_sales'], 2) . '</span>
                      </li>';
                }
                echo '</ul>';
            }
    
            echo '</div></div>';
    
            // Inventory Reductions
            echo '
              <div class="card shadow">
                <div class="card-body">
                  <h5 class="card-title text-primary">Inventory Reductions</h5>';
            processOrderReductions($conn, $selectedDate);
            echo '</div></div>';
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Fatal error: " . $e->getMessage() . "</div>";
        }
    }
    

    echo '</div>';
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Fatal error: " . $e->getMessage() . "</div>";
}

$conn->close();
?>