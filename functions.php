<?php
// Database connection
include 'db.php'; 

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
    file_put_contents('debug_postres.txt', print_r(  $result, true));
    if ($result->num_rows > 0) {
        // Display products that are below the minimum quantity
        while ($row = $result->fetch_assoc()) {
            $alert_id = $row['alert_id'];
            $product_id = $row['product_id'];
            $warehouse_id = $row['warehouse_id'];
            $current_quantity = $row['current_quantity'];
            $minimum_quantity = $row['minimum_quantity'];
            $alert_date = $row['alert_date'];

            echo "<tr>
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
            
        }
    } else {
        echo "<tr><td colspan='6'>No low inventory alerts found.</td></tr>";
    }
}
fetchLowInventoryAlerts($conn);
?>