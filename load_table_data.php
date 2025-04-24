<?php
session_start();
require_once 'db.php';

$table = $_GET['table'] ?? '';
$limit = $_GET['limit'] ?? 20;
$page = $_GET['page'] ?? 1;


if (!$table || !preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    echo "<p class='text-danger'>Invalid table name.</p>";
    exit;
}

$limit = ($limit === 'ALL') ? 'ALL' : intval($limit);
$page = max(1, intval($page));

// Get total row count
$countResult = $conn->query("SELECT COUNT(*) as total FROM `$table`");
$totalRows = ($countResult && $countRow = $countResult->fetch_assoc()) ? $countRow['total'] : 0;

// Calculate pagination
$offset = 0;
if ($limit !== 'ALL') {
    $offset = ($page - 1) * $limit;
    $limitClause = "LIMIT $offset, $limit";
} else {
    $limitClause = "";
}


$result = $conn->query("SELECT * FROM `$table` $limitClause");

if (!$result) {
    echo "<p class='text-danger'>Failed to query table: " . $conn->error . "</p>";
    exit;
}

if ($limit == 'ALL') {
    if ($totalRows  == 0){
        $totalPages = 1;
    }else{
        $totalPages = ceil($totalRows / $totalRows);
    }
    
}else{
    $totalPages = max(1, ceil($totalRows / $limit));
}

//$totalPages = ceil($totalRows / $limit);
// Display as simple table
//echo "<h4>Contents of $table</h4>";
echo "
<div class='d-flex justify-content-between align-items-center mb-3 flex-wrap'>
  <h4 class='mb-0'>Contents of $table</h4>
    <button class='btn btn-sm btn-outline-warning text-black' id='back-to-tables'>
      &larr; Back to Tables
    </button>
  <div class='d-flex align-items-center gap-2 flex-wrap'>

    <button class='btn btn-sm btn-outline-light' id='first-page'>&laquo; First</button>
    <button class='btn btn-sm btn-outline-light' id='prev-page'>&larr; Prev</button>

    <span class='text-black'>Page <strong>$page</strong> of <strong>$totalPages</strong></span>

    <button class='btn btn-sm btn-outline-light' id='next-page'>Next &rarr;</button>
    <button class='btn btn-sm btn-outline-light' id='last-page'>Last &raquo;</button>

    <span class='text-black ms-3'>Show:</span>
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
echo "<script>$('#content-area').data('total-pages', $totalPages);</script>";
// Show pagination info (optional)
if ($limit !== 'ALL') {
  //  $totalPages = ceil($totalRows / $limit);
    echo "<p class='text-black small'>Page $page of $totalPages • $totalRows total rows</p>";
}

$conn->close();
?>
