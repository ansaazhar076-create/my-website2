<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config.php";

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get all packages
$packages_query = "SELECT * FROM tour_packages ORDER BY created_at DESC";
$packages_result = mysqli_query($conn, $packages_query);

if(!$packages_result) {
    die("Error fetching packages: " . mysqli_error($conn));
}

// Handle delete action
if(isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $delete_query = "DELETE FROM tour_packages WHERE id = $id";
    if(mysqli_query($conn, $delete_query)) {
        header("Location: packages.php?success=deleted");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - TravelWorld Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --sidebar-width: 260px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1100;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .mobile-menu-toggle i {
            font-size: 1.2rem;
        }
        
        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, #1a252f 100%);
            color: white;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            font-size: 0.85rem;
            opacity: 0.7;
            margin: 0;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--secondary-color);
        }
        
        .menu-item i {
            width: 25px;
            font-size: 1.1rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        /* Top Bar */
        .top-bar {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .top-bar h1 {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 0;
        }
        
        .top-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .add-package-btn {
            background: linear-gradient(135deg, var(--success-color), #229954);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.2);
        }
        
        .add-package-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.3);
            color: white;
        }
        
        .logout-btn {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
            color: white;
        }
        
        /* Content Container */
        .content-container {
            padding: 0 30px 30px;
        }
        
        /* Alert Messages */
        .alert-custom {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 25px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-icon.total {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
        }
        
        .stat-icon.active {
            background: linear-gradient(135deg, var(--success-color), #229954);
        }
        
        .stat-icon.inactive {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
        }
        
        .stat-details h3 {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 0;
        }
        
        .stat-details p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Packages Table */
        .packages-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 0;
        }
        
        .search-box {
            position: relative;
            width: 300px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--secondary-color);
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .package-card {
            background: white;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .package-card:hover {
            border-color: var(--secondary-color);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .package-image {
            height: 180px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            position: relative;
        }
        
        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .package-body {
            padding: 20px;
        }
        
        .package-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .package-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 15px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .package-info span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .package-info i {
            color: var(--secondary-color);
            width: 20px;
        }
        
        .package-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--accent-color);
            margin-bottom: 15px;
        }
        
        .package-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }
        
        .btn-edit {
            background: var(--secondary-color);
            color: white;
        }
        
        .btn-edit:hover {
            background: #2980b9;
            transform: translateY(-2px);
            color: white;
        }
        
        .btn-delete {
            background: var(--accent-color);
            color: white;
        }
        
        .btn-delete:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .top-bar {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
                padding: 80px 20px 20px 20px;
            }
            
            .top-bar h1 {
                font-size: 1.5rem;
            }
            
            .top-actions {
                flex-direction: column;
            }
            
            .search-box {
                width: 100%;
            }
            
            .content-container {
                padding: 0 15px 20px;
            }
            
            .packages-section {
                padding: 20px;
            }
            
            .section-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .packages-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-plane"></i> TravelWorld</h3>
            <p>Admin Panel</p>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="packages.php" class="menu-item active">
                <i class="fas fa-box"></i>
                <span>Manage Packages</span>
            </a>
            <a href="bookings.php" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Bookings</span>
            </a>
            <a href="users.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="add-package.php" class="menu-item">
                <i class="fas fa-plus-circle"></i>
                <span>Add Package</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1><i class="fas fa-box"></i> Manage Packages</h1>
            <div class="top-actions">
                <a href="add-package.php" class="add-package-btn">
                    <i class="fas fa-plus"></i>
                    Add New Package
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="content-container">
            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-custom">
                <i class="fas fa-check-circle"></i>
                <?php 
                    if($_GET['success'] == 'deleted') echo 'Package deleted successfully!';
                    if($_GET['success'] == 'added') echo 'Package added successfully!';
                    if($_GET['success'] == 'updated') echo 'Package updated successfully!';
                ?>
            </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo mysqli_num_rows($packages_result); ?></h3>
                        <p>Total Packages</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon active">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3>
                            <?php 
                            mysqli_data_seek($packages_result, 0);
                            $active_count = 0;
                            while($p = mysqli_fetch_assoc($packages_result)) {
                                if($p['status'] == 'active') $active_count++;
                            }
                            echo $active_count;
                            mysqli_data_seek($packages_result, 0);
                            ?>
                        </h3>
                        <p>Active Packages</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon inactive">
                        <i class="fas fa-eye-slash"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo mysqli_num_rows($packages_result) - $active_count; ?></h3>
                        <p>Inactive Packages</p>
                    </div>
                </div>
            </div>

            <!-- Packages Section -->
            <div class="packages-section">
                <div class="section-header">
                    <h2 class="section-title">All Packages</h2>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search packages...">
                    </div>
                </div>

                <?php if(mysqli_num_rows($packages_result) > 0): ?>
                <div class="packages-grid" id="packagesGrid">
                    <?php while($package = mysqli_fetch_assoc($packages_result)): ?>
                    <div class="package-card" data-package-name="<?php echo htmlspecialchars($package['package_name']); ?>">
                        <div class="package-image">
                            <i class="fas fa-map-marked-alt"></i>
                            <span class="status-badge <?php echo $package['status']; ?>">
                                <?php echo ucfirst($package['status']); ?>
                            </span>
                        </div>
                        <div class="package-body">
                            <h3 class="package-title"><?php echo htmlspecialchars($package['package_name']); ?></h3>
                            
                            <div class="package-info">
                                <span>
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($package['destination']); ?>
                                </span>
                                <span>
                                    <i class="fas fa-clock"></i>
                                    <?php echo htmlspecialchars($package['duration']); ?>
                                </span>
                                <span>
                                    <i class="fas fa-users"></i>
                                    Max <?php echo $package['max_people']; ?> People
                                </span>
                            </div>
                            
                            <div class="package-price">
                                PKR <?php echo number_format($package['price'], 0); ?>
                            </div>
                            
                            <div class="package-actions">
                                <a href="edit-package.php?id=<?php echo $package['id']; ?>" class="action-btn btn-edit">
                                    <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <button onclick="deletePackage(<?php echo $package['id']; ?>)" class="action-btn btn-delete">
                                    <i class="fas fa-trash"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No Packages Found</h3>
                    <p>Start by adding your first tour package!</p>
                    <a href="add-package.php" class="add-package-btn" style="display: inline-flex; margin-top: 20px;">
                        <i class="fas fa-plus"></i>
                        Add New Package
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
        
        // Close sidebar when clicking menu items on mobile
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const packagesGrid = document.getElementById('packagesGrid');
        
        if(searchInput && packagesGrid) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const packageCards = packagesGrid.querySelectorAll('.package-card');
                
                packageCards.forEach(card => {
                    const packageName = card.getAttribute('data-package-name').toLowerCase();
                    if(packageName.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }

        // Delete confirmation
        function deletePackage(id) {
            if(confirm('Are you sure you want to delete this package? This action cannot be undone.')) {
                window.location.href = 'packages.php?delete=1&id=' + id;
            }
        }
    </script>
</body>
</html>