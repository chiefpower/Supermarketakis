<?php
require_once 'db.php';

$table = $_POST['table'] ?? '';
if (!$table) {
    echo "<p class='text-danger'>Invalid table specified.</p>";
    exit;
}

// Fetch column meta including 'Extra'
$columnMeta = [];
$metaQuery = "DESCRIBE `$table`";
$metaResult = $conn->query($metaQuery);
if ($metaResult) {
    while ($metaRow = $metaResult->fetch_assoc()) {
        $columnMeta[] = [
            'Field' => $metaRow['Field'],
            'Type' => $metaRow['Type'],
            'Extra' => $metaRow['Extra'],
        ];
    }
}

echo "
<div class='d-flex justify-content-between align-items-center mb-3 flex-wrap'>
  <h4 class='mb-0'>Insert New Row into <strong>" . htmlspecialchars($table) . "</strong></h4>
  <button class='btn btn-sm btn-outline-warning text-black' 
          id='back-to-tables-insert' 
          data-table='" . htmlspecialchars($table) . "'>
    &larr; Back to Tables
  </button>
  <div class='d-flex align-items-left gap-2 flex-wrap'></div>
  <div class='d-flex align-items-left gap-2 flex-wrap'></div>
</div>
<form class='insert-row-form p-3 border rounded bg-light d-flex align-items-center flex-wrap gap-3' id='insert-form'>
";

foreach ($columnMeta as $col) {
    $column = $col['Field'];
    $type = $col['Type'];
    $extra = $col['Extra'];
    $isAutoIncrement = stripos($extra, 'auto_increment') !== false;

    $inputType = 'text';
    $step = '';
    $readonlyAttr = $isAutoIncrement ? 'readonly disabled' : '';

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
        echo "<textarea class='form-control form-control-sm' name='" . htmlspecialchars($column) . "' $readonlyAttr></textarea>";
    } elseif ($inputType === 'checkbox') {
        echo "<input type='checkbox' class='form-check-input' name='" . htmlspecialchars($column) . "' value='1' $readonlyAttr>";
    } else {
        echo "<input type='$inputType' class='form-control form-control-sm' name='" . htmlspecialchars($column) . "' $step $readonlyAttr>";
    }

    echo "</div>";
}

echo "
    <input type='hidden' name='table' value='" . htmlspecialchars($table) . "'>
    <button type='submit' class='btn btn-success'>Insert Row</button>
    <div id='message' class='mt-3'>
</form>
";
?>