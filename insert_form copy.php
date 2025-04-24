<?php
require_once 'db.php';

$table = $_POST['table'] ?? '';
if (!$table) {
    echo "<p class='text-danger'>Invalid table specified.</p>";
    exit;
}

// Fetch column meta
$columnMeta = [];
$metaQuery = "DESCRIBE `$table`";
$metaResult = $conn->query($metaQuery);
if ($metaResult) {
    while ($metaRow = $metaResult->fetch_assoc()) {
        $columnMeta[$metaRow['Field']] = $metaRow['Type'];
    }
}

echo "
<h4 class='mb-3'>Insert New Row into <strong>$table</strong></h4>
<form class='insert-row-form p-3 border rounded bg-light d-flex align-items-center flex-wrap gap-3' id='insert-form'>
";

foreach ($columnMeta as $column => $type) {
    // Optionally skip AUTO_INCREMENT fields (like primary key)
    if (stripos($type, 'auto_increment') !== false) {
        continue;
    }

    $inputType = 'text';
    $step = '';

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
    echo "<label class='small fw-bold mb-1'>$column</label>";

    if ($inputType === 'textarea') {
        echo "<textarea class='form-control form-control-sm' name='" . htmlspecialchars($column) . "'></textarea>";
    } elseif ($inputType === 'checkbox') {
        echo "<input type='checkbox' class='form-check-input' name='" . htmlspecialchars($column) . "' value='1'>";
    } else {
        echo "<input type='$inputType' class='form-control form-control-sm' name='" . htmlspecialchars($column) . "' $step>";
    }

    echo "</div>";
}

echo "
    <input type='hidden' name='table' value='" . htmlspecialchars($table) . "'>
    <button type='submit' class='btn btn-success'>Insert Row</button>
    <div id='message' class='mt-3'>
    <span class='insert-feedback ms-2 small'></span>
</form>
";
?>