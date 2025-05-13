<?php
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "arimorid";
} else {
    $servername = "sdb-80.hosting.stackcp.net";
    $username = "arimoride-353038356e7a";
    $password = "01#Admin@arimoridgr";
    $dbname = "arimoride-353038356e7a";
}

try {
    // Create connection without specifying a database
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if database exists
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if ($result->num_rows == 0) {
        $createDbQuery = "CREATE DATABASE $dbname";
        if ($conn->query($createDbQuery) === TRUE) {
            echo "Database '$dbname' created successfully.";
        } else {
            die("Error creating database: " . $conn->error);
        }
    }
    
    // Now select the database
    $conn->select_db($dbname);
} catch (mysqli_sql_exception $e) {
    die("Database error: " . $e->getMessage());
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>