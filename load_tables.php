<?php
session_start();
require_once 'db.php';

$result = $conn->query("SHOW TABLES");

if ($result) {
   //echo "<h4>Available Tables</h4><ul class='list-group'>";
   echo "
   <div class='d-flex justify-content-between align-items-center mb-3 flex-wrap'>
   
       <!-- Available Tables title, aligned to the left -->
       <h4 class='mb-0'>Available Tables:</h4>
   
       <!-- Centered Add/Delete/Update label with checkbox for selection -->
       <div class='d-flex align-items-center gap-2 mx-auto'>
           <span class='text-dark'>Add / Delete / Update:</span>
           <div class='form-check form-check-inline'>
               <input class='form-check-input' type='checkbox' id='action-toggle' value='toggle'>
               <label class='form-check-label' for='action-toggle'></label>
           </div>
       </div>
   
   </div>
   ";

   echo "<div class='row'>"; // Start Bootstrap row

    $count = 0;
    while ($row = $result->fetch_array()) {
        $tableName = htmlspecialchars($row[0]);

        echo "<div class='col-md-4 mb-3'>"; // 3 columns
        echo "<a href='#' class='table-link d-block p-2 rounded text-center' data-table='$tableName' style='text-decoration: none; color: #000;'>";
        echo $tableName;
        echo "</a>";
        echo "</div>";

        $count++;
    }

    if ($count === 0) {
        echo "<p class='text-black'>No tables found.</p>";
    }

    echo "</div>"; // Close row
} else {
    echo "<p class='text-warning'>No tables found in the database.</p>";
}


?>