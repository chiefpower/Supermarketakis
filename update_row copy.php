<?php
require_once 'db.php';

$table = $_POST['table'] ?? '';
$primaryKey = $_POST['primaryKey'] ?? '';
$primaryValue = $_POST['primaryValue'] ?? '';

if (!$table || !$primaryKey || !$primaryValue) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$updateParts = [];
$types = [];
$values = [];

foreach ($_POST as $key => $val) {
    if (in_array($key, ['table', 'primaryKey', 'primaryValue'])) continue;
    $updateParts[] = "`$key` = ?";
    $types[] = is_numeric($val) ? 'i' : 's';
    $values[] = $val;
}

if (empty($updateParts)) {
    echo json_encode(['status' => 'error', 'message' => 'No data to update.']);
    exit;
}

$sql = "UPDATE `$table` SET " . implode(', ', $updateParts) . " WHERE `$primaryKey` = ?";
$types[] = is_numeric($primaryValue) ? 'i' : 's';
$values[] = $primaryValue;

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

$stmt->bind_param(implode('', $types), ...$values);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Row updated']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
?>