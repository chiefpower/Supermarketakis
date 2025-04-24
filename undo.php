<?php
require_once 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header('Content-Type: application/json');

try {
    $table = $_POST['table'] ?? '';
    $id = $_POST['id'] ?? '';
    $pk = $_POST['pk'] ?? '';

    if (!$table || !$id || !$pk) {
        throw new Exception("Missing table, primary key, or ID.");
    }

    // Fetch the row first
    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rowData = $result->fetch_assoc();

    if (!$rowData) {
        throw new Exception("Row not found or already deleted.");
    }

    // Check if this is a confirm delete call
    if (isset($_POST['confirm']) && $_POST['confirm'] == '1') {
        $delStmt = $conn->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
        $delStmt->bind_param("i", $id);
        $delStmt->execute();
        echo json_encode(['success' => true, 'message' => 'Undo successful — row deleted.']);
    } else {
        echo json_encode([
            'success' => true,
            'preview' => $rowData,
            'message' => 'Confirm deletion of this row?'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Undo failed: ' . $e->getMessage()]);
}
?>