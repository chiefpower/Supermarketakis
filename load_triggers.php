<?php
require_once 'db.php';

function sanitize($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Handle DELETE
if (isset($_GET['delete'])) {
    $trigger = $conn->real_escape_string($_GET['delete']);
    try {
        $conn->query("DROP TRIGGER `$trigger`");
        echo "<div class='alert alert-success'>Trigger <strong>$trigger</strong> deleted successfully.</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error deleting trigger: " . $e->getMessage() . "</div>";
    }
}

// Handle VIEW
if (isset($_GET['view'])) {
    $viewTrigger = $conn->real_escape_string($_GET['view']);
    $query = "SHOW TRIGGERS WHERE `Trigger` = '$viewTrigger'";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        echo "<div class='card mb-4 shadow'>
                <div class='card-body'>
                    <h5 class='card-title'>Viewing Trigger: <code>" . sanitize($row['Trigger']) . "</code></h5>
                    <p><strong>Table:</strong> " . sanitize($row['Table']) . "</p>
                    <p><strong>Event:</strong> " . sanitize($row['Event']) . "</p>
                    <p><strong>Timing:</strong> " . sanitize($row['Timing']) . "</p>
                    <p><strong>Statement:</strong><br><code>" . sanitize($row['Statement']) . "</code></p>
                    <a href='triggers.php' class='btn btn-secondary'>Back</a>
                </div>
              </div>";
    } else {
        echo "<div class='alert alert-warning'>Trigger not found.</div>";
    }
    return;
}

// Handle EDIT (form + recreate logic)
if (isset($_GET['edit'])) {
    $trigger = $conn->real_escape_string($_GET['edit']);
    $query = "SHOW TRIGGERS WHERE `Trigger` = '$trigger'";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // DROP & CREATE logic
            $newSQL = $_POST['statement'];
            try {
                $conn->query("DROP TRIGGER `$trigger`");
                $conn->query($newSQL);
                echo "<div class='alert alert-success'>Trigger <strong>$trigger</strong> updated.</div>";
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>Error updating trigger: " . $e->getMessage() . "</div>";
            }
        }

        // Edit form
        echo "<div class='card mb-4 shadow'>
                <div class='card-body'>
                    <h5 class='card-title'>Edit Trigger: <code>" . sanitize($row['Trigger']) . "</code></h5>
                    <form method='post'>
                        <div class='mb-3'>
                            <label for='statement' class='form-label'>Trigger SQL Statement</label>
                            <textarea name='statement' id='statement' class='form-control' rows='5'>" . sanitize($row['Statement']) . "</textarea>
                        </div>
                        <button type='submit' class='btn btn-warning'>Save Changes</button>
                        <a href='triggers.php' class='btn btn-secondary'>Cancel</a>
                    </form>
                </div>
              </div>";
    } else {
        echo "<div class='alert alert-warning'>Trigger not found.</div>";
    }
    return;
}

// Show All Triggers
echo '<div class="card mb-4 shadow">
        <div class="card-body">
          <h5 class="card-title text-primary">Database Triggers</h5>';

try {
    $query = "SHOW TRIGGERS";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo "<div class='table-responsive'><table class='table table-bordered table-sm'>";
        echo "<thead>
                <tr>
                  <th>Name</th>
                  <th>Table</th>
                  <th>Event</th>
                  <th>Timing</th>
                  <th>Statement</th>
                  <th>Actions</th>
                </tr>
              </thead><tbody>";

        while ($row = $result->fetch_assoc()) {
            $triggerName = urlencode($row['Trigger']);
            echo "<tr>
                    <td>{$row['Trigger']}</td>
                    <td>{$row['Table']}</td>
                    <td>{$row['Event']}</td>
                    <td>{$row['Timing']}</td>
                    <td><code style='white-space: nowrap;'>{$row['Statement']}</code></td>
                    <td>
                      <a href='triggers.php?view={$triggerName}' class='btn btn-sm btn-info'>View</a>
                      <a href='triggers.php?edit={$triggerName}' class='btn btn-sm btn-warning'>Edit</a>
                      <a href='triggers.php?delete={$triggerName}' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to delete this trigger?');\">Delete</a>
                    </td>
                  </tr>";
        }

        echo "</tbody></table></div>";
    } else {
        echo "<p class='text-muted'>No triggers found.</p>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error fetching triggers: " . $e->getMessage() . "</div>";
}

echo '</div></div>';
?>