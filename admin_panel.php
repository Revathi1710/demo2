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
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --dark-color: #1a252f;
            --light-color: #ecf0f1;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            transition: var(--transition);
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            position: relative;
        }

        .sidebar-logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }

        .sidebar.collapsed .sidebar-logo {
            max-width: 40px;
        }

        .sidebar-title {
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            transition: var(--transition);
        }

        .sidebar.collapsed .sidebar-title {
            display: none;
        }

        .sidebar-toggle {
            position: absolute;
            top: 1rem;
            right: -15px;
            background: var(--secondary-color);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .sidebar-toggle:hover {
            background: var(--accent-color);
            transform: scale(1.1);
        }

        /* Navigation Styles */
        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 1.5rem;
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }

        .sidebar.collapsed .nav-section-title {
            display: none;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            position: relative;
            border-radius: 0;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .nav-link.active {
            color: white;
            background: var(--secondary-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--accent-color);
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
            margin-left: var(--sidebar-width);
            transition: var(--transition);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Top Header */
        .top-header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid #e9ecef;
        }

        .header-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--secondary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-details h6 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
        }

        .user-details small {
            color: #6c757d;
        }

        /* Content Area */
        .content-area {
            padding: 2rem;
        }

        .welcome-card {
            background: linear-gradient(135deg, var(--secondary-color), var(--success-color));
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .welcome-card h2 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .welcome-card p {
            margin: 0;
            opacity: 0.9;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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
            margin-bottom: 1rem;
        }

        .stat-icon.companies { background: var(--secondary-color); }
        .stat-icon.candidates { background: var(--success-color); }
        .stat-icon.employees { background: var(--warning-color); }
        .stat-icon.appointments { background: var(--accent-color); }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 1001;
                background: var(--primary-color);
                color: white;
                border: none;
                width: 45px;
                height: 45px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
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