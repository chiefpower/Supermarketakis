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
function processUserOrderReductions($conn, $selectedDate) {
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
                        //mysqli_query($conn, "SET max_sp_recursion_depth = 10");
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

function backOrderUpdate(mysqli $conn, int $product_id, int $warehouse_id, string $selectedDate) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $formatted_date = date('Y-m-d', strtotime($selectedDate)); // ensure format is correct

        // 1. Check for existing confirmed order on selected date
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM orders 
            WHERE product_id = ? 
            AND warehouse_id = ? 
            AND status = 'confirmed' 
            AND order_date = ?
        ");
        $stmt->bind_param("iis", $product_id, $warehouse_id, $formatted_date);
        $stmt->execute();
        $stmt->bind_result($order_count);
        $stmt->fetch();
        $stmt->close();

        // 2. Check for existing pending backorder request
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM backorder_requests 
            WHERE product_id = ? AND warehouse_id = ? AND status = 'pending'
        ");
        $stmt->bind_param("ii", $product_id, $warehouse_id);
        $stmt->execute();
        $stmt->bind_result($backorder_exists);
        $stmt->fetch();
        $stmt->close();

        if ($backorder_exists > 0) {
            // 3. Update existing backorder status to 'ordered'
            $stmt = $conn->prepare("
                UPDATE backorder_requests 
                SET status = 'ordered', request_date = NOW() 
                WHERE product_id = ? AND warehouse_id = ? AND status = 'pending'
            ");
            $stmt->bind_param("ii", $product_id, $warehouse_id);
            $stmt->execute();
            $stmt->close();
        }

        echo "Backorder request update for product $product_id and warehouse $warehouse_id on $selectedDate. $backorder_exists rows affected.<br>";

    } catch (Exception $e) {
        echo "Error in backOrderUpdate: " . $e->getMessage();
    }
}

