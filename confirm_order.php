<?php
// confirm_order.php
// Database connection
include 'db.php'; 

// Get raw POST data

$data = json_decode(file_get_contents("php://input"), true);
//$order_id = intval($data['order_id']);
// Enable exception reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Return JSON
header('Content-Type: application/json');

try {
    // Check for order_id in POST data
    if (!isset($data['order_id'])) {
        throw new Exception("Order ID not provided.");
    }

    $order_id = intval($data['order_id']);

    // Prepare and execute the update
    $sql = "UPDATE user_orders SET status = 'confirmed' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    echo json_encode(["success" => true]);

    // Clean up
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Handle exceptions and return an error message
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
