<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config.php";

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get package ID
if(!isset($_GET['id'])) {
    header("Location: packages.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch package data
$query = "SELECT * FROM tour_packages WHERE id='$id'";
$result = mysqli_query($conn, $query);

if(!$result || mysqli_num_rows($result) == 0) {
    header("Location: packages.php?error=notfound");
    exit;
}

$data = mysqli_fetch_assoc($result);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['desc']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $duration = mysqli_real_escape_string($conn, $_POST['duration']);
    $max_people = intval($_POST['max_people']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "
        UPDATE tour_packages SET
        package_name='$name',
        description='$description',
        destination='$destination',
        price='$price',
        duration='$duration',
        max_people='$max_people',
        status='$status'
        WHERE id='$id'
    ";
    
    if(mysqli_query($conn, $update_query)) {
        header("Location: packages.php?success=updated");
        exit;
    } else {
        $error = "Error updating package: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Package - TravelWorld Admin</title>
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
        
        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .back-btn {
            background: #e9ecef;
            color: var(--primary-color);
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: #dee2e6;
            transform: translateX(-3px);
            color: var(--primary-color);
        }
        
        .top-bar h1 {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 0;
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
        
        /* Form Container */
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            max-width: 900px;
            margin: 0 auto;
        }
        
        .form-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-header h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: #6c757d;
            margin: 0;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: var(--secondary-color);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .form-label .required {
            color: var(--accent-color);
            margin-left: 3px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .input-group .form-control {
            padding-left: 45px;
        }
        
        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }
        
        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--success-color), #229954);
            color: white;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(39, 174, 96, 0.4);
        }
        
        .btn-secondary {
            background: #e9ecef;
            color: var(--primary-color);
        }
        
        .btn-secondary:hover {
            background: #dee2e6;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert i {
            font-size: 1.2rem;
        }
        
        .char-counter {
            text-align: right;
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
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
            
            .top-bar-left {
                flex-direction: column;
                align-items: stretch;
            }
            
            .top-bar h1 {
                font-size: 1.4rem;
            }
            
            .content-container {
                padding: 0 15px 20px;
            }
            
            .form-container {
                padding: 25px 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column-reverse;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
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
            <div class="top-bar-left">
                <a href="packages.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Packages
                </a>
                <h1><i class="fas fa-edit"></i> Edit Package</h1>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>

        <!-- Content -->
        <div class="content-container">
            <div class="form-container">
                <div class="form-header">
                    <h2>Update Package Details</h2>
                    <p>Modify the information for "<?php echo htmlspecialchars($data['package_name']); ?>"</p>
                </div>

                <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" id="editForm">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Basic Information
                        </h3>

                        <div class="form-group">
                            <label class="form-label">
                                Package Name<span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <i class="fas fa-tag input-icon"></i>
                                <input 
                                    type="text" 
                                    name="name" 
                                    class="form-control" 
                                    value="<?php echo htmlspecialchars($data['package_name']); ?>"
                                    required
                                    maxlength="100"
                                    id="packageName"
                                >
                            </div>
                            <div class="char-counter">
                                <span id="nameCounter">0</span>/100 characters
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Destination<span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <i class="fas fa-map-marker-alt input-icon"></i>
                                <input 
                                    type="text" 
                                    name="destination" 
                                    class="form-control" 
                                    value="<?php echo htmlspecialchars($data['destination']); ?>"
                                    required
                                    placeholder="e.g., Maldives, Paris, Dubai"
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Description<span class="required">*</span>
                            </label>
                            <textarea 
                                name="desc" 
                                class="form-control" 
                                required
                                maxlength="1000"
                                id="packageDesc"
                                placeholder="Provide a detailed description of the package..."
                            ><?php echo htmlspecialchars($data['description']); ?></textarea>
                            <div class="char-counter">
                                <span id="descCounter">0</span>/1000 characters
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Details -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-money-bill-wave"></i>
                            Pricing & Details
                        </h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    Price (PKR)<span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-dollar-sign input-icon"></i>
                                    <input 
                                        type="number" 
                                        name="price" 
                                        class="form-control" 
                                        value="<?php echo $data['price']; ?>"
                                        required
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    Duration<span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-clock input-icon"></i>
                                    <input 
                                        type="text" 
                                        name="duration" 
                                        class="form-control" 
                                        value="<?php echo htmlspecialchars($data['duration']); ?>"
                                        required
                                        placeholder="e.g., 5 Days 4 Nights"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    Max People<span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-users input-icon"></i>
                                    <input 
                                        type="number" 
                                        name="max_people" 
                                        class="form-control" 
                                        value="<?php echo $data['max_people']; ?>"
                                        required
                                        min="1"
                                        placeholder="e.g., 10"
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    Status<span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-toggle-on input-icon"></i>
                                    <select name="status" class="form-control" required>
                                        <option value="active" <?php echo $data['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $data['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="packages.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Package
                        </button>
                    </div>
                </form>
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

        // Character counters
        const packageName = document.getElementById('packageName');
        const nameCounter = document.getElementById('nameCounter');
        const packageDesc = document.getElementById('packageDesc');
        const descCounter = document.getElementById('descCounter');

        function updateCounter(input, counter) {
            counter.textContent = input.value.length;
        }

        if(packageName && nameCounter) {
            updateCounter(packageName, nameCounter);
            packageName.addEventListener('input', () => updateCounter(packageName, nameCounter));
        }

        if(packageDesc && descCounter) {
            updateCounter(packageDesc, descCounter);
            packageDesc.addEventListener('input', () => updateCounter(packageDesc, descCounter));
        }

        // Form validation
        const editForm = document.getElementById('editForm');
        editForm.addEventListener('submit', function(e) {
            const price = parseFloat(document.querySelector('input[name="price"]').value);
            const maxPeople = parseInt(document.querySelector('input[name="max_people"]').value);

            if(price <= 0) {
                e.preventDefault();
                alert('Price must be greater than 0');
                return false;
            }

            if(maxPeople <= 0) {
                e.preventDefault();
                alert('Max people must be at least 1');
                return false;
            }
        });
    </script>
</body>
</html>