function markOrdersAsDelivered(mysqli $conn, int $product_id, int $warehouse_id, string $selectedDate, string $source_type): bool {
    try {
        $formatted_date = date('Y-m-d', strtotime($selectedDate));
        if ($source_type === 'store'){
            $stmt = $conn->prepare("
                UPDATE orders
                SET status = 'delivered'
                WHERE product_id = ? AND warehouse_id = ? AND order_date = ? AND status = 'confirmed'
            ");
        }else{
            $stmt = $conn->prepare("
                UPDATE orders
                SET status = 'delivered'
                WHERE product_id = ? AND source_id = ? AND order_date = ? AND status = 'confirmed'
            ");
        }
        
        $stmt->bind_param("iis", $product_id, $warehouse_id, $formatted_date);

        if ($stmt->execute()) {
            $affected = $stmt->affected_rows;
            $stmt->close();
            if ($affected > 0){
                echo "$affected order(s) marked as delivered.<br>";
            }      
            return true;
        } else {
            throw new Exception("Failed to execute update.");
        }
    } catch (Exception $e) {
        echo "Error updating order status: " . $e->getMessage();
        return false;
    }
}

function handleOrderPHP(mysqli $conn, int $product_id, int $quantity, int $warehouse_id, int $source_id, string $source_type, bool $is_auto_triggered = false, $selectedDate) {
    $source_type = strtolower($source_type);
    try {
        

        if ($source_type === 'store') {
            $stmt = $conn->prepare("SELECT quantity FROM warehouse_inventory WHERE product_id = ? AND warehouse_id = ?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("ii", $product_id, $warehouse_id);
            if (!$stmt->execute()) throw new Exception("Execution failed: " . $stmt->error);
            $stmt->bind_result($current_qty);
            $stmt->fetch();
            $stmt->close();

            if (is_null($current_qty)) {
                throw new Exception("Product '$product_id' not found in warehouse '$warehouse_id'.");
            } elseif ($current_qty < $quantity) {
                $shortfall = $quantity - $current_qty;
                //file_put_contents('debug3_postres.txt', print_r(  $source_id, true));
                // Update warehouse
                $stmt = $conn->prepare("UPDATE warehouse_inventory SET quantity = quantity - ? WHERE product_id = ? AND warehouse_id = ?");
                if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
                $stmt->bind_param("iii", $current_qty, $product_id, $warehouse_id);
                if (!$stmt->execute()) throw new Exception("Execution failed: " . $stmt->error);
                $stmt->close();
                
                // Log OUT
                $query = "INSERT INTO inventory_transactions (product_id, quantity, warehouse_id, store_id, source_type, transaction_type, notes)
                          VALUES ($product_id, $current_qty, $warehouse_id, $source_id, 'store', 'OUT', 'Partial transfer to store $source_id')";
                if (!$conn->query($query)) throw new Exception($conn->error);
                
                // Store inventory update/insert
                $exists = $conn->query("SELECT 1 FROM store_inventory WHERE product_id = $product_id AND store_id = $source_id")->num_rows > 0;
                if ($exists) {
                    $query = "UPDATE store_inventory SET quantity = quantity + $current_qty WHERE product_id = $product_id AND store_id = $source_id";
                } else {
                    $query = "INSERT INTO store_inventory (product_id, store_id, quantity) VALUES ($product_id, $source_id, $current_qty)";
                }
                if (!$conn->query($query)) throw new Exception($conn->error);
                
                // Log IN
                $query = "INSERT INTO inventory_transactions (product_id, quantity, warehouse_id, store_id, source_type, transaction_type, notes)
                          VALUES ($product_id, $current_qty, $warehouse_id, $source_id, 'store', 'IN', 'Partially received from warehouse $warehouse_id')";
                if (!$conn->query($query)) throw new Exception($conn->error);
                
                // Backorder
                $query = "INSERT INTO backorder_requests (product_id, warehouse_id, store_id, requested_qty, fulfilled_qty, shortfall_qty, request_type, notes)
                          VALUES ($product_id, $warehouse_id, $source_id, $quantity, $current_qty, $shortfall, 'store', 'Auto-created from store order shortfall')";
                if (!$conn->query($query)) throw new Exception($conn->error);
                
                if (!$is_auto_triggered) {
                    $stmt = $conn->prepare("CALL PlaceOrderForLowInventory(?, ?, 'warehouse')");
                    if (!$stmt) throw new Exception($conn->error);
                    $stmt->bind_param("ii", $product_id, $warehouse_id);
                    if (!$stmt->execute()) throw new Exception($stmt->error);
                    $stmt->close();
                }

                echo " Shortfall: only $current_qty moved from '$warehouse_id' to '$source_id'. Reorder placed.<br>";
                backOrderUpdate($conn, $product_id, $warehouse_id, $selectedDate);
            } else {
                //file_put_contents('debug5_postres.txt', print_r(  $warehouse_id , true));
                // Full transfer
                $stmt = $conn->prepare("UPDATE warehouse_inventory SET quantity = quantity - ? WHERE product_id = ? AND warehouse_id = ?");
                if (!$stmt) throw new Exception($conn->error);
                $stmt->bind_param("iii", $quantity, $product_id, $warehouse_id);
                if (!$stmt->execute()) throw new Exception($stmt->error);
                $stmt->close();
                
                $query = "INSERT INTO inventory_transactions (product_id, quantity, warehouse_id, store_id, source_type, transaction_type, notes)
                          VALUES ($product_id, $quantity, $warehouse_id, $source_id, 'store', 'OUT', 'Transfer to store $source_id')";
                if (!$conn->query($query)) throw new Exception($conn->error);

                $exists = $conn->query("SELECT 1 FROM store_inventory WHERE product_id = $product_id AND store_id = $source_id")->num_rows > 0;
                $query = $exists
                    ? "UPDATE store_inventory SET quantity = quantity + $quantity WHERE product_id = $product_id AND store_id = $source_id"
                    : "INSERT INTO store_inventory (product_id, store_id, quantity) VALUES ($product_id, $source_id, $quantity)";
                if (!$conn->query($query)) throw new Exception($conn->error);
               // file_put_contents('debug7_postres.txt', print_r(  $source_id, true));
                $query = "INSERT INTO inventory_transactions (product_id, quantity, warehouse_id, store_id, source_type, transaction_type, notes)
                          VALUES ($product_id, $quantity, $warehouse_id, $source_id, 'store', 'IN', 'Received from warehouse $warehouse_id')";
                if (!$conn->query($query)) throw new Exception($conn->error);

                echo "Full transfer completed for Store '$source_id' and Product '$product_id' .<br>";
            }
            backOrderUpdate($conn, $product_id, $warehouse_id, $selectedDate);
            markOrdersAsDelivered($conn, $product_id, $warehouse_id, $selectedDate, $source_type = 'store');
        } elseif ($source_type === 'warehouse') {
            // Warehouse receiving from supplier
            
            // Get order price
            //$stmt = $conn->prepare("SELECT price FROM orders WHERE product_id = ? AND warehouse_id = ? AND status = 'confirmed' ORDER BY order_date DESC LIMIT 1");
            $stmt = $conn->prepare("SELECT price FROM orders WHERE product_id = ? AND source_id = ? 
                                    AND source_type = 'warehouse' AND status = 'confirmed' 
                                    ORDER BY order_date DESC LIMIT 1");
            if (!$stmt) throw new Exception($conn->error);
            $stmt->bind_param("ii", $product_id, $warehouse_id);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->bind_result($current_price);
            $stmt->fetch();
            $stmt->close();

            // Get product price
            $stmt = $conn->prepare("SELECT price FROM products WHERE product_id = ?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("i", $product_id);
            if (!$stmt->execute()) throw new Exception("Execution failed: " . $stmt->error);
            $stmt->bind_result($product_price);
            if (!$stmt->fetch()) throw new Exception("Product not found.");
            $stmt->close();
    
            if (isset($current_price) && $current_price != $product_price) {
                $stmt = $conn->prepare("UPDATE products SET price = ? WHERE product_id = ?");
                $stmt->bind_param("di", $current_price, $product_id);
                if (!$stmt->execute()) throw new Exception($stmt->error);
                $stmt->close();
            }
            
            // Inventory update or insert
            $exists = $conn->query("SELECT 1 FROM warehouse_inventory WHERE product_id = $product_id AND warehouse_id = $warehouse_id")->num_rows > 0;
            if ($exists) {
                
                $stmt = $conn->prepare("CALL UpdateWarehouseInventoryAndHandleBackorders(?, ?, ?, ?)");
                $stmt->bind_param("iiii", $product_id, $quantity, $warehouse_id, $source_id);
                if (!$stmt->execute()) throw new Exception($stmt->error);
                
                $stmt->close();
            } else {
             //   $combined = "$warehouse_id $product_id $quantity";
                $combined = "$product_id $quantity $warehouse_id $source_id $source_type " . ($is_auto_triggered ? '1' : '0') . " $selectedDate";
                //file_put_contents('debug7_postres.txt', print_r(  $combined, true));
                $query = "INSERT INTO warehouse_inventory (product_id, warehouse_id, quantity) VALUES ($product_id, $warehouse_id, $quantity)";
                if (!$conn->query($query)) throw new Exception($conn->error);
            }
           
            // Log IN
            $query = "INSERT INTO inventory_transactions (product_id, quantity, warehouse_id, source_type, transaction_type, notes)
                      VALUES ($product_id, $quantity, $warehouse_id, 'warehouse', 'IN', 'Received from supplier (source ID $source_id)')";
            if (!$conn->query($query)) throw new Exception($conn->error);

            echo " Warehouse '$warehouse_id' restocked from supplier for Product '$product_id'.<br>";
            backOrderUpdate($conn, $product_id, $warehouse_id, $selectedDate);
            markOrdersAsDelivered($conn, $product_id, $warehouse_id, $selectedDate, $source_type = 'warehouse');
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error in handleOrderPHP: " . $e->getMessage() . "</div>";
    }
}

function processConfirmedOrders(mysqli $conn, $selectedDate) {
    $selectedDate = date('Y-m-d', strtotime($selectedDate));
    $query = "SELECT order_id, product_id, quantity, warehouse_id, source_id, source_type
              FROM orders
              WHERE status = 'confirmed' AND order_date = ?"; // Filter by date
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $selectedDate); // Bind the date parameter
    $stmt->execute();
    
    $result = $stmt->get_result(); // Use get_result to fetch the result

    if ($result && $result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
            $product_id = (int)$row['product_id'];
            $quantity = (int)$row['quantity'];
            $source_id = (int)$row['source_id'];
            $source_type = $row['source_type'];
            $is_auto_triggered = true;

            // If warehouse_id is NULL, build it from source_type + source_id
            if (is_null($row['warehouse_id'])) {
                if ($source_type === 'warehouse') {
                    $warehouse_id = $source_id; // Set warehouse_id to source_id when source_type is 'warehouse'
                }
            } else {
                $warehouse_id = (int)$row['warehouse_id'];
            }
            handleOrderPHP($conn, $product_id, $quantity, $warehouse_id, $source_id, $source_type, $is_auto_triggered, $selectedDate);
        }
        
        // Echo result
        echo "All confirmed orders processed.<br>";

    } else {
        echo " No confirmed orders found.<br>";
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
              <div class="card mb-4 shadow">
                <div class="card-body">
                  <h5 class="card-title text-primary">Inventory Reductions</h5>';
            processUserOrderReductions($conn, $selectedDate);
            echo '</div></div>'; 
            try{

                // Process Order Updates Addition/Reduction
                echo '
                <div class="card mb-4 shadow">
                  <div class="card-body">
                    <h5 class="card-title text-primary">Processed Orders</h5>';
                    processConfirmedOrders($conn, $selectedDate);
              echo '</div></div>';

            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>Fatal error3: " . $e->getMessage() . "</div>";
            }         
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Fatal error1: " . $e->getMessage() . "</div>";
        }
    }
    

    echo '</div>';
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Fatal error2: " . $e->getMessage() . "</div>";
}

$conn->close();
?>