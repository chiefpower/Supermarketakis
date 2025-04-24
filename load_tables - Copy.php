<?php
                        // Query to get all table names
                        $tables = [];
                        $result = $conn->query("SHOW TABLES");

                        if ($result) {
                            while ($row = $result->fetch_array()) {
                                $tables[] = $row[0];
                            }
                        } else {
                            echo "Error fetching tables: " . $conn->error;
                        }
                      ?>  
                      <ul>
                        <?php foreach ($tables as $table): ?>
                            <li><?= htmlspecialchars($table) ?></li>
                        <?php endforeach; ?>
                      </ul>