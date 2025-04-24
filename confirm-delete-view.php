<?php
require_once 'db.php';

$table = $_POST['table'] ?? '';
$ids = $_POST['ids'] ?? [];
//print_r($ids);
//error_log(print_r($ids, true)); // writes to server's error log
if (!$table || !preg_match('/^[a-zA-Z0-9_]+$/', $table) || empty($ids)) {
    echo "<p class='text-danger'>Invalid request.</p>";
    exit;
}

// Detect primary key dynamically
$pkResult = $conn->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
$pkRow = $pkResult->fetch_assoc();
$primaryKey = $pkRow['Column_name'] ?? null;

if (!$primaryKey) {
    echo "<p class='text-danger'>Could not detect primary key.</p>";
    exit;
}

$idList = implode(",", array_map(function ($id) use ($conn) {
    return "'" . $conn->real_escape_string($id) . "'";
}, $ids));
//print_r($idList);
$result = $conn->query("SELECT * FROM `$table` WHERE `$primaryKey` IN ($idList)");

echo "<h5 class='text-danger'>Confirm Deletion</h5>";
echo "<p>The following rows in table <strong>$table</strong> will be <strong>permanently deleted</strong>. Are you sure?</p>";

echo "<table class='table table-bordered table-sm bg-white'><thead><tr>";
while ($field = $result->fetch_field()) {
    echo "<th>" . htmlspecialchars($field->name) . "</th>";
}
echo "</tr></thead><tbody>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    foreach ($row as $cell) {
        echo "<td>" . htmlspecialchars($cell) . "</td>";
    }
    echo "</tr>";
}
echo "</tbody></table>";

echo "
<div class='d-flex gap-2 align-items-start flex-wrap w-100'>
    <button class='btn btn-sm btn-danger' id='confirm-delete' 
            data-table='" . htmlspecialchars($table) . "' 
            data-ids='" . htmlspecialchars(json_encode($ids)) . "'>
        Yes, Delete
    </button>
    <button class='btn btn-sm btn-secondary' id='cancel-delete' data-table='" . htmlspecialchars($table) . "'>Cancel</button>
    <div id='message' class='flex-grow-1 d-flex align-items-center' style='min-height: 32px;'></div>
</div>
";
?>