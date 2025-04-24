<?php
// Database connection
include 'db.php'; 

// Create connection with exception handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exceptions for MySQLi


// Check connection and handle errors
try {
    // Check if the connection is successful
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
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total_sales'];
        } else {
            return 0;
        }
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

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sales_per_store[] = [
                    'store_id' => $row['store_id'],
                    'store_name' => $row['store_name'],
                    'store_sales' => $row['store_sales']
                ];
            }
        }

        return $sales_per_store;
    } catch (Exception $e) {
        die("Error executing query: " . $e->getMessage());
    }
}

// Function to get store name by store_id
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

// Function to get product name by product_id
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

        // Get the product name
        $product_name = getProductName($conn, $product_id);

        // Get the store name
        $store_name = getStoreName($conn, $store_id);

        // Prepare the SQL statement to reduce the quantity
        $sql = "UPDATE store_inventory
                SET quantity = quantity - ?
                WHERE store_id = ? AND product_id = ? AND quantity >= ?";

        // Prepare and bind parameters
        $stmt = $conn->prepare($sql);

        // Check if prepare() failed
        if ($stmt === false) {
            throw new Exception("Error preparing the SQL statement: " . $conn->error);
        }

        $stmt->bind_param("iiii", $quantity_to_reduce, $store_id, $product_id, $quantity_to_reduce);

        // Execute the statement and check for errors
        if (!$stmt->execute()) {
            throw new Exception("Error executing update query: " . $stmt->error);
        }

        $stmt->close();

        // Display the quantity reduction details
      //  echo "Reduced quantity (Old Quantity: $current_quantity) for Product '$product_name' in Store '$store_name' by $quantity_to_reduce.<br>";
        
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

    echo '<div class="banner-ad large bg-info block-1 p-4" id="content-area" style="min-height: 20rem; max-height: 80vh; max-width: 100%; overflow: auto;">';
    
    if (empty($sales_per_store)) {
        echo "No sales data found per store.<br>";
    } else {
        echo "Sales per Store:<br>";
        foreach ($sales_per_store as $store) {
            $store_name = $store['store_name'];
            $store_id = $store['store_id'];
            $sales = $store['store_sales'];
        
            echo "<p class='text-black'>Store: $store_name (ID: $store_id) - Sales: $" . number_format((float)$sales, 2) . "</p>";
        }
    }
    echo '</div>';
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage();
}

// Close the connection
$conn->close();
?>