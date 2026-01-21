<?php
session_start();
require_once 'functions.php';

// Check if user is logged in
if(!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Validate package ID
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: packages.php");
    exit;
}

$package = getPackageById($_GET['id']);

// Check if package exists
if(!$package) {
    $_SESSION['error'] = "Package not found!";
    header("Location: packages.php");
    exit;
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    try {
        global $conn;
        
        // Get and validate form data
        $booking_date = sanitize($_POST['travel_date']);
        $people = (int)$_POST['people'];
        $uid = (int)$_SESSION['user_id'];
        $pid = (int)$package['id'];
        
        // Calculate total amount
        $total_amount = $package['price'] * $people;
        
        // Get special requests (optional)
        $special_requests = isset($_POST['special_requests']) ? sanitize($_POST['special_requests']) : '';
        
        // Validate inputs
        if(empty($booking_date) || $people < 1) {
            $_SESSION['error'] = "Please fill all fields correctly!";
            header("Location: book-now.php?id=" . $pid);
            exit;
        }
        
        // Using prepared statement to prevent SQL injection
        // Match your actual database column names: booking_date, total_amount, special_requests
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, package_id, booking_date, number_of_people, total_amount, special_requests, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        
        if(!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("iisids", $uid, $pid, $booking_date, $people, $total_amount, $special_requests);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Booking Successful! Your booking has been confirmed.";
            $stmt->close();
            header("Location: my-bookings.php");
            exit;
        } else {
            throw new Exception("Booking failed: " . $stmt->error);
        }
        
    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        error_log("Booking Error: " . $e->getMessage()); // Log error for debugging
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Package - TravelWorld</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        .navbar {
            background-color: var(--primary-color) !important;
            padding: 15px 0;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .hero-section p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        .booking-container {
            max-width: 900px;
            margin: 0 auto;
            padding-bottom: 50px;
        }
        .package-info-card {
            background: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        .package-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .package-header h3 {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
        }
        .package-details {
            padding: 25px 30px;
            background: #f8f9fa;
        }
        .detail-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-item i {
            font-size: 1.3rem;
            color: var(--secondary-color);
            width: 40px;
            text-align: center;
        }
        .detail-item span {
            font-size: 1rem;
            color: #495057;
        }
        .booking-form-card {
            background: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 35px;
        }
        .form-section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--secondary-color);
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-label i {
            color: var(--secondary-color);
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .btn-book {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 50px;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
            background: linear-gradient(135deg, #2980b9, var(--secondary-color));
        }
        .features-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .features-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        .feature-badge {
            background: white;
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin: 5px;
            display: inline-block;
        }
        .price-display {
            background: var(--accent-color);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            font-size: 1.5rem;
            font-weight: bold;
            display: inline-block;
            margin: 15px 0;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-plane"></i> TravelWorld
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="packages.php">Packages</a></li>
                    <li class="nav-item"><a class="nav-link" href="my-bookings.php">My Bookings</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1><i class="fas fa-ticket-alt"></i> Complete Your Booking</h1>
            <p>You're just one step away from your dream vacation</p>
        </div>
    </div>

    <!-- Booking Content -->
    <div class="container booking-container">
        <?php displayAlert(); ?>

        <!-- Package Information -->
        <div class="package-info-card">
            <div class="package-header">
                <h3><i class="fas fa-map-marked-alt"></i> <?= htmlspecialchars($package['package_name']); ?></h3>
            </div>
            <div class="package-details">
                <div class="detail-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><strong>Destination:</strong> <?= htmlspecialchars($package['destination']); ?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-clock"></i>
                    <span><strong>Duration:</strong> <?= htmlspecialchars($package['duration']); ?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-users"></i>
                    <span><strong>Maximum Group Size:</strong> <?= htmlspecialchars($package['max_people']); ?> People</span>
                </div>
            </div>
            
            <?php if(!empty($package['features'])): ?>
            <div class="features-section mx-3 mb-3">
                <div class="features-title"><i class="fas fa-star"></i> Package Includes:</div>
                <?php 
                $features = explode(',', $package['features']);
                foreach($features as $feature): 
                ?>
                    <span class="feature-badge"><?= htmlspecialchars(trim($feature)); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="text-center pb-3">
                <div class="price-display">
                    <i class="fas fa-tag"></i> <?= formatCurrency($package['price']); ?> / person
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="booking-form-card">
            <h2 class="form-section-title">
                <i class="fas fa-edit"></i> Booking Details
            </h2>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-calendar-alt"></i>
                        Select Your Travel Date
                    </label>
                    <input 
                        type="date" 
                        name="travel_date" 
                        class="form-control" 
                        required
                        min="<?= date('Y-m-d'); ?>"
                    >
                    <small class="text-muted">Choose a date for your trip</small>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-users"></i>
                        Number of Travelers
                    </label>
                    <input 
                        type="number" 
                        name="people" 
                        class="form-control" 
                        required
                        min="1"
                        max="<?= $package['max_people']; ?>"
                        placeholder="Enter number of people"
                        id="people"
                        onchange="updateTotal()"
                    >
                    <small class="text-muted">Maximum <?= $package['max_people']; ?> people allowed</small>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-comment-dots"></i>
                        Special Requests (Optional)
                    </label>
                    <textarea 
                        name="special_requests" 
                        class="form-control" 
                        rows="3"
                        placeholder="Any special requests or requirements?"
                    ></textarea>
                </div>

                <div class="alert alert-info">
                    <strong><i class="fas fa-calculator"></i> Total Amount:</strong>
                    <span id="totalAmount" style="font-size: 1.3rem; font-weight: bold; color: var(--accent-color);">
                        <?= formatCurrency($package['price']); ?>
                    </span>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn-book">
                        <i class="fas fa-check-circle"></i> Confirm Booking
                    </button>
                </div>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i> Secure booking | 
                        <i class="fas fa-undo"></i> Free cancellation up to 24 hours
                    </small>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p>&copy; 2025 TravelWorld. All Rights Reserved.</p>
            <p>
                <i class="fas fa-phone"></i> +1-234-567-8900 | 
                <i class="fas fa-envelope"></i> info@travelworld.com
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calculate and update total amount dynamically
        function updateTotal() {
            const pricePerPerson = <?= $package['price']; ?>;
            const people = document.getElementById('people').value || 1;
            const total = pricePerPerson * people;
            
            // Format currency (you can adjust this based on your formatCurrency function)
            const formatted = ' + total.toFixed(2);
            document.getElementById('totalAmount').textContent = formatted;
        }
        
        // Update total on page load if value exists
        document.addEventListener('DOMContentLoaded', function() {
            updateTotal();
        });
    </script>
</body>
</html>