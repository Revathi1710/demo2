<?php
ini_set('display_errors',1);
include("connection.php");
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$employee_username = $_SESSION['username'];
$logindesignation = $_SESSION['designation'];
$loginemployee_id = $_SESSION['id'];
$isAdmin = $_SESSION['isAdmin'];

function getEmployeeDetails($employee_username) {
    global $con;
    $stmt = $con->prepare("SELECT * FROM employees WHERE username = ?");
    $stmt->bind_param("s", $employee_username);
    $stmt->execute();
    $result = $stmt->get_result();
    $employeeDetails = $result->fetch_assoc();
    $stmt->close();
    return $employeeDetails;
}

$employeeDetails = getEmployeeDetails($employee_username);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Board - Modern Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #4facfe;
            --warning-color: #43e97b;
            --danger-color: #fa709a;
            --dark-color: #2d3748;
            --light-color: #f7fafc;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            transition: var(--transition);
            z-index: 1000;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            text-align: center;
            position: relative;
            background: rgba(255, 255, 255, 0.5);
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
            color: var(--dark-color);
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
            background: var(--primary-color);
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
            box-shadow: var(--shadow-md);
        }

        .sidebar-toggle:hover {
            background: var(--secondary-color);
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
            color: rgba(45, 55, 72, 0.6);
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
            color: rgba(45, 55, 72, 0.8);
            text-decoration: none;
            transition: var(--transition);
            position: relative;
            border-radius: 0;
        }

        .nav-link:hover {
            color: var(--primary-color);
            background: rgba(102, 126, 234, 0.1);
            transform: translateX(5px);
        }

        .nav-link.active {
            color: var(--primary-color);
            background: rgba(102, 126, 234, 0.15);
            font-weight: 500;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--primary-color);
            border-radius: 0 4px 4px 0;
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

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: var(--transition);
            min-height: 100vh;
            background: transparent;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Top Header */
        .top-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 1rem 2rem;
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left h1 {
            color: var(--dark-color);
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .header-left p {
            color: rgba(45, 55, 72, 0.6);
            margin: 0;
            font-size: 0.9rem;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            width: 300px;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(45, 55, 72, 0.5);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }

        .user-details h6 {
            margin: 0;
            color: var(--dark-color);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-details small {
            color: rgba(45, 55, 72, 0.6);
        }

        /* Task Board Styles */
        .task-board {
            padding: 0 2rem 2rem;
        }

        .board-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .board-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .create-task-btn {
            background: linear-gradient(135deg, var(--success-color), var(--warning-color));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            margin-left: auto;
        }

        .create-task-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .board-filters {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .filter-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .filter-btn:hover, .filter-btn.active {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        /* Kanban Board */
        .kanban-board {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .kanban-column {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            min-height: 600px;
        }

        .column-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }

        .column-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .column-count {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .column-actions {
            display: flex;
            gap: 0.5rem;
        }

        .column-action-btn {
            background: none;
            border: none;
            color: rgba(45, 55, 72, 0.5);
            padding: 0.25rem;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
        }

        .column-action-btn:hover {
            color: var(--primary-color);
            background: rgba(102, 126, 234, 0.1);
        }

        /* Task Cards */
        .task-cards {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .task-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .task-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-color);
        }

        .task-card.high-priority::before { background: var(--danger-color); }
        .task-card.medium-priority::before { background: var(--warning-color); }
        .task-card.low-priority::before { background: var(--success-color); }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .task-labels {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .task-label {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .task-label.frontend { background: rgba(240, 147, 251, 0.2); color: var(--accent-color); }
        .task-label.backend { background: rgba(79, 172, 254, 0.2); color: var(--success-color); }
        .task-label.design { background: rgba(250, 112, 154, 0.2); color: var(--danger-color); }
        .task-label.research { background: rgba(67, 233, 123, 0.2); color: var(--warning-color); }

        .task-menu {
            background: none;
            border: none;
            color: rgba(45, 55, 72, 0.5);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: var(--transition);
        }

        .task-menu:hover {
            color: var(--dark-color);
            background: rgba(0, 0, 0, 0.05);
        }

        .task-title {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            font-size: 1rem;
            line-height: 1.4;
        }

        .task-description {
            color: rgba(45, 55, 72, 0.7);
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .task-progress {
            margin-bottom: 1rem;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .progress-text {
            font-size: 0.8rem;
            color: rgba(45, 55, 72, 0.7);
            font-weight: 500;
        }

        .progress-percentage {
            font-size: 0.8rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .progress-bar-container {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
            height: 6px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .progress-bar.high { background: linear-gradient(90deg, var(--danger-color), var(--accent-color)); }
        .progress-bar.medium { background: linear-gradient(90deg, var(--warning-color), var(--success-color)); }
        .progress-bar.low { background: linear-gradient(90deg, var(--success-color), var(--primary-color)); }

        .task-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .task-assignees {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .assignee-avatars {
            display: flex;
            margin-left: -0.25rem;
        }

        .assignee-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid white;
            margin-left: -0.25rem;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }

        .assignee-avatar:nth-child(2) { background: var(--success-color); }
        .assignee-avatar:nth-child(3) { background: var(--warning-color); }
        .assignee-avatar:nth-child(4) { background: var(--danger-color); }

        .task-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: rgba(45, 55, 72, 0.5);
            font-size: 0.8rem;
        }

        .task-meta-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
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
                background: rgba(255, 255, 255, 0.9);
                color: var(--primary-color);
                border: none;
                width: 45px;
                height: 45px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: var(--shadow-md);
                backdrop-filter: blur(10px);
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

            .kanban-board {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .search-input {
                width: 200px;
            }

            .board-header {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar, .kanban-column::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track, .kanban-column::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb, .kanban-column::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover, .kanban-column::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
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
            <h5 class="sidebar-title">weihu</h5>
            <button class="sidebar-toggle d-none d-md-block" id="sidebarToggle">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <!-- Main Navigation -->
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <div class="nav-item">
                    <a href="header.php" class="nav-link">
                        <i class="fas fa-home nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="task-board.php" class="nav-link active">
                        <i class="fas fa-tasks nav-icon"></i>
                        <span class="nav-text">Tasks</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-line nav-icon"></i>
                        <span class="nav-text">Activities</span>
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
            <div class="header-content">
                <div class="header-left">
                    <h1>Board</h1>
                    <p>Today is <?php echo date('l, M jS, Y'); ?></p>
                </div>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Find something">
                    </div>
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($employeeDetails['employee_name'], 0, 1)); ?>
                        </div>
                        <div class="user-details d-none d-md-block">
                            <h6><?php echo $employeeDetails['employee_name']; ?></h6>
                            <small><?php echo $logindesignation; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Task Board -->
        <div class="task-board">
            <div class="board-header">
                <div class="board-title">
                    <i class="fas fa-tasks"></i>
                    Daily Tasks
                </div>
                <div class="board-filters">
                    <button class="filter-btn active">Filters</button>
                    <div class="user-avatars d-none d-md-flex">
                        <div class="assignee-avatar">BS</div>
                        <div class="assignee-avatar">JD</div>
                        <div class="assignee-avatar">MK</div>
                        <div class="assignee-avatar">AL</div>
                        <div class="assignee-avatar">+2</div>
                    </div>
                </div>
                <button class="create-task-btn">
                    <i class="fas fa-plus me-2"></i>Create task
                </button>
            </div>

            <div class="kanban-board">
                <!-- Todo List Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <div class="column-title">
                            <i class="fas fa-circle" style="color: #f093fb;"></i>
                            Todo list
                            <span class="column-count">16</span>
                        </div>
                        <div class="column-actions">
                            <button class="column-action-btn">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="column-action-btn">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                    </div>
                    <div class="task-cards">
                        <!-- Task Card 1 -->
                        <div class="task-card high-priority">
                            <div class="task-header">
                                <div class="task-labels">
                                    <span class="task-label frontend">Frontend</span>
                                    <span class="task-label research">Research</span>
                                </div>
                                <button class="task-menu">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                            <h4 class="task-title">Search inspirations for upcoming project</h4>
                            <p class="task-description">Look for design patterns and modern UI trends</p>
                            <div class="task-progress">
                                <div class="progress-label">
                                    <span class="progress-text">Progress</span>
                                    <span class="progress-percentage">40%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar high" style="width: 40%;"></div>
                                </div>
                            </div>
                            <div class="task-footer">
                                <div class="task-assignees">
                                    <div class="assignee-avatars">
                                        <div class="assignee-avatar">BS</div>
                                        <div class="assignee-avatar">JD</div>
                                    </div>
                                </div>
                                <div class="task-meta">
                                    <div class="task-meta-item">
                                        <i class="fas fa-paperclip"></i>
                                        <span>12</span>
                                    </div>
                                    <div class="task-meta-item">
                                        <i class="fas fa-comment"></i>
                                        <span>8</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Task Card 2 -->
                        <div class="task-card medium-priority">
                            <div class="task-header">
                                <div class="task-labels">
                                    <span class="task-label frontend">Frontend</span>
                                    <span class="task-label design">Design</span>
                                </div>
                                <button class="task-menu">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                            <h4 class="task-title">Gimko mobile app design</h4>
                            <p class="task-description">Create user flow and wireframes for mobile application</p>
                            <div class="task-progress">
                                <div class="progress-label">
                                    <span class="progress-text">Progress</span>
                                    <span class="progress-percentage">15%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar medium" style="width: 15%;"></div>
                                </div>
                            </div>
                            <div class="task-footer">
                                <div class="task-assignees">
                                    <div class="assignee-avatars">
                                        <div class="assignee-avatar">MK</div>
                                        <div class="assignee-avatar">AL</div>
                                    </div>
                                </div>
                                <div class="task-meta">
                                    <div class="task-meta-item">
                                        <i class="fas fa-paperclip"></i>
                                        <span>7</span>
                                    </div>
                                    <div class="task-meta-item">
                                        <i class="fas fa-comment"></i>
                                        <span>3</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- In Progress Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <div class="column-title">
                            <i class="fas fa-circle" style="color: #43e97b;"></i>
                            In Progress
                            <span class="column-count">3</span>
                        </div>
                        <div class="column-actions">
                            <button class="column-action-btn">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="column-action-btn">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                    </div>
                    <div class="task-cards">
                        <!-- Task Card 3 -->
                        <div class="task-card high-priority">
                            <div class="task-header">
                                <div class="task-labels">
                                    <span class="task-label backend">Backend</span>
                                    <span class="task-label research">Research</span>
                                </div>
                                <button class="task-menu">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                            <h4 class="task-title">Weihu product task and the task product topic</h4>
                            <p class="task-description">Develop comprehensive product strategy and roadmap</p>
                            <div class="task-progress">
                                <div class="progress-label">
                                    <span class="progress-text">Progress</span>
                                    <span class="progress-percentage">65%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar high" style="width: 65%;"></div>
                                </div>
                            </div>
                            <div class="task-footer">
                                <div class="task-assignees">
                                    <div class="assignee-avatars">
                                        <div class="assignee-avatar">BS</div>
                                        <div class="assignee-avatar">JD</div>
                                    </div>
                                </div>
                                <div class="task-meta">
                                    <div class="task-meta-item">
                                        <i class="fas fa-paperclip"></i>
                                        <span>6</span>
                                    </div>
                                    <div class="task-meta-item">
                                        <i class="fas fa-comment"></i>
                                        <span>1</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Task Card 4 -->
                        <div class="task-card low-priority">
                            <div class="task-header">
                                <div class="task-labels">
                                    <span class="task-label frontend">Frontend</span>
                                    <span class="task-label design">Design</span>
                                </div>
                                <button class="task-menu">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                            <h4 class="task-title">Design CRM shop product page responsive website</h4>
                            <p class="task-description">Create responsive design for e-commerce platform</p>
                            <div class="task-progress">
                                <div class="progress-label">
                                    <span class="progress-text">Progress</span>
                                    <span class="progress-percentage">40%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar low" style="width: 40%;"></div>
                                </div>
                            </div>
                            <div class="task-footer">
                                <div class="task-assignees">
                                    <div class="assignee-avatars">
                                        <div class="assignee-avatar">MK</div>
                                    </div>
                                </div>
                                <div class="task-meta">
                                    <div class="task-meta-item">
                                        <i class="fas fa-paperclip"></i>
                                        <span>12</span>
                                    </div>
                                    <div class="task-meta-item">
                                        <i class="fas fa-comment"></i>
                                        <span>8</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- In Review Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <div class="column-title">
                            <i class="fas fa-circle" style="color: #4facfe;"></i>
                            In Review
                            <span class="column-count">2</span>
                        </div>
                        <div class="column-actions">
                            <button class="column-action-btn">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="column-action-btn">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                    </div>
                    <div class="task-cards">
                        <!-- Task Card 5 -->
                        <div class="task-card medium-priority">
                            <div class="task-header">
                                <div class="task-labels">
                                    <span class="task-label frontend">Frontend</span>
                                    <span class="task-label design">Design</span>
                                </div>
                                <button class="task-menu">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                            <h4 class="task-title">Crypto product landing page create and develop</h4>
                            <p class="task-description">Build modern landing page for cryptocurrency platform</p>
                            <div class="task-footer">
                                <div class="task-assignees">
                                    <div class="assignee-avatars">
                                        <div class="assignee-avatar">BS</div>
                                        <div class="assignee-avatar">JD</div>
                                        <div class="assignee-avatar">MK</div>
                                    </div>
                                </div>
                                <div class="task-meta">
                                    <div class="task-meta-item">
                                        <i class="fas fa-paperclip"></i>
                                        <span>12</span>
                                    </div>
                                    <div class="task-meta-item">
                                        <i class="fas fa-comment"></i>
                                        <span>8</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Task Card 6 -->
                        <div class="task-card low-priority">
                            <div class="task-header">
                                <div class="task-labels">
                                    <span class="task-label backend">Backend</span>
                                    <span class="task-label design">Design</span>
                                </div>
                                <button class="task-menu">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                            <h4 class="task-title">Network video platform web app design and develop</h4>
                            <p class="task-description">Create streaming platform with modern UI/UX</p>
                            <div class="task-footer">
                                <div class="task-assignees">
                                    <div class="assignee-avatars">
                                        <div class="assignee-avatar">AL</div>
                                        <div class="assignee-avatar">BS</div>
                                    </div>
                                </div>
                                <div class="task-meta">
                                    <div class="task-meta-item">
                                        <i class="fas fa-paperclip"></i>
                                        <span>12</span>
                                    </div>
                                    <div class="task-meta-item">
                                        <i class="fas fa-comment"></i>
                                        <span>8</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Done Column -->
                <div class="kanban-column">
                    <div class="column-header">
                        <div class="column-title">
                            <i class="fas fa-circle" style="color: #667eea;"></i>
                            Done
                            <span class="column-count">4</span>
                        </div>
                        <div class="column-actions">
                            <button class="column-action-btn">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="column-action-btn">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                    </div>
                    <div class="task-cards">
                        <!-- Task Card 7 -->
                        <div class="task-card high-priority">
                            <div class="task-header">
                                <div class="task-labels">
                                    <span class="task-label frontend">Frontend</span>
                                    <span class="task-label design">Design</span>
                                </div>
                                <button class="task-menu">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                            <h4 class="task-title">Affitto product full service</h4>
                            <p class="task-description">Complete product development and deployment</p>
                            <div class="task-footer">
                                <div class="task-assignees">
                                    <div class="assignee-avatars">
                                        <div class="assignee-avatar">JD</div>
                                        <div class="assignee-avatar">MK</div>
                                    </div>
                                </div>
                                <div class="task-meta">
                                    <div class="task-meta-item">
                                        <i class="fas fa-paperclip"></i>
                                        <span>7</span>
                                    </div>
                                    <div class="task-meta-item">
                                        <i class="fas fa-comment"></i>
                                        <span>2</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Task Card 8 -->
                        <div class="task-card medium-priority">
                            <div class="task-header">
                                <div class="task-labels">
                                    <span class="task-label research">Research</span>
                                    <span class="task-label design">Design</span>
                                </div>
                                <button class="task-menu">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                            <h4 class="task-title">Design Mail app product page redesign</h4>
                            <p class="task-description">Modernize email application interface</p>
                            <div class="task-footer">
                                <div class="task-assignees">
                                    <div class="assignee-avatars">
                                        <div class="assignee-avatar">BS</div>
                                        <div class="assignee-avatar">AL</div>
                                    </div>
                                </div>
                                <div class="task-meta">
                                    <div class="task-meta-item">
                                        <i class="fas fa-paperclip"></i>
                                        <span>12</span>
                                    </div>
                                    <div class="task-meta-item">
                                        <i class="fas fa-comment"></i>
                                        <span>6</span>
                                    </div>
                                </div>
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

        // Search functionality
        const searchInput = document.querySelector('.search-input');
        searchInput?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const taskCards = document.querySelectorAll('.task-card');
            
            taskCards.forEach(card => {
                const title = card.querySelector('.task-title').textContent.toLowerCase();
                const description = card.querySelector('.task-description').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = searchTerm === '' ? 'block' : 'none';
                }
            });
        });

        // Task card hover effects
        const taskCards = document.querySelectorAll('.task-card');
        taskCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Filter functionality
        const filterButtons = document.querySelectorAll('.filter-btn');
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Smooth animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe task cards for animation
        taskCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });

        // Auto-hide mobile menu on resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            }
        });
    </script>
</body>
</html>