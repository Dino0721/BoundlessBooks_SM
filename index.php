<?php
// Database connection settings
$host = "localhost";
$username = "root";
$password = "";

// Database name
$database = "ebookDB";

// SQL file path
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
        echo "<p>Database '$database' already exists. No action required.</p>";
    } else {
        echo "<p>Database '$database' does not exist. Creating it...</p>";

        // Read SQL from the file
        if (!file_exists($sqlFilePath)) {
            throw new Exception("SQL file '$sqlFilePath' not found.");
        }
        $sqlCommands = file_get_contents($sqlFilePath);

        // Execute SQL commands
        if ($conn->multi_query($sqlCommands)) {
            echo "<p>Database '$database' created successfully.</p>";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }

        // Wait for all queries to finish executing
        while ($conn->more_results() && $conn->next_result());

        echo "<p>All SQL commands executed successfully.</p>";
    }

    // Display a link to the home page
    echo '<p><a href="../user/login.php">Go to Login</a></p>';
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
} finally {
    $conn->close();
}
?>

<?php
require 'pageFormat/base.php';

$_title = 'BoundlessBooks';
// include 'pageFormat/head.php';
// require 'loginSide/login.php';
// include 'landingPage.php';
?>

<?php
include 'pageFormat/footer.php';
?>
