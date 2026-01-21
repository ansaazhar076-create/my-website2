<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config.php";

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get statistics with error handling
$users_query = mysqli_query($conn,"SELECT id FROM users");
$users = $users_query ? mysqli_num_rows($users_query) : 0;

$packages_query = mysqli_query($conn,"SELECT id FROM tour_packages");
$packages = $packages_query ? mysqli_num_rows($packages_query) : 0;

$bookings_query = mysqli_query($conn,"SELECT id FROM bookings");
$bookings = $bookings_query ? mysqli_num_rows($bookings_query) : 0;

// Get recent bookings with error handling
$recent_bookings_query = "
    SELECT b.*, 
           u.full_name as user_name,
           t.package_name 
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN tour_packages t ON b.package_id = t.id
    ORDER BY b.created_at DESC
    LIMIT 5
";
$recent_bookings = mysqli_query($conn, $recent_bookings_query);

if(!$recent_bookings) {
    // If query fails, create empty result
    $recent_bookings = mysqli_query($conn, "SELECT * FROM bookings WHERE 1=0");
}

// Get pending bookings count with error handling
$pending_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
$pending_count = 0;
if($pending_result) {
    $pending_row = mysqli_fetch_assoc($pending_result);
    $pending_count = $pending_row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TravelWorld</title>
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
        
        .admin-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
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
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            opacity: 0.1;
            transition: all 0.3s ease;
        }
        
        .stat-card.users::before {
            background: var(--secondary-color);
        }
        
        .stat-card.packages::before {
            background: var(--success-color);
        }
        
        .stat-card.bookings::before {
            background: var(--warning-color);
        }
        
        .stat-card.pending::before {
            background: var(--accent-color);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 15px;
        }
        
        .stat-card.users .stat-icon {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
        }
        
        .stat-card.packages .stat-icon {
            background: linear-gradient(135deg, var(--success-color), #229954);
        }
        
        .stat-card.bookings .stat-icon {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
        }
        
        .stat-card.pending .stat-icon {
            background: linear-gradient(135deg, var(--accent-color), #c0392b);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 2.2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        /* Quick Actions */
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.3rem;
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
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .action-btn {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
            color: white;
        }
        
        .action-btn i {
            font-size: 1.3rem;
        }
        
        /* Recent Bookings */
        .recent-bookings {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        
        .bookings-table {
            width: 100%;
            margin-top: 20px;
            min-width: 600px;
        }
        
        .bookings-table thead {
            background: #f8f9fa;
        }
        
        .bookings-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--primary-color);
            border-bottom: 2px solid #e9ecef;
        }
        
        .bookings-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #495057;
        }
        
        .bookings-table tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
            white-space: nowrap;
        }
        
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-badge.confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        /* Mobile Styles */
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
                align-items: flex-start;
                padding: 80px 20px 20px 20px;
            }
            
            .top-bar h1 {
                font-size: 1.5rem;
            }
            
            .admin-profile {
                width: 100%;
                justify-content: space-between;
            }
            
            .content-container {
                padding: 0 15px 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-value {
                font-size: 1.8rem;
            }
            
            .quick-actions,
            .recent-bookings {
                padding: 20px;
            }
            
            .section-title {
                font-size: 1.1rem;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .bookings-table {
                font-size: 0.85rem;
            }
            
            .bookings-table th,
            .bookings-table td {
                padding: 10px 8px;
            }
            
            .logout-btn {
                padding: 8px 16px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 480px) {
            .top-bar h1 {
                font-size: 1.3rem;
            }
            
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
            
            .stat-value {
                font-size: 1.6rem;
            }
            
            .action-btn {
                padding: 12px 16px;
                font-size: 0.9rem;
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
            <a href="dashboard.php" class="menu-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="packages.php" class="menu-item">
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
            <h1>Dashboard Overview</h1>
            <div class="admin-profile">
                <div class="admin-avatar">A</div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="content-container">
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card users">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value"><?= number_format($users); ?></div>
                </div>

                <div class="stat-card packages">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-label">Tour Packages</div>
                    <div class="stat-value"><?= number_format($packages); ?></div>
                </div>

                <div class="stat-card bookings">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-label">Total Bookings</div>
                    <div class="stat-value"><?= number_format($bookings); ?></div>
                </div>

                <div class="stat-card pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-label">Pending Bookings</div>
                    <div class="stat-value"><?= number_format($pending_count); ?></div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2 class="section-title">
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h2>
                <div class="action-buttons">
                    <a href="add-package.php" class="action-btn">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add New Package</span>
                    </a>
                    <a href="packages.php" class="action-btn" style="background: linear-gradient(135deg, var(--success-color), #229954);">
                        <i class="fas fa-box"></i>
                        <span>Manage Packages</span>
                    </a>
                    <a href="bookings.php" class="action-btn" style="background: linear-gradient(135deg, var(--warning-color), #e67e22);">
                        <i class="fas fa-calendar-check"></i>
                        <span>View Bookings</span>
                    </a>
                    <a href="users.php" class="action-btn" style="background: linear-gradient(135deg, var(--accent-color), #c0392b);">
                        <i class="fas fa-users"></i>
                        <span>Manage Users</span>
                    </a>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="recent-bookings">
                <h2 class="section-title">
                    <i class="fas fa-history"></i>
                    Recent Bookings
                </h2>
                
                <?php if(mysqli_num_rows($recent_bookings) > 0): ?>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Date</th>
                            <th>People</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                        <tr>
                            <td><strong>#<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><?= htmlspecialchars($booking['user_name']); ?></td>
                            <td><?= htmlspecialchars($booking['package_name']); ?></td>
                            <td><?= date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                            <td><?= $booking['number_of_people']; ?> people</td>
                            <td>
                                <span class="status-badge <?= strtolower($booking['status']); ?>">
                                    <?= ucfirst($booking['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No recent bookings found</p>
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
    </script>
</body>
</html>