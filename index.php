<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "ebookDB";
$sqlFilePath = "ebookDB.txt";

try {
    // Connect to MySQL server
    $conn = new mysqli($host, $username, $password);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "<p>Connected to MySQL server successfully.</p>";

    // Check if the database exists
    $dbExistsQuery = "SHOW DATABASES LIKE '$database'";
    $result = $conn->query($dbExistsQuery);
    if ($result->num_rows > 0) {
        echo "<p>Database '$database' already exists. No action needed.</p>";
    } else {
        echo "<p>Database '$database' does not exist. Creating it...</p>";

        // Read SQL from the file
        if (!file_exists($sqlFilePath)) {
            throw new Exception("SQL file '$sqlFilePath' not found.");
        }
        $sqlCommands = file_get_contents($sqlFilePath);

        // Execute SQL commands
        if ($conn->multi_query($sqlCommands)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
                if ($conn->error) {
                    throw new Exception("Error in SQL command: " . $conn->error);
                }
            } while ($conn->more_results() && $conn->next_result());
        } else {
            throw new Exception("Error executing SQL commands: " . $conn->error);
        }

        echo "<p>Database '$database' created successfully.</p>";
    }

    echo '<p><a href="user/login.php">Go to Home Page</a></p>';
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
} finally {
    $conn->close();
}
?>
