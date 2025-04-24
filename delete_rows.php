<?php
require_once 'db.php';
//header('Content-Type: text/html; charset=UTF-8');

$table = $_POST['table'] ?? '';
$ids = $_POST['ids'] ?? [];

if (!$table || !preg_match('/^[a-zA-Z0-9_]+$/', $table) || empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$pkResult = $conn->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
$pkRow = $pkResult->fetch_assoc();
$primaryKey = $pkRow['Column_name'] ?? null;

if (!$primaryKey) {
    echo json_encode(['success' => false, 'message' => 'Could not detect primary key.']);
    exit;
}

$idList = implode(",", array_map(function ($id) use ($conn) {
    return "'" . $conn->real_escape_string($id) . "'";
}, $ids));

if ($conn->query("DELETE FROM `$table` WHERE `$primaryKey` IN ($idList)")) {
    echo json_encode([
        'success' => true,
        'message' => 'Rows deleted successfully: ' . implode(', ', $ids)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Delete failed: ' . $conn->error
    ]);
}
?>