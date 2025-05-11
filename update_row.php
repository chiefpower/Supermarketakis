<?php
require_once 'db.php';
session_start();
header('Content-Type: application/json');

$table = $_POST['table'] ?? '';
$primaryKey = $_POST['primaryKey'] ?? '';
$primaryValue = $_POST['primaryValue'] ?? '';

if (!$table || !$primaryKey || !$primaryValue) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

// Detect foreign keys for the table
$foreignKeys = [];
$fkQuery = $conn->prepare("
    SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = ? 
      AND REFERENCED_TABLE_NAME IS NOT NULL
");
$fkQuery->bind_param("s", $table);
$fkQuery->execute();
$fkResult = $fkQuery->get_result();

while ($fk = $fkResult->fetch_assoc()) {
    $foreignKeys[$fk['COLUMN_NAME']] = [
        'referenced_table' => $fk['REFERENCED_TABLE_NAME'],
        'referenced_column' => $fk['REFERENCED_COLUMN_NAME']
    ];
}

$updateParts = [];
$types = '';
$values = [];

foreach ($_POST as $key => $val) {
    if (in_array($key, ['table', 'primaryKey', 'primaryValue'])) continue;

    $updateParts[] = "`$key` = ?";
    
    // Handle NULL values
    if ($val === '' || strtolower($val) === 'null') {
        $types .= 's';
        $values[] = null;
    } else {
        $types .= is_numeric($val) ? 'i' : 's';
        $values[] = $val;
    }

    // Validate foreign key
    if (array_key_exists($key, $foreignKeys) && $val !== '' && strtolower($val) !== 'null') {
        $ref = $foreignKeys[$key];
        $checkStmt = $conn->prepare("SELECT 1 FROM `{$ref['referenced_table']}` WHERE `{$ref['referenced_column']}` = ? LIMIT 1");
        $checkType = is_numeric($val) ? 'i' : 's';
        $checkStmt->bind_param($checkType, $val);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => "Foreign key constraint failed: $key = $val not found in {$ref['referenced_table']}"
            ]);
            exit;
        }
    }
}


// Primary key condition (WHERE clause)
$whereClause = "`$primaryKey` = ?";
$types .= is_numeric($primaryValue) ? 'i' : 's';
$values[] = $primaryValue;

if (empty($whereClause)) {
    echo json_encode(['status' => 'error', 'message' => 'No fields provided to update.']);
    exit;
}

// Construct the SQL query
$sql = "UPDATE `$table` SET " . implode(', ', $updateParts) . " WHERE $whereClause";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

// Bind parameters dynamically
$bindValues = [];
foreach ($values as $i => $val) {
    $bindValues[$i] = &$values[$i];
}
array_unshift($bindValues, $types);
call_user_func_array([$stmt, 'bind_param'], $bindValues);

try {
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Row updated successfully.']);
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
}
?>