<?php
require_once 'db.php';

$targetPage = 'load_procedures.php';
renderProceduresTable($conn, $targetPage);

// Handle DELETE
if (isset($_GET['delete'])) {
    $procedure = $conn->real_escape_string($_GET['delete']);
    try {
        $conn->query("DROP PROCEDURE IF EXISTS `$procedure`");
        echo "<div class='alert alert-success'>Procedure <strong>$procedure</strong> deleted successfully.</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error deleting procedure: " . $e->getMessage() . "</div>";
    }
}

// Handle VIEW
if (isset($_GET['view'])) {

    $procedure = $conn->real_escape_string($_GET['view']);
    $result = $conn->query("SHOW CREATE PROCEDURE `$procedure`");

    if ($result && $row = $result->fetch_assoc()) {
        echo "<div class='card mb-4 shadow'>
                <div class='card-body'>
                    <h5 class='card-title'>Viewing Procedure: <code>" . sanitize($procedure) . "</code></h5>
                    <pre style='white-space: pre-wrap;'>" . sanitize($row['Create Procedure']) . "</pre>
                </div>
              </div>";
    } else {
        echo "<div class='alert alert-warning'>Procedure not found.</div>";
    }

}

// Handle EDIT (simple form to replace the procedure)
if (isset($_GET['edit'])) {
    $procedure = $conn->real_escape_string($_GET['edit']);
    $result = $conn->query("SHOW CREATE PROCEDURE `$procedure`");
    if ($result && $row = $result->fetch_assoc()) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newSQL = $_POST['definition'];
            try {
                $conn->query("DROP PROCEDURE IF EXISTS `$procedure`");
                $conn->query($newSQL);
                echo "<div class='alert alert-success'>Procedure <strong>$procedure</strong> updated successfully.</div>";
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>Error updating procedure: " . $e->getMessage() . "</div>";
            }
        }

        echo "<div class='card mb-4 shadow'>
                <div class='card-body'>
                    <h5 class='card-title'>Edit Procedure: <code>" . sanitize($procedure) . "</code></h5>
                    <form method='post'>
                        <div class='mb-3'>
                            <label for='definition' class='form-label'>Procedure SQL Definition</label>
                            <textarea name='definition' id='definition' class='form-control' rows='10'>" . sanitize($row['Create Procedure']) . "</textarea>
                        </div>
                        <button type='submit' class='btn btn-warning'>Save Changes</button>
                        <a href='#' class='btn btn-secondary' id='cancel-edit-proc'>Cancel</a>
                    </form>
                </div>
              </div>";
    } else {
        echo "<div class='alert alert-warning'>Procedure not found.</div>";
    }
    return;
}

function sanitize($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function renderProceduresTable($conn, $targetPage = 'load_procedures.php') {
    echo '<div class="card mb-4 shadow">
            <div class="card-body">
              <h5 class="card-title text-primary">Stored Procedures</h5>';

    try {
        $query = "SHOW PROCEDURE STATUS WHERE Db = DATABASE()";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            echo "<div class='table-responsive'><table class='table table-bordered table-sm'>";
            echo "<thead>
                    <tr>
                      <th>Name</th>
                      <th>Modified</th>
                      <th>Actions</th>
                    </tr>
                  </thead><tbody>";

            while ($row = $result->fetch_assoc()) {
                $name = urlencode($row['Name']);
                echo "<tr>
                        <td>{$row['Name']}</td>
                        <td>{$row['Modified']}</td>
                        <td>
                          <a href='#' class='btn btn-sm btn-info' id='show-stor-proc' data-name='{$row['Name']}'>View</a>
                          <a href='#' class='btn btn-sm btn-warning' id='edit-stor-proc' data-name='{$row['Name']}'>Edit</a>
                          <a href='{$targetPage}?delete={$name}' class='btn btn-sm btn-danger' onclick=\"return confirm('Delete procedure {$row['Name']}?');\">Delete</a>
                        </td>
                      </tr>";
            }

            echo "</tbody></table></div>";
        } else {
            echo "<p class='text-muted'>No stored procedures found.</p>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error fetching procedures: " . $e->getMessage() . "</div>";
    }

    echo '</div></div>';
}

?>