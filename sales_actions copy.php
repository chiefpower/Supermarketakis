<?php
// Database connection
include 'db.php'; 

// Create connection with exception handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exceptions for MySQLi

// Function to get store name based on store_id
function getStoreName($conn, $store_id) {
    try {
        $sql = "SELECT name FROM stores WHERE store_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $store_id);
        $stmt->execute();
        $stmt->bind_result($store_name);
        $stmt->fetch();
        $stmt->close();
        return $store_name;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// Function to get product name based on product_id
function getProductName($conn, $product_id) {
    try {
        $sql = "SELECT name FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($product_name);
        $stmt->fetch();
        $stmt->close();
        return $product_name;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// Function to reduce quantities based on store_id and product_id
function reduceQuantity($conn, $store_id, $product_id, $quantity_to_reduce) {
    // Check if the quantity to reduce is valid (positive integer)
    if ($quantity_to_reduce <= 0) {
        die("Error: Quantity to reduce must be a positive integer.");
    }

    try {
        // Get the current quantity before reducing
        $sql = "SELECT quantity FROM store_inventory WHERE store_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $store_id, $product_id);
        $stmt->execute();
        $stmt->bind_result($current_quantity);
        $stmt->fetch();
        $stmt->close();

        if ($current_quantity < $quantity_to_reduce) {
            echo "Error: Not enough stock to reduce for Product ID $product_id in Store ID $store_id.<br>";
            return;
        }

        // Prepare the SQL statement to reduce the quantity
        $sql = "UPDATE store_inventory
                SET quantity = quantity - ?
                WHERE store_id = ? AND product_id = ? AND quantity >= ?";

        // Prepare and bind parameters
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new Exception("Error preparing the SQL statement: " . $conn->error);
        }

        $stmt->bind_param("iiii", $quantity_to_reduce, $store_id, $product_id, $quantity_to_reduce);

        // Execute the statement and check for errors
        if (!$stmt->execute()) {
            throw new Exception("Error executing update query: " . $stmt->error);
        }

        $stmt->close();

        // Get the store and product names
        $store_name = getStoreName($conn, $store_id);
        $product_name = getProductName($conn, $product_id);

        // Display the quantity reduction details
        echo "Reduced quantity (Old Quantity: $current_quantity) for Product '$product_name' in Store '$store_name' by $quantity_to_reduce.<br>";

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// Function to get the products of each store
function getStoreProducts($conn, $store_id) {
    try {
        $sql = "SELECT product_id, quantity
                FROM store_inventory
                WHERE store_id = ?";

        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Error preparing the SQL statement: " . $conn->error);
        }

        $stmt->bind_param("i", $store_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $stmt->close();
        
        return $products;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// Main logic

try {
    // Calculate total sales for all stores
    $total_sales = getTotalSales($conn);
    if ($total_sales === 0) {
        echo "No sales data found.<br>";
    } else {
        echo "Total Sales for All Stores: $" . number_format($total_sales, 2) . "<br>";
    }

    // Calculate sales per store
    $sales_per_store = getSalesPerStore($conn);
    if (empty($sales_per_store)) {
        echo "No sales data found per store.<br>";
    } else {
        echo "Sales per Store:<br>";
        foreach ($sales_per_store as $store_id => $store_sales) {
            echo "Store Name: " . getStoreName($conn, $store_id) . " - Sales: $" . number_format($store_sales, 2) . "<br>";

            // Dynamically reduce quantity for each store
            $products = getStoreProducts($conn, $store_id);
            foreach ($products as $product) {
                // Example: Reduce by 10% of the quantity for demonstration purposes
                $quantity_to_reduce = (int) ($product['quantity'] * 0.1); // Reducing 10% for example
                if ($quantity_to_reduce > 0) {
                    reduceQuantity($conn, $store_id, $product['product_id'], $quantity_to_reduce);
                }
            }
        }
    }

} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage();
}

// Close the connection
$conn->close();
?>