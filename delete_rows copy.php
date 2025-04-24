<?php
require_once 'db.php';

$table = $_POST['table'] ?? '';
$ids = $_POST['ids'] ?? [];

if (!$table || empty($ids)) {
    echo "<p class='text-danger'>Invalid table or no rows selected.</p>";
    exit;
}

$escapedIds = array_map('intval', $ids); // Again assuming numeric IDs
$idsString = implode(',', $escapedIds);
$primaryKey = getPrimaryKey($conn, $table) ?? 'id'; // fallback if not found

$sql = "DELETE FROM `$table` WHERE $primaryKey IN ($idsString)";
if ($conn->query($sql)) {
    echo "<p class='text-success'>Successfully deleted " . count($ids) . " row(s).</p>";
} else {
    echo "<p class='text-danger'>Error deleting rows: " . $conn->error . "</p>";
}

function getPrimaryKey($conn, $table) {
    $query = "SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['Column_name'];
    }
    return null;
}
?>