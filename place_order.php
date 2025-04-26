<?php
// Database connection
include 'db.php'; 

if (isset($_POST['alert_id'])) {
    $alert_id = $_POST['alert_id'];

    // Fetch the details for the product with the low inventory
    $sql = "
        SELECT 
            pi.product_id, 
            wi.warehouse_id, 
            wi.quantity AS current_quantity, 
            wi.minimum_quantity, 
            sp.supplier_id,
            sp.sale_price
        FROM 
            low_inventory_alerts lia
        JOIN 
            warehouse_inventory wi ON lia.product_id = wi.product_id
        JOIN 
            supplier_product sp ON wi.product_id = sp.product_id
        JOIN 
            products pi ON pi.product_id = wi.product_id
        WHERE 
            lia.alert_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $alert_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $product_id = $row['product_id'];
        $warehouse_id = $row['warehouse_id'];
        $current_quantity = $row['current_quantity'];
        $minimum_quantity = $row['minimum_quantity'];
        $supplier_id = $row['supplier_id'];
        $quantity_to_order = $minimum_quantity - $current_quantity; // The amount to order
        $order_date = date('Y-m-d'); // Today's date

        // Place the order by inserting into the orders table
        $order_sql = "INSERT INTO orders (product_id, quantity, order_date, supplier_id) VALUES (?, ?, ?, ?)";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("iiii", $product_id, $quantity_to_order, $order_date, $supplier_id);
        $order_stmt->execute();
        $order_stmt->close();

        // Delete the alert after placing the order
        $delete_sql = "DELETE FROM low_inventory_alerts WHERE alert_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $alert_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        echo "Order placed successfully!";
    } else {
        echo "No details found for the selected alert.";
    }
}
?>