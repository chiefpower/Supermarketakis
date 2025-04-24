<?php
require_once 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // enable exceptions

header('Content-Type: application/json');

try {
    $table = $_POST['table'] ?? '';
    if (!$table) {
        throw new Exception('No table specified.');
    }

    unset($_POST['table']);

    // Convert checkboxes (on → 1)
    foreach ($_POST as $k => $v) {
        if ($v === 'on') {
            $_POST[$k] = 1;
        }
    }

    $columns = array_keys($_POST);
    $values = array_values($_POST);

    // Validate required columns
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
    $checkQuery->bind_param("s", $table);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    while ($row = $result->fetch_assoc()) {
        $requiredCols[] = $row['COLUMN_NAME'];
    }

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

    // Prepare SQL
    $placeholders = array_fill(0, count($columns), '?');
    $sql = "INSERT INTO `$table` (`" . implode('`,`', $columns) . "`) VALUES (" . implode(',', $placeholders) . ")";
    $stmt = $conn->prepare($sql);

    // Detect parameter types dynamically
    $types = '';
    $bindValues = [];

    foreach ($values as $val) {
        if (is_null($val) || $val === '') {
            $types .= 's'; // use 's' for NULL-safe binding
            $bindValues[] = null;
        } elseif (is_numeric($val)) {
            if (strpos($val, '.') !== false) {
                $types .= 'd'; // float
                $bindValues[] = (float) $val;
            } else {
                $types .= 'i'; // integer
                $bindValues[] = (int) $val;
            }
        } else {
            $types .= 's'; // string
            $bindValues[] = $val;
        }
    }

    // Bind parameters dynamically
    $stmt->bind_param($types, ...$bindValues);
    $stmt->execute();

    //echo json_encode(['success' => true, 'message' => 'Row inserted successfully.']);
    $primaryKey = getPrimaryKey($conn, $table);
    $insertedId = $conn->insert_id;

    echo json_encode([
        'success' => true,
        'message' => 'Row inserted successfully.',
        'undo' => [
            'table' => $table,
            'primary_key' => $primaryKey,
            'id' => $insertedId
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Get the primary key column name for the table
function getPrimaryKey($conn, $table) {
    $query = $conn->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_KEY = 'PRI'
        LIMIT 1
    ");
    $query->bind_param("s", $table);
    $query->execute();
    $res = $query->get_result();
    $row = $res->fetch_assoc();
    return $row ? $row['COLUMN_NAME'] : null;
}
?>