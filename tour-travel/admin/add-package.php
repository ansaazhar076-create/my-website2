<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config.php";

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD']=="POST") {
    try {
        // Sanitize and validate inputs
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $desc = mysqli_real_escape_string($conn, $_POST['desc']);
        $dest = mysqli_real_escape_string($conn, $_POST['dest']);
        $duration = mysqli_real_escape_string($conn, $_POST['duration']);
        $price = floatval($_POST['price']);
        $max = intval($_POST['max']);
        $features = mysqli_real_escape_string($conn, $_POST['features']);
        
        // Validate required fields
        if(empty($name) || empty($desc) || empty($dest) || empty($duration) || $price <= 0 || $max <= 0) {
            $error = "Please fill all fields correctly!";
        } else {
            // Handle image upload
            $image = null;
            if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['image']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if(in_array($ext, $allowed)) {
                    $new_filename = uniqid() . '.' . $ext;
                    $upload_path = "../uploads/" . $new_filename;
                    
                    if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $image = $new_filename;
                    }
                }
            }
            
            // Insert into database
            $stmt = $conn->prepare("
                INSERT INTO tour_packages 
                (package_name, description, destination, duration, price, max_people, features, image, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            
            $stmt->bind_param("ssssdiis", $name, $desc, $dest, $duration, $price, $max, $features, $image);
            
            if($stmt->execute()) {
                $_SESSION['success'] = "Package added successfully!";
                header("Location: packages.php");
                exit;
            } else {
                $error = "Failed to add package: " . $stmt->error;
            }
            $stmt->close();
        }
    } catch(Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Package - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
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
        
        .back-btn {
            background: var(--secondary-color);
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
        
        .back-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            color: white;
        }
        
        /* Content Container */
        .content-container {
            padding: 0 30px 30px;
        }
        
        /* Form Card */
        .form-card {
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
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .form-header p {
            color: #6c757d;
            margin: 0;
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
        
        .form-label .required {
            color: var(--accent-color);
        }
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .file-upload {
            border: 2px dashed #e9ecef;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-upload:hover {
            border-color: var(--secondary-color);
            background: #f8f9fa;
        }
        
        .file-upload input[type="file"] {
            display: none;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .upload-text {
            color: #6c757d;
        }
        
        .image-preview {
            margin-top: 15px;
            max-width: 300px;
            border-radius: 8px;
            display: none;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--success-color), #229954);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 8px;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 auto;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 25px;
            border: none;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .form-card {
                padding: 25px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
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
            <a href="bookings.php" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Bookings</span>
            </a>
            <a href="users.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="add-package.php" class="menu-item active">
                <i class="fas fa-plus-circle"></i>
                <span>Add Package</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1><i class="fas fa-plus-circle"></i> Add New Package</h1>
            <a href="packages.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Packages
            </a>
        </div>

        <!-- Content -->
        <div class="content-container">
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $error; ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <div class="form-header">
                    <h2>Package Information</h2>
                    <p>Fill in the details to create a new tour package</p>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <!-- Package Name -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-tag"></i>
                            Package Name <span class="required">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" placeholder="Enter package name" required>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-align-left"></i>
                            Description <span class="required">*</span>
                        </label>
                        <textarea name="desc" class="form-control" placeholder="Enter detailed description of the package" required></textarea>
                    </div>

                    <!-- Destination and Duration -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Destination <span class="required">*</span>
                            </label>
                            <input type="text" name="dest" class="form-control" placeholder="e.g., Bali, Indonesia" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-clock"></i>
                                Duration <span class="required">*</span>
                            </label>
                            <input type="text" name="duration" class="form-control" placeholder="e.g., 7 Days / 6 Nights" required>
                        </div>
                    </div>

                    <!-- Price and Max People -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-dollar-sign"></i>
                                Price (USD) <span class="required">*</span>
                            </label>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-users"></i>
                                Maximum People <span class="required">*</span>
                            </label>
                            <input type="number" name="max" class="form-control" placeholder="e.g., 20" required>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-list"></i>
                            Features
                        </label>
                        <textarea name="features" class="form-control" placeholder="Enter features separated by commas (e.g., Hotel Accommodation, Breakfast & Dinner, Airport Transfer)"></textarea>
                        <small class="text-muted">Separate each feature with a comma</small>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-image"></i>
                            Package Image
                        </label>
                        <label for="image" class="file-upload">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="upload-text">
                                <strong>Click to upload</strong> or drag and drop<br>
                                <small>PNG, JPG, GIF up to 10MB</small>
                            </div>
                            <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                        </label>
                        <img id="imagePreview" class="image-preview" alt="Preview">
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center mt-4">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-check-circle"></i>
                            Add Package
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>