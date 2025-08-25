<?php
ini_set('display_errors',0);
include("connection.php");
session_start();

if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to the login page
    header("Location: index.php");
    exit(); // Ensure no further code is executed
}

$employee_username = $_SESSION['username'];
$logindesignation = $_SESSION['designation'];
$loginemployee_id=$_SESSION['id'];
$isAdmin= $_SESSION['isAdmin'];

function getEmployeeDetails($employee_username) {
    global $con; // Assuming $con is your database connection

    // Prepare the SQL query to prevent SQL injection
    $stmt = $con->prepare("SELECT * FROM employees WHERE username = ?");
    $stmt->bind_param("s", $employee_username); // Bind the parameter

    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the employee details
    $employeeDetails = $result->fetch_assoc();

    // Close the statement and connection
    $stmt->close();

    return $employeeDetails;
}

// Example usage
$employeeDetails = getEmployeeDetails($employee_username);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --accent-color: #ec4899;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --info-color: #06b6d4;
            --light-color: #f8fafc;
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.3);
            --text-primary: #374151;
            --text-secondary: #6b7280;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Glass Background Pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 119, 198, 0.15) 0%, transparent 50%);
            z-index: -1;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 20px;
            left: 20px;
            height: calc(100vh - 40px);
            width: var(--sidebar-width);
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            transition: var(--transition);
            z-index: 1000;
            overflow: hidden;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            text-align: center;
            position: relative;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            max-width: 80px;
            height: auto;
            margin-bottom: 0.75rem;
            transition: var(--transition);
            filter: brightness(1.2) drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .sidebar.collapsed .sidebar-logo {
            max-width: 35px;
        }

        .sidebar-title {
            color: var(--text-primary);
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            transition: var(--transition);
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
        }

        .sidebar.collapsed .sidebar-title {
            display: none;
        }

        .sidebar-toggle {
            position: absolute;
            top: 1.5rem;
            right: -15px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary-color);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            box-shadow: 
                0 4px 12px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 1);
            transform: scale(1.1);
            box-shadow: 
                0 6px 20px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 1);
        }

        /* Navigation Styles */
        .sidebar-nav {
            padding: 1rem 0;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            color: var(--text-secondary);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 1.5rem;
            margin-bottom: 0.75rem;
            transition: var(--transition);
            opacity: 0.8;
        }

        .sidebar.collapsed .nav-section-title {
            display: none;
        }

        .nav-item {
            margin-bottom: 0.5rem;
            padding: 0 1rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            color: var(--text-primary);
            text-decoration: none;
            transition: var(--transition);
            position: relative;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            font-weight: 500;
        }

        .nav-link:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 
                0 4px 12px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .nav-link.active {
            color: var(--primary-color);
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(255, 255, 255, 0.8);
            box-shadow: 
                0 4px 16px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 1);
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: -1px;
            top: 50%;
            transform: translateY(-50%);
            height: 70%;
            width: 3px;
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
            border-radius: 0 3px 3px 0;
        }

        .nav-icon {
            font-size: 1.2rem;
            width: 20px;
            margin-right: 1rem;
            text-align: center;
            transition: var(--transition);
        }

        .nav-text {
            transition: var(--transition);
            white-space: nowrap;
        }

        .sidebar.collapsed .nav-text {
            display: none;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 0.75rem;
        }

        .sidebar.collapsed .nav-icon {
            margin-right: 0;
        }

        /* Dropdown Menu */
        .nav-dropdown {
            position: relative;
        }

        .dropdown-toggle::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-left: auto;
            transition: var(--transition);
        }

        .dropdown-toggle.collapsed::after {
            transform: rotate(-90deg);
        }

        .dropdown-menu-custom {
            background: rgba(0, 0, 0, 0.2);
            border: none;
            border-radius: 0;
            box-shadow: none;
            margin: 0;
            padding: 0;
        }

        .dropdown-item-custom {
            padding: 0.5rem 1.5rem 0.5rem 3.5rem;
            color: rgba(255, 255, 255, 0.7);
            transition: var(--transition);
        }

        .dropdown-item-custom:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        /* Main Content */
        .main-content {
            margin-left: calc(var(--sidebar-width) + 40px);
            margin-top: 20px;
            margin-right: 20px;
            margin-bottom: 20px;
            transition: var(--transition);
            min-height: calc(100vh - 40px);
        }

        .main-content.expanded {
            margin-left: calc(var(--sidebar-collapsed-width) + 40px);
        }

        /* Top Header */
        .top-header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        .header-title {
            color: var(--text-primary);
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            box-shadow: 
                0 4px 12px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .user-details h6 {
            margin: 0;
            color: var(--text-primary);
            font-weight: 600;
        }

        .user-details small {
            color: var(--text-secondary);
        }

        /* Content Area */
        .content-area {
            padding: 0;
        }

        .welcome-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
        }

        .welcome-card h2 {
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--text-primary);
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
        }

        .welcome-card p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 
                0 16px 48px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            margin-bottom: 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 4px 12px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        .stat-icon.companies { 
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        .stat-icon.candidates { 
            background: linear-gradient(135deg, var(--success-color), #34d399);
        }
        .stat-icon.employees { 
            background: linear-gradient(135deg, var(--warning-color), #fbbf24);
        }
        .stat-icon.appointments { 
            background: linear-gradient(135deg, var(--accent-color), #f472b6);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 500;
            margin: 0;
        }

        /* Card Content Styling */
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        .card-header {
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px 16px 0 0 !important;
        }

        .btn {
            border-radius: 10px;
            font-weight: 500;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-color: transparent;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #34d399);
            border-color: transparent;
        }

        .btn-info {
            background: linear-gradient(135deg, var(--info-color), #67e8f9);
            border-color: transparent;
        }

        .btn-outline-primary {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                left: 10px;
                top: 10px;
                height: calc(100vh - 20px);
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin: 10px;
            }
            
            .mobile-toggle {
                position: fixed;
                top: 1.5rem;
                left: 1.5rem;
                z-index: 1001;
                background: var(--glass-bg);
                backdrop-filter: blur(20px);
                color: var(--primary-color);
                border: 1px solid var(--glass-border);
                width: 50px;
                height: 50px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 
                    0 8px 32px rgba(0, 0, 0, 0.1),
                    inset 0 1px 0 rgba(255, 255, 255, 0.5);
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(5px);
                z-index: 999;
                display: none;
            }
            
            .sidebar-overlay.show {
                display: block;
            }

            .welcome-card {
                padding: 2rem 1.5rem;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1rem;
            }
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Additional Glass Effects */
        .text-muted {
            color: var(--text-secondary) !important;
        }

        .border-0 {
            border: 1px solid var(--glass-border) !important;
        }

        .shadow-sm {
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.5) !important;
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .stat-card:nth-child(1) { animation: float 6s ease-in-out infinite; }
        .stat-card:nth-child(2) { animation: float 6s ease-in-out infinite 0.5s; }
        .stat-card:nth-child(3) { animation: float 6s ease-in-out infinite 1s; }
        .stat-card:nth-child(4) { animation: float 6s ease-in-out infinite 1.5s; }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle d-md-none" id="mobileToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="uploads/Mpk-logo.webp" alt="MPK Logo" class="sidebar-logo">
            <h5 class="sidebar-title">MPK Admin</h5>
            <button class="sidebar-toggle d-none d-md-block" id="sidebarToggle">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <!-- Main Navigation -->
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <div class="nav-item">
                    <a href="header.php" class="nav-link active">
                        <i class="fas fa-home nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- Company Management -->
            <div class="nav-section">
                <div class="nav-section-title">Company Management</div>
                <?php if ($isAdmin == '1') { ?>
                <div class="nav-item">
                    <a href="addCompany.php" class="nav-link">
                        <i class="fas fa-plus-circle nav-icon"></i>
                        <span class="nav-text">Add Company</span>
                    </a>
                </div>
                <?php } ?>
                <div class="nav-item">
                    <a href="allCompany.php" class="nav-link">
                        <i class="fas fa-building nav-icon"></i>
                        <span class="nav-text">All Companies</span>
                    </a>
                </div>
            </div>

            <!-- Candidate Management -->
            <div class="nav-section">
                <div class="nav-section-title">Candidate Management</div>
                <?php if ($isAdmin == '1') { ?>
                <div class="nav-item">
                    <a href="addCandidate.php" class="nav-link">
                        <i class="fas fa-user-plus nav-icon"></i>
                        <span class="nav-text">Add Candidate</span>
                    </a>
                </div>
                <?php } ?>
                <div class="nav-item">
                    <a href="allCandidate.php" class="nav-link">
                        <i class="fas fa-users nav-icon"></i>
                        <span class="nav-text">All Candidates</span>
                    </a>
                </div>
            </div>

            <?php if ($isAdmin == '1') { ?>
            <!-- Employee Management -->
            <div class="nav-section">
                <div class="nav-section-title">Employee Management</div>
                <div class="nav-item">
                    <a href="addemployee.php" class="nav-link">
                        <i class="fas fa-user-tie nav-icon"></i>
                        <span class="nav-text">Add Employee</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="allEmployee.php" class="nav-link">
                        <i class="fas fa-id-badge nav-icon"></i>
                        <span class="nav-text">All Employees</span>
                    </a>
                </div>
            </div>

            <!-- Operations -->
            <div class="nav-section">
                <div class="nav-section-title">Operations</div>
                <div class="nav-item">
                    <a href="allAppointment.php" class="nav-link">
                        <i class="fas fa-calendar-check nav-icon"></i>
                        <span class="nav-text">Appointments</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="hiring.php" class="nav-link">
                        <i class="fas fa-handshake nav-icon"></i>
                        <span class="nav-text">Hiring</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="dailyFollowup.php" class="nav-link">
                        <i class="fas fa-clipboard-check nav-icon"></i>
                        <span class="nav-text">Daily Follow-up</span>
                    </a>
                </div>
            </div>
            <?php } ?>

            <!-- Account -->
            <div class="nav-section">
                <div class="nav-section-title">Account</div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-user-circle nav-icon"></i>
                        <span class="nav-text">Profile</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-cog nav-icon"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt nav-icon"></i>
                        <span class="nav-text">Logout</span>
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Header -->
        <header class="top-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="header-title">Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($employeeDetails['employee_name'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <h6><?php echo $employeeDetails['employee_name']; ?></h6>
                        <small><?php echo $logindesignation; ?></small>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Welcome Card -->
            <div class="welcome-card">
                <h2>Welcome back, <?php echo $employeeDetails['employee_name']; ?>!</h2>
                <p>Here's what's happening with your business today.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon companies">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-number">125</div>
                    <p class="stat-label">Total Companies</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon candidates">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number">342</div>
                    <p class="stat-label">Active Candidates</p>
                </div>
                <?php if ($isAdmin == '1') { ?>
                <div class="stat-card">
                    <div class="stat-icon employees">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-number">28</div>
                    <p class="stat-label">Employees</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon appointments">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-number">45</div>
                    <p class="stat-label">Appointments</p>
                </div>
                <?php } ?>
            </div>

            <!-- Main Dashboard Content -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">Recent Activities</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Your main dashboard content will be displayed here.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if ($isAdmin == '1') { ?>
                                <a href="addCompany.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Company
                                </a>
                                <a href="addCandidate.php" class="btn btn-success">
                                    <i class="fas fa-user-plus me-2"></i>Add Candidate
                                </a>
                                <a href="addemployee.php" class="btn btn-info">
                                    <i class="fas fa-user-tie me-2"></i>Add Employee
                                </a>
                                <?php } ?>
                                <a href="allCompany.php" class="btn btn-outline-primary">
                                    <i class="fas fa-building me-2"></i>View Companies
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        // Desktop sidebar toggle
        sidebarToggle?.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.className = 'fas fa-chevron-right';
            } else {
                icon.className = 'fas fa-chevron-left';
            }
        });

        // Mobile sidebar toggle
        mobileToggle?.addEventListener('click', function() {
            sidebar.classList.add('show');
            sidebarOverlay.classList.add('show');
        });

        // Close mobile sidebar
        sidebarOverlay?.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });

        // Active navigation highlight
        const navLinks = document.querySelectorAll('.nav-link');
        const currentPath = window.location.pathname;
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath.split('/').pop()) {
                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        });

        // Function to check renewal dates
        function checkRenewal() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "checkRenewal.php", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log(xhr.responseText);
                }
            };
            xhr.send();
        }

        // Run the checkRenewal function every 1 hour
        setInterval(checkRenewal, 3600000);
        window.onload = checkRenewal;

        // Smooth scrolling for sidebar
        sidebar.addEventListener('wheel', function(e) {
            e.preventDefault();
            this.scrollTop += e.deltaY;
        });
    </script>
</body>
</html>