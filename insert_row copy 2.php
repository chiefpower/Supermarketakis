<?php
require_once 'db.php';

header('Content-Type: application/json'); // Always return JSON

// Uncomment during development
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

$table = $_POST['table'] ?? '';
if (!$table) {
    echo json_encode(['success' => false, 'message' => 'No table specified.']);
    exit;
}

unset($_POST['table']);

// Handle checkboxes (e.g., <input type="checkbox" ...>)
foreach ($_POST as $k => $v) {
    if ($v === 'on') {
        $_POST[$k] = 1;
    }
}

$columns = array_keys($_POST);

// Escape and format values
$values = array_map(function ($val) use ($conn) {
    if ($val === '' || strtolower($val) === 'null') {
        return "NULL";
    }
    return "'" . $conn->real_escape_string($val) . "'";
}, array_values($_POST));

// Check for required NOT NULL columns without default
$requiredCols = [];
$checkQuery = $conn->prepare("
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = ? 
      AND IS_NULLABLE = 'NO' 
      AND COLUMN_DEFAULT IS NULL 
      AND EXTRA NOT LIKE '%auto_increment%'
");

if (!$checkQuery) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$checkQuery->bind_param("s", $table);
$checkQuery->execute();
$result = $checkQuery->get_result();

while ($row = $result->fetch_assoc()) {
    $requiredCols[] = $row['COLUMN_NAME'];
}

// Validate required fields
$missingFields = [];
foreach ($requiredCols as $col) {
    if (!isset($_POST[$col]) || $_POST[$col] === '') {
        $missingFields[] = $col;
    }
}

if (!empty($missingFields)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: ' . implode(', ', $missingFields)
    ]);
    exit;
}

// Build SQL
$sql = "INSERT INTO `$table` (`" . implode('`,`', $columns) . "`) VALUES (" . implode(',', $values) . ")";
//echo json_encode(['debug_sql' => $sql]); exit;
if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Row inserted successfully.']);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Insert failed: ' . $conn->error
    ]);
}
?>