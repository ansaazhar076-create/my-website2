<?php
session_start();
require_once 'functions.php';

if(!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

global $conn;

$uid = $_SESSION['user_id'];
$result = $conn->query("
    SELECT b.*, t.package_name, t.destination, t.price 
    FROM bookings b
    JOIN tour_packages t ON b.package_id = t.id
    WHERE b.user_id = $uid
    ORDER BY b.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - TravelWorld</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
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
        .bookings-container {
            max-width: 1200px;
            margin: 0 auto;
            padding-bottom: 50px;
        }
        .booking-card {
            background: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            margin-bottom: 25px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .booking-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .booking-title {
            color: white;
            font-size: 1.4rem;
            font-weight: bold;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .booking-id {
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .status-badge {
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pending {
            background: var(--warning-color);
            color: white;
        }
        .status-confirmed {
            background: var(--success-color);
            color: white;
        }
        .status-cancelled {
            background: var(--accent-color);
            color: white;
        }
        .status-completed {
            background: var(--primary-color);
            color: white;
        }
        .booking-body {
            padding: 25px;
        }
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .detail-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .detail-content {
            flex: 1;
        }
        .detail-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .detail-value {
            font-size: 1.1rem;
            color: var(--primary-color);
            font-weight: 600;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .empty-state-icon {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        .empty-state h3 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        .empty-state p {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 25px;
        }
        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
            padding: 12px 30px;
            font-size: 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        .stats-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        .stat-card {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(41, 128, 185, 0.1));
            transform: translateY(-3px);
        }
        .stat-icon {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }

        @media (max-width: 768px) {
            .booking-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .booking-details {
                grid-template-columns: 1fr;
            }
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
                    <li class="nav-item"><a class="nav-link active" href="my-bookings.php">My Bookings</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1><i class="fas fa-bookmark"></i> My Bookings</h1>
            <p>Track and manage all your travel reservations</p>
        </div>
    </div>

    <!-- Bookings Content -->
    <div class="container bookings-container">
        <?php displayAlert(); ?>

        <?php 
        $bookings = [];
        $total_bookings = 0;
        $pending_count = 0;
        $confirmed_count = 0;
        
        while($b = $result->fetch_assoc()) {
            $bookings[] = $b;
            $total_bookings++;
            if($b['status'] == 'Pending') $pending_count++;
            if($b['status'] == 'Confirmed') $confirmed_count++;
        }
        ?>

        <?php if($total_bookings > 0): ?>
            <!-- Statistics Section -->
            <div class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                        <div class="stat-value"><?= $total_bookings; ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-value"><?= $pending_count; ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-value"><?= $confirmed_count; ?></div>
                        <div class="stat-label">Confirmed</div>
                    </div>
                </div>
            </div>

            <!-- Bookings List -->
            <?php foreach($bookings as $booking): ?>
            <div class="booking-card">
                <div class="booking-header">
                    <div>
                        <h3 class="booking-title">
                            <i class="fas fa-map-marked-alt"></i>
                            <?= htmlspecialchars($booking['package_name']); ?>
                        </h3>
                        <div class="booking-id">
                            Booking ID: #<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>
                        </div>
                    </div>
                    <span class="status-badge status-<?= strtolower($booking['status']); ?>">
                        <?php
                        $status_icons = [
                            'Pending' => 'fa-clock',
                            'Confirmed' => 'fa-check-circle',
                            'Cancelled' => 'fa-times-circle',
                            'Completed' => 'fa-flag-checkered'
                        ];
                        $icon = $status_icons[$booking['status']] ?? 'fa-info-circle';
                        ?>
                        <i class="fas <?= $icon; ?>"></i> <?= htmlspecialchars($booking['status']); ?>
                    </span>
                </div>
                
                <div class="booking-body">
                    <div class="booking-details">
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Destination</div>
                                <div class="detail-value"><?= htmlspecialchars($booking['destination']); ?></div>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Travel Date</div>
                                <div class="detail-value"><?= date('M d, Y', strtotime($booking['travel_date'])); ?></div>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Travelers</div>
                                <div class="detail-value"><?= htmlspecialchars($booking['number_of_people']); ?> People</div>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="detail-content">
                                <div class="detail-label">Total Price</div>
                                <div class="detail-value"><?= formatCurrency($booking['price'] * $booking['number_of_people']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-suitcase"></i>
                </div>
                <h3>No Bookings Yet</h3>
                <p>Start planning your next adventure! Browse our amazing tour packages.</p>
                <a href="packages.php" class="btn btn-primary">
                    <i class="fas fa-compass"></i> Explore Packages
                </a>
            </div>
        <?php endif; ?>
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
</body>
</html>