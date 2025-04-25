<?php
include 'db.php';

header('Content-Type: application/json');

try {
    $sql = "
        SELECT 
            DATE(uo.created_at) AS sale_date,
            SUM(uoi.quantity * uoi.price) AS total_sales
        FROM 
            user_order_items uoi
        JOIN 
            user_orders uo ON uoi.order_id = uo.id
        WHERE 
            uo.status IN ('confirmed', 'shipped', 'delivered')
        GROUP BY 
            DATE(uo.created_at)
        ORDER BY 
            sale_date ASC
    ";

    $result = $conn->query($sql);
    $dates = [];
    $totals = [];

    while ($row = $result->fetch_assoc()) {
        $dates[] = $row['sale_date'];
        $totals[] = (float) $row['total_sales'];
    }

    echo json_encode([
        'success' => true,
        'labels' => $dates,
        'data' => $totals
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>