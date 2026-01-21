<?php
session_start();
require_once 'functions.php';

if(!isset($_GET['id'])) {
    header("Location: packages.php");
    exit;
}

$package = getPackageById($_GET['id']);
if(!$package) {
    header("Location: packages.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($package['package_name']); ?> - TravelWorld</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
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
        .breadcrumb-custom {
            background: transparent;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 0.95rem;
        }
        .breadcrumb-custom a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .breadcrumb-custom a:hover {
            color: white;
        }
        .breadcrumb-custom span {
            color: rgba(255,255,255,0.6);
        }
        .package-container {
            max-width: 1200px;
            margin: 0 auto;
            padding-bottom: 50px;
        }
        .image-section {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .package-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
        }
        .image-placeholder {
            width: 100%;
            height: 500px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 5rem;
        }
        .details-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 35px;
            margin-bottom: 30px;
        }
        .package-title {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .package-title i {
            color: var(--secondary-color);
        }
        .package-description {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #495057;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-left: 4px solid var(--secondary-color);
            border-radius: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-item {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(41, 128, 185, 0.05));
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .info-item:hover {
            transform: translateY(-3px);
            border-color: var(--secondary-color);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
        }
        .info-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        .info-content {
            flex: 1;
        }
        .info-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 1.2rem;
            color: var(--primary-color);
            font-weight: 600;
        }
        .features-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title i {
            color: var(--secondary-color);
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        .feature-item {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .feature-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .feature-item i {
            color: var(--success-color);
            font-size: 1.2rem;
        }
        .feature-item span {
            color: #495057;
            font-weight: 500;
        }
        .price-section {
            background: linear-gradient(135deg, var(--accent-color), #c0392b);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.3);
        }
        .price-label {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .price-amount {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .price-per {
            font-size: 1rem;
            opacity: 0.9;
        }
        .booking-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .btn-book {
            background: linear-gradient(135deg, var(--success-color), #229954);
            border: none;
            padding: 18px 50px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }
        .btn-book:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
            color: white;
        }
        .btn-login {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border: none;
            padding: 18px 50px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
            color: white;
        }
        .booking-note {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            color: #6c757d;
        }
        .booking-note i {
            color: var(--secondary-color);
            margin-right: 8px;
        }
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }

        @media (max-width: 768px) {
            .package-image, .image-placeholder {
                height: 300px;
            }
            .package-title {
                font-size: 1.5rem;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .price-amount {
                font-size: 2rem;
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
                    <li class="nav-item"><a class="nav-link active" href="packages.php">Packages</a></li>
                    <?php if(isLoggedIn()): ?>
                        <li class="nav-item"><a class="nav-link" href="my-bookings.php">My Bookings</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="breadcrumb-custom">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <span>/</span>
                <a href="packages.php">Packages</a>
                <span>/</span>
                <span style="color: white;"><?= htmlspecialchars($package['package_name']); ?></span>
            </div>
            <h1><?= htmlspecialchars($package['package_name']); ?></h1>
        </div>
    </div>

    <!-- Package Details -->
    <div class="container package-container">
        <?php displayAlert(); ?>

        <div class="row">
            <!-- Left Column - Image -->
            <div class="col-lg-7">
                <div class="image-section">
                    <?php if(!empty($package['image']) && file_exists('uploads/' . $package['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($package['image']); ?>" 
                             alt="<?= htmlspecialchars($package['package_name']); ?>" 
                             class="package-image">
                    <?php else: ?>
                        <div class="image-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="details-card">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        About This Package
                    </h2>
                    <div class="package-description">
                        <?= nl2br(htmlspecialchars($package['description'])); ?>
                    </div>

                    <?php if(!empty($package['features'])): ?>
                    <div class="features-section">
                        <h3 class="section-title">
                            <i class="fas fa-check-double"></i>
                            What's Included
                        </h3>
                        <div class="features-grid">
                            <?php 
                            $features = explode(',', $package['features']);
                            foreach($features as $feature): 
                            ?>
                                <div class="feature-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span><?= htmlspecialchars(trim($feature)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column - Info & Booking -->
            <div class="col-lg-5">
                <div class="details-card">
                    <h2 class="package-title">
                        <i class="fas fa-map-marked-alt"></i>
                        Package Details
                    </h2>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Destination</div>
                                <div class="info-value"><?= htmlspecialchars($package['destination']); ?></div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Duration</div>
                                <div class="info-value"><?= htmlspecialchars($package['duration']); ?></div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Max Capacity</div>
                                <div class="info-value"><?= htmlspecialchars($package['max_people']); ?> People</div>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Package Type</div>
                                <div class="info-value">Premium</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="price-section">
                    <div class="price-label">Starting From</div>
                    <div class="price-amount"><?= formatCurrency($package['price']); ?></div>
                    <div class="price-per">per person</div>
                </div>

                <div class="booking-section">
                    <?php if(isLoggedIn()): ?>
                        <a href="book-now.php?id=<?= $package['id']; ?>" class="btn-book">
                            <i class="fas fa-calendar-check"></i>
                            Book This Package
                        </a>
                        <div class="booking-note">
                            <i class="fas fa-shield-alt"></i> Secure booking process
                            <br>
                            <i class="fas fa-undo"></i> Free cancellation up to 24 hours
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i>
                            Login to Book
                        </a>
                        <div class="booking-note">
                            <i class="fas fa-info-circle"></i> Please login to make a booking
                            <br>
                            Don't have an account? <a href="register.php" style="color: var(--secondary-color); font-weight: bold;">Register here</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
</body>
</html>