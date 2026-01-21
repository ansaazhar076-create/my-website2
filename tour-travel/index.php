<?php
require_once 'functions.php';
$packages = getActivePackages();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour & Travel Management System</title>
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
        }
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .hero-section p {
            font-size: 1.3rem;
            margin-bottom: 30px;
        }
        .navbar {
            background-color: var(--primary-color) !important;
            padding: 15px 0;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .package-card {
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        .package-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .package-image {
            height: 250px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        .price-badge {
            background: var(--accent-color);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 1.3rem;
            font-weight: bold;
            display: inline-block;
            margin: 15px 0;
        }
        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
            padding: 12px 30px;
            font-size: 1rem;
            border-radius: 50px;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .features-badge {
            background: #f8f9fa;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 3px;
            display: inline-block;
        }
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
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
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="packages.php">Packages</a></li>
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
            <h1>Explore The World With Us</h1>
            <p>Discover amazing destinations and create unforgettable memories</p>
            <a href="packages.php" class="btn btn-light btn-lg">
                <i class="fas fa-compass"></i> View All Packages
            </a>
        </div>
    </div>

    <!-- Featured Packages -->
    <div class="container mt-5">
        <?php displayAlert(); ?>
        
        <h2 class="text-center mb-4">Featured Tour Packages</h2>
        <div class="row">
            <?php while($package = $packages->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card package-card">
                    <div class="package-image">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($package['package_name']); ?></h5>
                        <p class="text-muted">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($package['destination']); ?>
                        </p>
                        <p class="card-text"><?php echo substr(htmlspecialchars($package['description']), 0, 100); ?>...</p>
                        
                        <div class="mb-3">
                            <i class="fas fa-clock text-primary"></i> <?php echo htmlspecialchars($package['duration']); ?>
                            <br>
                            <i class="fas fa-users text-primary"></i> Max <?php echo $package['max_people']; ?> People
                        </div>

                        <?php 
                        $features = explode(',', $package['features']);
                        foreach(array_slice($features, 0, 3) as $feature): 
                        ?>
                            <span class="features-badge"><?php echo htmlspecialchars(trim($feature)); ?></span>
                        <?php endforeach; ?>

                        <div class="text-center">
                            <div class="price-badge"><?php echo formatCurrency($package['price']); ?></div>
                            <br>
                            <a href="package-details.php?id=<?php echo $package['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
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