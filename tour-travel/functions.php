<?php
require_once 'config.php';

// Sanitize input data
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Get user data
function getUserData($user_id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Get all active packages
function getActivePackages() {
    global $conn;
    $sql = "SELECT * FROM tour_packages WHERE status = 'active' ORDER BY created_at DESC";
    $result = $conn->query($sql);
    return $result;
}

// Get package by ID
function getPackageById($id) {
    global $conn;
    $id = (int)$id;
    $sql = "SELECT * FROM tour_packages WHERE id = $id";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Show alert message
function showAlert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Display alert message
function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        $class = $alert['type'] == 'success' ? 'alert-success' : 'alert-danger';
        echo "<div class='alert $class alert-dismissible fade show' role='alert'>
                {$alert['message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        unset($_SESSION['alert']);
    }
}
?>