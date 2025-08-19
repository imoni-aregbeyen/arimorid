<?php
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "arimorid";
    define('CALLBACK_URL', 'http://localhost/arimorid/?page=payment-callback');
} else {
    $servername = "sdb-80.hosting.stackcp.net";
    $username = "arimoride-353038356e7a";
    $password = "01#Admin@arimoridgr";
    $dbname = "arimoride-353038356e7a";
    define('CALLBACK_URL', 'https://arimoridgr.com.ng/?page=payment-callback');
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

function get_data($tbl, $whr = '') {
  $conn = $GLOBALS['conn'];
  $rs = $conn->query("SELECT * FROM $tbl $whr ORDER BY id DESC");
  $num_rows = $rs->num_rows;
  $data = [];
  while ($row = $rs->fetch_assoc()) {
    $data[] = $row;
  }
  return $data;
}
?>