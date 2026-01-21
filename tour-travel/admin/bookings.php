<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle status update
if(isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    
    if($stmt->execute()) {
        $_SESSION['success'] = 'Booking status updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update status!';
    }
    $stmt->close();
    
    header('Location: bookings.php');
    exit();
}

// Get all bookings with user and package info
$sql = "SELECT b.*, u.full_name, u.email, u.phone, p.package_name, p.destination 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN tour_packages p ON b.package_id = p.id 
        ORDER BY b.created_at DESC";
$bookings = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Panel</title>
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
        
        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
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
            margin-bottom: 30px;
        }
        
        .top-bar h1 {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 0;
        }
        
        /* Content Container */
        .content-container {
            padding: 0 30px 30px;
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
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
        
        .stat-icon.pending { background: var(--warning-color); }
        .stat-icon.confirmed { background: var(--success-color); }
        .stat-icon.cancelled { background: var(--accent-color); }
        
        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 0;
        }
        
        .stat-info p {
            font-size: 0.9rem;
            color: #6c757d;
            margin: 0;
        }
        
        /* Table Card */
        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .table-header {
            padding: 20px 25px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .table-header h3 {
            font-size: 1.3rem;
            color: var(--primary-color);
            margin: 0;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .bookings-table {
            width: 100%;
            margin: 0;
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
            white-space: nowrap;
        }
        
        .bookings-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #495057;
        }
        
        .bookings-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
            display: inline-block;
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
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 2px;
        }
        
        .action-btn.view {
            background: var(--secondary-color);
            color: white;
        }
        
        .action-btn.edit {
            background: var(--success-color);
            color: white;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        /* Modal Styling */
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--primary-color);
            width: 180px;
            flex-shrink: 0;
        }
        
        .info-value {
            color: #495057;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content-container {
                padding: 0 15px 15px;
            }
            
            .top-bar {
                padding: 15px;
                margin-left: 60px;
            }
            
            .top-bar h1 {
                font-size: 1.3rem;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
            }
            
            .bookings-table {
                font-size: 0.85rem;
            }
            
            .bookings-table th,
            .bookings-table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

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
            <a href="packages.php" class="menu-item">
                <i class="fas fa-box"></i>
                <span>Manage Packages</span>
            </a>
            <a href="bookings.php" class="menu-item active">
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
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1><i class="fas fa-calendar-check"></i> Manage Bookings</h1>
        </div>

        <!-- Content -->
        <div class="content-container">
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php
            // Get stats
            $total = mysqli_num_rows($bookings);
            mysqli_data_seek($bookings, 0); // Reset pointer
            
            $pending = 0;
            $confirmed = 0;
            $cancelled = 0;
            
            $temp_bookings = [];
            while($b = mysqli_fetch_assoc($bookings)) {
                $temp_bookings[] = $b;
                if($b['status'] == 'pending') $pending++;
                if($b['status'] == 'confirmed') $confirmed++;
                if($b['status'] == 'cancelled') $cancelled++;
            }
            ?>

            <!-- Stats Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $pending; ?></h3>
                        <p>Pending</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon confirmed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $confirmed; ?></h3>
                        <p>Confirmed</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon cancelled">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $cancelled; ?></h3>
                        <p>Cancelled</p>
                    </div>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="table-card">
                <div class="table-header">
                    <h3>All Bookings (<?= $total; ?>)</h3>
                </div>
                <div class="table-responsive">
                    <table class="bookings-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Package</th>
                                <th>Travel Date</th>
                                <th>People</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($temp_bookings as $booking): ?>
                            <tr>
                                <td><strong>#<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($booking['full_name']); ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($booking['email']); ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($booking['package_name']); ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($booking['destination']); ?></small>
                                </td>
                                <td><?= date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                <td><?= $booking['number_of_people']; ?></td>
                                <td><strong>$<?= number_format($booking['total_amount'], 2); ?></strong></td>
                                <td>
                                    <span class="status-badge <?= strtolower($booking['status']); ?>">
                                        <?= ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn view" data-bs-toggle="modal" 
                                            data-bs-target="#viewModal<?= $booking['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn edit" data-bs-toggle="modal" 
                                            data-bs-target="#statusModal<?= $booking['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- View Modal -->
                            <div class="modal fade" id="viewModal<?= $booking['id']; ?>">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-info-circle"></i> Booking Details
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="info-row">
                                                <div class="info-label">Booking ID:</div>
                                                <div class="info-value">#<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Customer Name:</div>
                                                <div class="info-value"><?= htmlspecialchars($booking['full_name']); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Email:</div>
                                                <div class="info-value"><?= htmlspecialchars($booking['email']); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Phone:</div>
                                                <div class="info-value"><?= htmlspecialchars($booking['phone']); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Package:</div>
                                                <div class="info-value"><?= htmlspecialchars($booking['package_name']); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Destination:</div>
                                                <div class="info-value"><?= htmlspecialchars($booking['destination']); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Travel Date:</div>
                                                <div class="info-value"><?= date('F d, Y', strtotime($booking['booking_date'])); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Number of People:</div>
                                                <div class="info-value"><?= $booking['number_of_people']; ?> people</div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Total Amount:</div>
                                                <div class="info-value"><strong>$<?= number_format($booking['total_amount'], 2); ?></strong></div>
                                            </div>
                                            <?php if($booking['special_requests']): ?>
                                            <div class="info-row">
                                                <div class="info-label">Special Requests:</div>
                                                <div class="info-value"><?= nl2br(htmlspecialchars($booking['special_requests'])); ?></div>
                                            </div>
                                            <?php endif; ?>
                                            <div class="info-row">
                                                <div class="info-label">Booking Date:</div>
                                                <div class="info-value"><?= date('F d, Y H:i', strtotime($booking['created_at'])); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Status:</div>
                                                <div class="info-value">
                                                    <span class="status-badge <?= strtolower($booking['status']); ?>">
                                                        <?= ucfirst($booking['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Modal -->
                            <div class="modal fade" id="statusModal<?= $booking['id']; ?>">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-edit"></i> Update Status
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Booking Status</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="pending" <?= $booking['status']=='pending'?'selected':''; ?>>Pending</option>
                                                        <option value="confirmed" <?= $booking['status']=='confirmed'?'selected':''; ?>>Confirmed</option>
                                                        <option value="cancelled" <?= $booking['status']=='cancelled'?'selected':''; ?>>Cancelled</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" name="update_status" class="btn btn-primary">
                                                    <i class="fas fa-save"></i> Update Status
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html>