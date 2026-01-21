<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tour_travel_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully!<br>";
    
    // Check if admin exists
    $result = $conn->query("SELECT * FROM admins");
    if($result->num_rows > 0) {
        echo "Admin table found with " . $result->num_rows . " records.<br>";
        while($row = $result->fetch_assoc()) {
            echo "Username: " . $row['username'] . "<br>";
        }
    } else {
        echo "No admin found. Please import the database.sql file.";
    }
}
?>