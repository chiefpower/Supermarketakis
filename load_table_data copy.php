<?php
session_start();
require_once 'db.php';

$table = $_GET['table'] ?? '';

if (!$table || !preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    echo "<p class='text-danger'>Invalid table name.</p>";
    exit;
}

$result = $conn->query("SELECT * FROM `$table` LIMIT 10");

if (!$result) {
    echo "<p class='text-danger'>Failed to query table: " . $conn->error . "</p>";
    exit;
}

// Display as simple table
//echo "<h4>Contents of $table</h4>";
echo "
<div class='d-flex justify-content-between align-items-center mb-3 flex-wrap'>
  <h4 class='mb-0'>Contents of $table</h4>
  
  <div class='d-flex align-items-center gap-2'>
    <button class='btn btn-sm btn-outline-light me-2' id='prev-page'>&larr; Prev</button>
    <button class='btn btn-sm btn-outline-light me-3' id='next-page'>Next &rarr;</button>

    <span class='text-black me-2'>Show:</span>
    <div class='btn-group btn-group-sm' role='group' aria-label='Row count selector'>
      <button type='button' class='btn btn-outline-light row-limit' data-limit='10'>10</button>
      <button type='button' class='btn btn-outline-light row-limit' data-limit='20'>20</button>
      <button type='button' class='btn btn-outline-light row-limit' data-limit='50'>50</button>
      <button type='button' class='btn btn-outline-light row-limit' data-limit='ALL'>ALL</button>
    </div>
  </div>
</div>
";

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

$conn->close();
?>
