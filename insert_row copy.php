<?php
require_once 'db.php';
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

$form = $_POST['formData'] ?? '';
$table = $_POST['table'] ?? '';
//print_r($table);
//print_r($form);
if (!$table) {
    echo json_encode(['success' => false, 'message' => 'No table specified.']);
    exit;
}

unset($_POST['table']);
$columns = array_keys($_POST);
//$columns = count($form);

// Handle checkboxes and convert them to 0/1
foreach ($_POST as $k => $v) {
    if ($v === 'on') {
        $_POST[$k] = 1;
    }
}

$values = array_map(function($val) use ($conn) {
    if ($val === '' || strtolower($val) === 'null') {
        return "NULL";
    }
    return "'" . $conn->real_escape_string($val) . "'";
}, array_values($_POST));

// Get required (NOT NULL, no DEFAULT) columns from the table
$requiredCols = [];
$query = $conn->prepare("
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = ? 
      AND IS_NULLABLE = 'NO' 
      AND COLUMN_DEFAULT IS NULL 
      AND EXTRA NOT LIKE '%auto_increment%'
");
$query->bind_param("s", $table);
$query->execute();
$result = $query->get_result();

while ($row = $result->fetch_assoc()) {
    $requiredCols[] = $row['COLUMN_NAME'];
}

// Check for missing required fields
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

//print_r($columns);
//print_r($values);
$sql = "INSERT INTO `$table` (`" . implode('`,`', $columns) . "`) VALUES (" . implode(',', $values) . ")";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Row inserted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $conn->error]);
}
?>