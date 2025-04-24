<?php
require_once 'db.php';

$limit = $_POST['limit'] ?? 20;
$table = $_POST['table'] ?? '';
$ids = $_POST['ids'] ?? [];

if (!$table || empty($ids)) {
    echo "<p class='text-danger'>Invalid table or no rows selected.</p>";
    exit;
}
$page = 1;
$limit = ($limit === 'ALL') ? 'ALL' : intval($limit);
$page = max(1, intval($page));

// Calculate pagination
$offset = 0;
if ($limit !== 'ALL') {
    $offset = ($page - 1) * $limit;
    $limitClause = "LIMIT $offset, $limit";
} else {
    $limitClause = "";
}

$escapedIds = array_map('intval', $ids); // Simple sanitization assuming numeric IDs
$idsString = implode(',', $escapedIds);
//print_r($table);
$primaryKey = getPrimaryKey($conn, $table) ?? 'id'; // fallback if not found
// Fetch rows to edit
$sql = "SELECT * FROM `$table` WHERE $primaryKey IN ($idsString) $limitClause";
$result = $conn->query($sql);
$numRows = $result->num_rows;

if ($limit == 'ALL') {
    if ($numRows  == 0){
        $totalPages = 1;
    }else{
        $totalPages = ceil($numRows / $numRows);
    }
    
}else{
    $totalPages = max(1, ceil($numRows / $limit));
}

//echo "<h4>Edit Rows in $table</h4>";
echo "
<div class='d-flex justify-content-between align-items-center mb-3 flex-wrap'>
  <h4 class='mb-0'>Edit Rows in $table</h4>
    <button class='btn btn-sm btn-outline-warning text-black' id='back-to-tables-insert' data-table='" . htmlspecialchars($table) . "'>
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

$columnMeta = [];
$metaQuery = "DESCRIBE `$table`";
$metaResult = $conn->query($metaQuery);
if ($metaResult) {
    while ($metaRow = $metaResult->fetch_assoc()) {
        $columnMeta[$metaRow['Field']] = $metaRow['Type'];
    }
}

while ($row = $result->fetch_assoc()) {
    $primaryKeyValue = $row[$primaryKey];

    $messageId = 'message-' . $primaryKeyValue;

    echo "<form class='edit-row-form mb-2 p-2 border rounded bg-light d-flex align-items-center flex-wrap gap-2' data-id='" . htmlspecialchars($primaryKeyValue) . "'>";

    // Hidden fields
    echo "<input type='hidden' name='table' value='" . htmlspecialchars($table) . "'>";
    echo "<input type='hidden' name='primaryKey' value='" . htmlspecialchars($primaryKey) . "'>";
    echo "<input type='hidden' name='primaryValue' value='" . htmlspecialchars($primaryKeyValue) . "'>";

    foreach ($row as $column => $value) {
        $type = $columnMeta[$column] ?? 'text';
        $inputType = 'text';

        // Determine input type from SQL column type
        if (preg_match('/int|bigint/', $type)) {
            $inputType = 'number';
            $step = 'step="1"';
        } elseif (preg_match('/float|double|decimal/', $type)) {
            $inputType = 'number';
            $step = 'step="0.01"';
        } elseif (preg_match('/date/', $type)) {
            $inputType = 'date';
        } elseif (preg_match('/time/', $type)) {
            $inputType = 'time';
        } elseif (preg_match('/text|blob/', $type)) {
            $inputType = 'textarea';
        } elseif (preg_match('/bool/', $type)) {
            $inputType = 'checkbox';
        }

        echo "<div class='d-flex flex-column'>";
        echo "<label class='small fw-bold mb-1'>" . htmlspecialchars($column) . "</label>";

        if ($inputType === 'textarea') {
            echo "<textarea class='form-control form-control-sm' name='" . htmlspecialchars($column) . "'>" . htmlspecialchars($value) . "</textarea>";
        } elseif ($inputType === 'checkbox') {
            // Hidden fallback for unchecked box
            echo "<input type='hidden' name='" . htmlspecialchars($column) . "' value='0'>";
            $checked = ($value) ? 'checked' : '';
            echo "<input type='checkbox' class='form-check-input' name='" . htmlspecialchars($column) . "' value='1' $checked>";
        } else {
            $extra = '';
            $step = ($inputType === 'number') ? 'step="1"' : '';
            if ($inputType === 'number' && preg_match('/salary|amount|price|count|quantity/i', $column)) {
                $extra .= ' min="0"';
                $step = 'step="0.01"';
            }

            echo "<input type='$inputType' class='form-control form-control-sm' name='" . htmlspecialchars($column) . "' value='" . htmlspecialchars($value) . "' $extra $step>";
        }

        echo "</div>";
    }

    //echo "<div id='message' class='mt-3'></div>";
    
    echo "<button type='submit' class='btn btn-sm btn-success ms-2'>Save</button>";
    echo "<div id='$messageId' class='mt-3'></div>";
    echo "</form>";
}



function getPrimaryKey($conn, $table) {
    $query = "SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['Column_name'];
    }
    return null;
}
?>