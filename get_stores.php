<?php
require_once 'db.php';
header('Content-Type: application/json');

$storeQuery = "SELECT store_id, name FROM stores ORDER BY name";
$storeResult = $conn->query($storeQuery);

$stores = [];
while ($store = $storeResult->fetch_assoc()) {
    $stores[] = [
        'id' => $store['store_id'],
        'name' => $store['name']
    ];
}

echo json_encode($stores);
?>