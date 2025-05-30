<?php
// Database connection
include 'db.php'; 

if (isset($_GET['low_inv'])) {
   // file_put_contents('debug_postres.txt', print_r(  $result, true));
    fetchLowInventoryAlerts($conn);
   // exit; 
}else{
    renderDashboardFunctions();
}

function fetchLowInventoryAlerts($conn) {                 
    // Query to fetch low inventory alerts from the database
    $sql = "SELECT 
                lia.alert_id,
                pi.product_id,
                wi.warehouse_id,
                wi.minimum_quantity,
                wi.quantity AS current_quantity,
                lia.alert_date
            FROM 
                low_inventory_alerts lia
            JOIN 
                warehouse_inventory wi ON lia.product_id = wi.product_id
            JOIN 
                products pi ON pi.product_id = wi.product_id
            ORDER BY 
                lia.alert_date DESC";

    $result = $conn->query($sql);
   //file_put_contents('debug_postres.txt', print_r(  $result, true));
    if ($result->num_rows > 0) {
        // Display products that are below the minimum quantity
        while ($row = $result->fetch_assoc()) {
            $alert_id = $row['alert_id'];
            $product_id = $row['product_id'];
            $warehouse_id = $row['warehouse_id'];
            $current_quantity = $row['current_quantity'];
            $minimum_quantity = $row['minimum_quantity'];
            $alert_date = $row['alert_date'];
             
            echo "<div class='card mb-4 shadow'>
            <div class='card-body'>
                  <tr>
                    <td>$product_id</td>
                    <td>$warehouse_id</td>
                    <td>$current_quantity</td>
                    <td>$minimum_quantity</td>
                    <td>$alert_date</td>
                    <td>
                        <form method='POST' action='place_order.php'>
                            <input type='hidden' name='alert_id' value='$alert_id'>
                            <button type='submit' class='btn btn-success'>Place Order</button>
                        </form>
                    </td>
                  </tr>";

                  
            echo "</div></div>";         
           
        }
    } else {
        echo "<tr><td colspan='6'>No low inventory alerts found.</td></tr>";
    }
}

function renderDashboardFunctions() {
    try {
        echo '
        <div class="card mb-4 shadow">
            <div class="card-body">
                
                <h4 class="mb-4">Available Functions</h4>
                <div class="d-flex flex-column flex-sm-row gap-3 mb-3">
                    <button class="btn btn-primary" id="show-orders">Display Orders</button>
                    <button class="btn btn-primary" id="show-procedures">Display Stored Procedures</button>
                    <button class="btn btn-primary" id="show-triggers">Display Triggers</button>
                    <a href="#" class="btn btn-primary" id="show-low-inv-alerts">Low Inventory Alerts</a>
                </div>
                
                <div id="function-result" class="mt-3"></div>
            </div>
        </div>';
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Fatal error: " . $e->getMessage() . "</div>";
    }
}

$conn->close();
?>