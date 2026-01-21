<?php
session_start();
require_once 'functions.php';
$packages = getActivePackages();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Packages - TravelWorld</title>
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
            padding: 80px 0;
            text-align: center;
            margin-bottom: 50px;
        }
        .hero-section h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .hero-section p {
            font-size: 1.3rem;
            opacity: 0.95;
            margin-bottom: 30px;
        }
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            display: block;
        }
        .stat-label {
            font-size: 0.95rem;
            opacity: 0.9;
        }
        .packages-container {
            max-width: 1400px;
            margin: 0 auto;
            padding-bottom: 50px;
        }
        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }
        .section-header h2 {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        .section-header p {
            font-size: 1.2rem;
            color: #6c757d;
        }
        .package-card {
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
            border-radius: 15px;
            overflow: hidden;
            background: white;
        }
        .package-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        .package-image-wrapper {
            position: relative;
            height: 280px;
            overflow: hidden;
        }
        .package-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .package-card:hover .package-image {
            transform: scale(1.1);
        }
        .image-placeholder {
            width: 100%;
            height: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4rem;
        }
        .package-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--accent-color);
            color: white;
            padding: 8px 18px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .package-body {
            padding: 25px;
        }
        .package-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 12px;
            min-height: 60px;
            display: flex;
            align-items: center;
        }
        .package-location {
            color: #6c757d;
            margin-bottom: 15px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .package-location i {
            color: var(--accent-color);
        }
        .package-description {
            color: #495057;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 20px;
            min-height: 80px;
        }
        .package-info {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-top: 2px solid #f8f9fa;
            border-bottom: 2px solid #f8f9fa;
            margin-bottom: 20px;
        }
        .info-item {
            text-align: center;
            flex: 1;
        }
        .info-item i {
            color: var(--secondary-color);
            font-size: 1.2rem;
            display: block;
            margin-bottom: 5px;
        }
        .info-label {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 0.9rem;
            color: var(--primary-color);
            font-weight: 600;
            margin-top: 3px;
        }
        .features-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
            min-height: 70px;
        }
        .feature-badge {
            background: #f8f9fa;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #495057;
            border: 1px solid #e9ecef;
        }
        .package-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .price-tag {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--accent-color);
        }
        .price-label {
            font-size: 0.85rem;
            color: #6c757d;
            display: block;
        }
        .btn-view {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border: none;
            padding: 12px 28px;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 50px;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
            color: white;
        }
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .empty-state-icon {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 25px;
        }
        .empty-state h3 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 15px;
        }
        .empty-state p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }

        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }
            .hero-section p {
                font-size: 1.1rem;
            }
            .section-header h2 {
                font-size: 2rem;
            }
            .package-title {
                font-size: 1.2rem;
                min-height: auto;
            }
            .package-description {
                min-height: auto;
            }
            .features-badges {
                min-height: auto;
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
            <h1><i class="fas fa-globe-americas"></i> Explore Our Tour Packages</h1>
            <p>Discover amazing destinations and create unforgettable memories</p>
            
            <?php
            $total_packages = 0;
            $packages_temp = getActivePackages();
            while($packages_temp->fetch_assoc()) $total_packages++;
            ?>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= $total_packages; ?>+</span>
                    <span class="stat-label">Tour Packages</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">50+</span>
                    <span class="stat-label">Destinations</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">10K+</span>
                    <span class="stat-label">Happy Travelers</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Packages Content -->
    <div class="container packages-container">
        <?php displayAlert(); ?>

        <div class="section-header">
            <h2>Available Tour Packages</h2>
            <p>Choose from our carefully curated selection of amazing travel experiences</p>
        </div>

        <?php 
        $packages_array = [];
        while($p = $packages->fetch_assoc()) {
            $packages_array[] = $p;
        }
        ?>

        <?php if(count($packages_array) > 0): ?>
            <div class="row">
                <?php foreach($packages_array as $package): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="package-card">
                        <div class="package-image-wrapper">
                            <?php if(!empty($package['image']) && file_exists('uploads/' . $package['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($package['image']); ?>" 
                                     alt="<?= htmlspecialchars($package['package_name']); ?>" 
                                     class="package-image">
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                            <?php endif; ?>
                            <div class="package-badge">
                                <i class="fas fa-star"></i> Featured
                            </div>
                        </div>
                        
                        <div class="package-body">
                            <h5 class="package-title">
                                <?= htmlspecialchars($package['package_name']); ?>
                            </h5>
                            
                            <p class="package-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= htmlspecialchars($package['destination']); ?>
                            </p>
                            
                            <p class="package-description">
                                <?= htmlspecialchars(substr($package['description'], 0, 120)); ?>...
                            </p>
                            
                            <div class="package-info">
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <div class="info-label">Duration</div>
                                    <div class="info-value"><?= htmlspecialchars($package['duration']); ?></div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-users"></i>
                                    <div class="info-label">Max People</div>
                                    <div class="info-value"><?= htmlspecialchars($package['max_people']); ?></div>
                                </div>
                            </div>
                            
                            <?php if(!empty($package['features'])): ?>
                            <div class="features-badges">
                                <?php 
                                $features = explode(',', $package['features']);
                                foreach(array_slice($features, 0, 3) as $feature): 
                                ?>
                                    <span class="feature-badge">
                                        <i class="fas fa-check"></i> <?= htmlspecialchars(trim($feature)); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="package-footer">
                                <div>
                                    <span class="price-label">Starting from</span>
                                    <div class="price-tag"><?= formatCurrency($package['price']); ?></div>
                                </div>
                                <a href="package-details.php?id=<?= $package['id']; ?>" class="btn-view">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-suitcase-rolling"></i>
                </div>
                <h3>No Packages Available</h3>
                <p>Check back soon for exciting new travel packages!</p>
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