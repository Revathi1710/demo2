<?php

include('connection.php');
session_start();

include('header.php');
ini_set('display_errors', 1);

$filter_query = "";

if (isset($_POST['update_employee'])) {
    $employee_id = $_POST['employee_id'];
    $selected_leads = $_POST['selected_leads'];

    if (!empty($employee_id) && !empty($selected_leads)) {
        $lead_ids = implode(',', $selected_leads);
        $query = "UPDATE company SET employee_id = ? WHERE id IN ($lead_ids)";

        if ($stmt = $con->prepare($query)) {
            $stmt->bind_param("i", $employee_id);

            if ($stmt->execute()) {
                echo '<script>alert("Leads Allocated Successfully");</script>';
                echo '<script>window.location.href = "allCompany.php";</script>';
            } else {
                echo "Error updating records: " . $con->error;
            }
            $stmt->close();
        }
    } else {
        echo '<script>alert("Please select an employee and at least one lead");</script>';
        echo '<script>window.location.href = "allCompany.php";</script>';
    }
}

if (isset($_POST['update_hiring'])) {
    $candidate_id = $_POST['candidate_id'];
    $company_id = $_POST['company_id'];
    $salary_details = $_POST['annual_income'];
    $bond = $_POST['bond'];
    $hiring_date = $_POST['hiring_date'];
    $employee_id = $_POST['employee_id'];
    $position = $_POST['position'];

    $update_query = "INSERT INTO hiring_details (company_id, candidate_id, salary_details, hiring_date, bond, position, employee_id) 
                     VALUES ('$company_id', '$candidate_id', '$salary_details', '$hiring_date', '$bond', '$position', '$employee_id')";

    if (mysqli_query($con, $update_query)) {
        echo '<script>alert("Hiring details added successfully.");</script>';
        echo '<script>window.location.href = "allCompany.php";</script>';
        mysqli_query($con, "UPDATE candidate SET isHiring = 'Hired' WHERE id = '$candidate_id'");
    } else {
        echo "Error: " . mysqli_error($con);
    }
}

// Check employee's designation
if ($isAdmin == '1') {
    function getTotalDriversCount($filter_query = "") {
        global $con;
        $base_query = "SELECT COUNT(*) AS total FROM company";
        if (!empty($filter_query)) {
            $base_query .= " WHERE $filter_query";
        }
        $result = mysqli_query($con, $base_query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row['total'];
        } else {
            echo "Error getting total candidate count: " . mysqli_error($con);
            return 0;
        }
    }

    function getDriversPaginated($offset, $driversPerPage, $filter_query = "", $order_by = "") {
        global $con;
        $base_query = "SELECT * FROM company";
        if (!empty($filter_query)) {
            $base_query .= " WHERE $filter_query";
        }
        if (!empty($order_by)) {
            $base_query .= " $order_by";
        } else {
            $base_query .= " ORDER BY id ASC";
        }
        $base_query .= " LIMIT $offset, $driversPerPage";
        $result = mysqli_query($con, $base_query);
        if ($result) {
            return $result;
        } else {
            echo "Error executing paginated candidate query: " . mysqli_error($con);
            return null;
        }
    }
} else {
    function getTotalDriversCount($filter_query = "") {
        global $con, $employeeDetails, $loginemployee_id;
        $base_query = "SELECT COUNT(*) AS total FROM company WHERE employee_id = $loginemployee_id";
        if (!empty($filter_query)) {
            $base_query .= " AND ($filter_query)";
        }
        $result = mysqli_query($con, $base_query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row['total'];
        } else {
            echo "Error getting total lead count: " . mysqli_error($con);
            return 0;
        }
    }

    function getDriversPaginated($offset, $driversPerPage, $filter_query = "", $order_by = "") {
        global $con, $employeeDetails, $loginemployee_id;
        $base_query = "SELECT * FROM company WHERE employee_id = $loginemployee_id";
        if (!empty($filter_query)) {
            $base_query .= " AND ($filter_query)";
        }
        if (!empty($order_by)) {
            $base_query .= " $order_by";
        }
        $base_query .= " LIMIT $offset, $driversPerPage";
        $result = mysqli_query($con, $base_query);
        if ($result) {
            return $result;
        } else {
            echo "Error executing paginated drivers query: " . mysqli_error($con);
            return null;
        }
    }
}

function getDomainHistory($domain_id) {
    global $con;
    $query = "SELECT * FROM companyActivedetails WHERE lead_id = $domain_id ORDER BY created_at DESC";
    $result = mysqli_query($con, $query);
    return $result;
}

$driversPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 7;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $driversPerPage;

$search_value = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$sort = isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : '';
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
$categorization = isset($_GET['categorization']) ? htmlspecialchars($_GET['categorization']) : '';

$from_date = isset($_GET['fromdate']) ? $_GET['fromdate'] : '';
$to_date = isset($_GET['todate']) ? $_GET['todate'] : '';

function convertDateFormat($date) {
    $dateArray = explode('/', $date);
    if (count($dateArray) == 3) {
        return $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
    }
    return null;
}

$from_date_db = convertDateFormat($from_date);
$to_date_db = convertDateFormat($to_date);

if ($status === 'NotOpen') {
    $filter_query = "(action IS NULL OR action = '')";
} elseif (in_array($status, ['NI', 'Callback', 'Prospect', 'Followup', 'Converted'])) {
    $filter_query = "(action = '$status')";
}

$order_by = "";
if (!empty($sort)) {
    if ($sort === 'ascending') {
        $order_by = "ORDER BY company_name ASC";
    } elseif ($sort === 'descending') {
        $order_by = "ORDER BY company_name DESC";
    } elseif ($sort === 'recently_added') {
        $order_by = "ORDER BY id DESC";
    }
}

if (!empty($categorization)) {
    $filter_query = "(category = '$categorization')";
}

if ($search_value) {
    if (empty($filter_query)) {
        $filter_query = "(company_name LIKE '%$search_value%' 
                        OR email LIKE '%$search_value%' 
                        OR contact_number LIKE '%$search_value%')";
    } else {
        $filter_query .= " AND (company_name LIKE '%$search_value%' 
                        OR email LIKE '%$search_value%' 
                        OR contact_number LIKE '%$search_value%')";
    }
}

if (!empty($from_date) && !empty($to_date)) {
    if (empty($filter_query)) {
        $filter_query = "(nextfollow_date BETWEEN '$from_date_db' AND '$to_date_db')";
    } else {
        $filter_query .= " AND (nextfollow_date BETWEEN '$from_date_db' AND '$to_date_db')";
    }
}

$total = getTotalDriversCount($filter_query);
$employee = getDriversPaginated($offset, $driversPerPage, $filter_query, $order_by);

$filters = [
    'fromdate' => isset($_GET['fromdate']) ? $_GET['fromdate'] : '',
    'todate' => isset($_GET['todate']) ? $_GET['todate'] : ''
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Daterangepicker CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    
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
            --border-color: #e2e8f0;
            --text-muted: #64748b;
            --bg-card: #ffffff;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
            color: var(--dark-color);
        }

        /* Main Container */
        .main-container {
            padding: 2rem;
            margin-left: 280px;
            transition: var(--transition);
        }

        .main-container.expanded {
            margin-left: 80px;
        }

        /* Page Header */
        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .page-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0;
        }

        .title-left h1 {
            color: var(--dark-color);
            font-size: 2.25rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .company-count {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }

        .title-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--success-color), var(--warning-color));
            border: none;
            color: white;
            padding: 0.875rem 1.75rem;
            border-radius: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            box-shadow: var(--shadow-md);
            font-size: 0.9rem;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
        }

        .btn-reset {
            background: linear-gradient(135deg, var(--danger-color), #ff6b6b);
            border: none;
            color: white;
            padding: 0.875rem 1.75rem;
            border-radius: 15px;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: var(--shadow-md);
            font-size: 0.9rem;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Filter Card */
        .filter-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            align-items: end;
        }

        .form-group {
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .form-control-modern {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--border-color);
            border-radius: 15px;
            font-size: 0.9rem;
            transition: var(--transition);
            background: white;
            color: var(--dark-color);
            font-weight: 500;
        }

        .form-control-modern:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            z-index: 2;
        }

        .search-input {
            padding-left: 3rem !important;
        }

        /* Data Table Card */
        .data-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .table-header {
            padding: 2rem;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.8), rgba(241, 245, 249, 0.8));
        }

        .table-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Modern Table - Fixed Layout */
        .table-container {
            overflow-x: auto;
            max-height: 70vh;
        }

        .table-modern {
            margin: 0;
            width: 100%;
            table-layout: fixed;
            min-width: 1200px;
        }

        .table-modern thead th {
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.95), rgba(241, 245, 249, 0.95));
            border: none;
            padding: 1.5rem 1rem;
            font-weight: 700;
            font-size: 0.8rem;
            color: var(--dark-color);
            text-transform: uppercase;
            letter-spacing: 0.75px;
            border-bottom: 3px solid var(--primary-color);
            position: sticky;
            top: 0;
            z-index: 10;
            text-align: left;
        }

        .table-modern tbody td {
            padding: 1.75rem 1rem;
            border: none;
            border-bottom: 1px solid rgba(226, 232, 240, 0.4);
            vertical-align: middle;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
            word-wrap: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table-modern tbody tr {
            transition: var(--transition);
            background: white;
        }

        .table-modern tbody tr:nth-child(even) {
            background: rgba(248, 250, 252, 0.3);
        }

        .table-modern tbody tr:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
            transform: translateY(-1px);
        }

        /* Optimized Column Widths */
        .table-modern th:nth-child(1),
        .table-modern td:nth-child(1) { 
            width: 60px; 
            text-align: center;
        }
        
        .table-modern th:nth-child(2),
        .table-modern td:nth-child(2) { 
            width: 160px; 
        }
        
        .table-modern th:nth-child(3),
        .table-modern td:nth-child(3) { 
            width: 280px; 
        }
        
        .table-modern th:nth-child(4),
        .table-modern td:nth-child(4) { 
            width: 150px; 
        }
        
        .table-modern th:nth-child(5),
        .table-modern td:nth-child(5) { 
            width: 180px; 
        }
        
        .table-modern th:nth-child(6),
        .table-modern td:nth-child(6) { 
            width: 130px; 
            text-align: center;
        }
        
        .table-modern th:nth-child(7),
        .table-modern td:nth-child(7) { 
            width: 160px; 
        }
        
        .table-modern th:nth-child(8),
        .table-modern td:nth-child(8) { 
            width: 100px; 
            text-align: center;
        }

        /* Company Info */
        .company-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .company-name {
            font-weight: 700;
            color: var(--dark-color);
            text-decoration: none;
            font-size: 0.95rem;
            transition: var(--transition);
            line-height: 1.4;
        }

        .company-name:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .company-location {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            white-space: nowrap;
        }

        .status-not-open {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.2), rgba(255, 193, 7, 0.1));
            color: #f59e0b;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .status-ni {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1));
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .status-success {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(34, 197, 94, 0.1));
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        /* Allocation Status */
        .allocation-status {
            padding: 0.6rem 1.2rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .allocated {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.1));
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .not-allocated {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.1));
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* Action Dropdown */
        .action-dropdown {
            position: relative;
        }

        .action-btn {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border: 1px solid rgba(102, 126, 234, 0.2);
            padding: 0.75rem;
            border-radius: 12px;
            color: var(--primary-color);
            cursor: pointer;
            transition: var(--transition);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn:hover {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .dropdown-menu-custom {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(226, 232, 240, 0.5);
            min-width: 200px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            backdrop-filter: blur(20px);
        }

        .dropdown-menu-custom.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item-custom {
            padding: 1rem 1.5rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .dropdown-item-custom:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            color: var(--primary-color);
        }

        .dropdown-item-custom.danger:hover {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            color: #dc2626;
        }

        /* Modern Modals */
        .modal-modern .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(20px);
        }

        .modal-modern .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 2rem;
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.8), rgba(241, 245, 249, 0.8));
            border-radius: 20px 20px 0 0;
        }

        .modal-modern .modal-title {
            font-weight: 700;
            color: var(--dark-color);
            font-size: 1.25rem;
        }

        .modal-modern .modal-body {
            padding: 2rem;
        }

        .modal-modern .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 2rem;
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.8), rgba(241, 245, 249, 0.8));
            border-radius: 0 0 20px 20px;
        }

        /* Pagination */
        .pagination-modern {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.75rem;
            margin-top: 2rem;
        }

        .page-link-modern {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border-radius: 15px;
            background: white;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .page-link-modern:hover {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .page-link-modern.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: var(--shadow-md);
        }

        /* Checkbox Styling */
        .form-check-input-modern {
            width: 1.5rem;
            height: 1.5rem;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .form-check-input-modern:checked {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-color: var(--primary-color);
        }

        /* Footer Info */
        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem;
            border-top: 1px solid var(--border-color);
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.8), rgba(241, 245, 249, 0.8));
        }

        .entries-info {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .per-page-selector {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .per-page-select {
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            background: white;
            color: var(--dark-color);
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .per-page-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        /* Employee Selection */
        .employee-selection {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .employee-selection h6 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-container {
                margin-left: 0;
                padding: 1.5rem;
            }

            .table-modern {
                min-width: 1000px;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .filter-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .title-left h1 {
                font-size: 1.75rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .title-actions {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
                width: 100%;
            }

            .page-header {
                padding: 1.5rem;
            }

            .filter-card,
            .table-header,
            .modal-modern .modal-body,
            .modal-modern .modal-header,
            .modal-modern .modal-footer {
                padding: 1.5rem;
            }

            .table-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .pagination-modern {
                justify-content: center;
                flex-wrap: wrap;
            }
        }

        /* Loading States */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        /* Custom Scrollbar */
        .table-container::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }

        .table-container::-webkit-scrollbar-track {
            background: rgba(248, 250, 252, 0.5);
            border-radius: 4px;
        }

        /* Enhanced Visual Effects */
        .data-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color));
            border-radius: 20px 20px 0 0;
        }

        .data-card {
            position: relative;
        }

        /* Improved spacing for better readability */
        .company-info .company-name {
            margin-bottom: 0.25rem;
        }

        .status-badge i {
            font-size: 0.7rem;
        }

        .allocation-status i {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
   
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <div class="title-left">
                    <h1>
                        <i class="fas fa-building"></i>
                        Company Management
                        <span class="company-count"><?= $total ?> Companies</span>
                    </h1>
                </div>
                <div class="title-actions">
                    <button class="btn-reset reset-btn">
                        <i class="fas fa-refresh me-2"></i>Reset Filters
                    </button>
                    <a href="addCompany.php" class="btn-primary-custom">
                        <i class="fas fa-plus"></i>Add Company
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="filter-card">
            <form action="" method="post">
                <div class="filter-row">
                    <!-- Search -->
                    <div class="form-group">
                        <label class="form-label">Search Companies</label>
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   class="form-control-modern search-input leadsearch" 
                                   placeholder="Search by name, email, or phone..." 
                                   name="search" 
                                   value="<?= $search_value ?>">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="form-group">
                        <label class="form-label">Status Filter</label>
                        <select name="status" class="form-control-modern status">
                            <option value="" <?= $status == '' ? 'selected' : '' ?>>All Status</option>
                            <option value="NI" <?= $status == 'NI' ? 'selected' : '' ?>>NI</option>
                            <option value="Callback" <?= $status == 'Callback' ? 'selected' : '' ?>>Callback</option>
                            <option value="Followup" <?= $status == 'Followup' ? 'selected' : '' ?>>Followup</option>
                            <option value="Prospect" <?= $status == 'Prospect' ? 'selected' : '' ?>>Prospect</option>
                            <option value="Converted" <?= $status == 'Converted' ? 'selected' : '' ?>>Converted</option>
                            <option value="NotOpen" <?= $status == 'NotOpen' ? 'selected' : '' ?>>Not Open</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select class="form-control-modern categorization" name="categorization">
                            <option value="" <?= $categorization == '' ? 'selected' : '' ?>>All Categories</option>
                            <option value="Production" <?= $categorization == 'Production' ? 'selected' : '' ?>>Production</option>
                            <option value="Maintance" <?= $categorization == 'Maintance' ? 'selected' : '' ?>>Maintenance</option>
                            <option value="Quality" <?= $categorization == 'Quality' ? 'selected' : '' ?>>Quality</option>
                            <option value="IT" <?= $categorization == 'IT' ? 'selected' : '' ?>>IT</option>
                            <option value="Non-IT" <?= $categorization == 'Non-IT' ? 'selected' : '' ?>>Non-IT</option>
                            <option value="Banking" <?= $categorization == 'Banking' ? 'selected' : '' ?>>Banking</option>
                            <option value="Others" <?= $categorization == 'Others' ? 'selected' : '' ?>>Others</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="form-group">
                        <label class="form-label">Date Range</label>
                        <input type="text" id="daterange" class="form-control-modern" placeholder="Select date range" />
                    </div>
                </div>

                <!-- Employee Allocation (Admin Only) -->
                <?php if ($isAdmin == '1') { ?>
                <div class="employee-selection">
                    <h6><i class="fas fa-users me-2"></i>Allocate to Employee</h6>
                    <div class="filter-row">
                        <div class="form-group">
                            <label class="form-label">Select Employee</label>
                            <select name="employee_id" class="form-control-modern" id="employeeSelect" required>
                                <option value="">Choose Employee</option>
                                <?php
                                function getEmployee2($con, $loginIdCustomer) {
                                    $query = "SELECT id, employee_name FROM employees WHERE isAdmin != '1'";
                                    return $con->query($query);
                                }

                                $getEmployees = getEmployee2($con, $loginIdCustomer);
                                if ($getEmployees && $getEmployees->num_rows > 0) {
                                    while ($employee2 = $getEmployees->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($employee2['id']) . '">' . htmlspecialchars($employee2['employee_name']) . '</option>';
                                    }
                                } else {
                                    echo '<option>No employees found</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn-primary-custom" name="update_employee" style="width: 100%;">
                                <i class="fas fa-check me-2"></i>Allocate Selected
                            </button>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </form>
        </div>

        <!-- Data Table -->
        <div class="data-card">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-table"></i>
                    Company Directory
                </h3>
            </div>
            
            <div class="table-container">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" class="form-check-input-modern">
                            </th>
                            <th>Person</th>
                            <th>Company Details</th>
                            <th>Phone</th>
                            <th>Allocated To</th>
                            <th>Status</th>
                            <th>Next Follow</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($employee) > 0) {
                            $serialNumber = $offset + 1;
                            while ($item = mysqli_fetch_assoc($employee)) {
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_leads[]" class="leadCheckbox form-check-input-modern" value="<?= $item['id']; ?>">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['contact_name']); ?></strong>
                                    </td>
                                    <td>
                                        <div class="company-info">
                                            <a href="viewprofile.php?id=<?= $item['id']; ?>" class="company-name">
                                                <?= !empty($item['company_name']) ? htmlspecialchars($item['company_name']) : 'No company name'; ?>
                                            </a>
                                            <div class="company-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?= htmlspecialchars($item['location']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone me-2 text-muted"></i>
                                        <?= htmlspecialchars($item['contact_number']); ?>
                                    </td>
                                    <td>
                                        <?php if (empty($item['employee_id'])): ?>
                                            <span class="allocation-status not-allocated">
                                                <i class="fas fa-exclamation-circle"></i>
                                                Not Allocated
                                            </span>
                                        <?php else: ?>
                                            <span class="allocation-status allocated">
                                                <i class="fas fa-user-check"></i>
                                                <?php
                                                if (!function_exists('getEmployeeName')) {
                                                    function getEmployeeName($employee_id, $con) {
                                                        $query = "SELECT employee_name FROM employees WHERE id = ?";
                                                        $stmt = $con->prepare($query);
                                                        $stmt->bind_param("i", $employee_id);
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        if ($result->num_rows > 0) {
                                                            $row = $result->fetch_assoc();
                                                            return $row['employee_name'];
                                                        }
                                                        return "Unknown Employee";
                                                    }
                                                }
                                                $employeeName = getEmployeeName($item['employee_id'], $con);
                                                echo htmlspecialchars($employeeName);
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['action'] == ''): ?>
                                            <span class="status-badge status-not-open">
                                                <i class="fas fa-clock"></i>Not Open
                                            </span>
                                        <?php else: ?>
                                            <?php if ($item['action'] == 'NI'): ?>
                                                <span class="status-badge status-ni">
                                                    <i class="fas fa-times-circle"></i><?= $item['action']; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge status-success">
                                                    <i class="fas fa-check-circle"></i><?= $item['action']; ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <small><i class="fas fa-calendar me-1"></i><?= $item['nextfollow_date']; ?></small>
                                            <small class="text-muted"><i class="fas fa-clock me-1"></i><?= $item['nextfollow_time']; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-dropdown">
                                            <button class="action-btn" type="button" onclick="toggleDropdown(<?= $item['id']; ?>)">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu-custom" id="dropdown-<?= $item['id']; ?>">
                                                <a class="dropdown-item-custom" href="viewCompany.php?id=<?= $item['id']; ?>">
                                                    <i class="fas fa-eye"></i>View Details
                                                </a>
                                                <button class="dropdown-item-custom" onclick="openModal('actionModal<?= $item['id']; ?>')">
                                                    <i class="fas fa-edit"></i>Take Action
                                                </button>
                                                <button class="dropdown-item-custom" onclick="openModal('HiringModal<?= $item['id']; ?>')">
                                                    <i class="fas fa-handshake"></i>Hiring Candidate
                                                </button>
                                                <button class="dropdown-item-custom" onclick="openModal('historyModal<?= $item['id']; ?>')">
                                                    <i class="fas fa-history"></i>History
                                                </button>
                                                <?php if($isAdmin == 1) { ?>
                                                <div style="border-top: 1px solid #e2e8f0; margin: 0.5rem 0;"></div>
                                                <a class="dropdown-item-custom danger" href="deleteCompany.php?id=<?= $item['id']; ?>" onclick="return confirm('Are you sure you want to delete this company?')">
                                                    <i class="fas fa-trash"></i>Delete
                                                </a>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <!-- Action Modal -->
                                        <div class="modal fade modal-modern" id="actionModal<?= $item['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-tasks me-2"></i>Take Action
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="updateAction2.php" method="POST">
                                                            <input type="hidden" name="domain_id" value="<?= $item['id']; ?>">
                                                            <input type="hidden" name="employee_id" value="<?= $loginemployee_id; ?>">

                                                            <div class="mb-3">
                                                                <label class="form-label">Action Type</label>
                                                                <select name="action" class="form-control-modern" required>
                                                                    <option value="">Select Action</option>
                                                                    <option value="Callback">Callback</option>
                                                                    <option value="NI">NI</option>
                                                                    <option value="Followup">Followup</option>
                                                                    <option value="Prospect">Prospect</option>
                                                                    <option value="Converted">Converted</option>
                                                                    <option value="">Not Opened</option>
                                                                </select>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Follow-up Date</label>
                                                                    <input type="date" name="nextfollow_date" class="form-control-modern">
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Follow-up Time</label>
                                                                    <input type="time" name="nextfollow_time" class="form-control-modern">
                                                                </div>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Details</label>
                                                                <textarea name="nextfollow" class="form-control-modern" rows="3" placeholder="Add any additional details..."></textarea>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Requirement</label>
                                                                <input type="text" name="requirement" class="form-control-modern" placeholder="Enter requirement details">
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn-primary-custom" name="update_lead">
                                                                    <i class="fas fa-save me-2"></i>Save Action
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Hiring Modal -->
                                        <div class="modal fade modal-modern" id="HiringModal<?= $item['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-handshake me-2"></i>Hiring Candidate
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="" method="POST">
                                                            <input type="hidden" name="company_id" value="<?= $item['id']; ?>">
                                                            <input type="hidden" name="employee_id" value="<?= $loginemployee_id; ?>">

                                                            <div class="mb-3">
                                                                <label class="form-label">Select Candidate</label>
                                                                <select name="candidate_id" class="form-control-modern" required>
                                                                    <option value="">Choose Candidate</option>
                                                                    <?php 
                                                                    $query = "SELECT * FROM candidate WHERE isHiring = 'Not Hired'";
                                                                    $result = mysqli_query($con, $query);
                                                                    if ($result) {
                                                                        while ($row = mysqli_fetch_assoc($result)) {
                                                                            echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Annual Income</label>
                                                                    <input type="text" name="annual_income" class="form-control-modern" placeholder="Enter annual income">
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Joining Date</label>
                                                                    <input type="date" name="hiring_date" class="form-control-modern">
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Position</label>
                                                                    <input type="text" name="position" class="form-control-modern" placeholder="Enter position">
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label">Bond Details</label>
                                                                    <input type="text" name="bond" class="form-control-modern" placeholder="Enter bond details">
                                                                </div>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn-primary-custom" name="update_hiring">
                                                                    <i class="fas fa-check me-2"></i>Confirm Hiring
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- History Modal -->
                                        <div class="modal fade modal-modern" id="historyModal<?= $item['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-history me-2"></i>History for <?= htmlspecialchars($item['company_name']); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="table-responsive">
                                                            <table class="table-modern">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Date</th>
                                                                        <th>Action</th>
                                                                        <th>Next Follow</th>
                                                                        <th>Message</th>
                                                                        <th>Employee</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $history = getDomainHistory($item['id']);
                                                                    if (mysqli_num_rows($history) > 0) {
                                                                        while ($history_item = mysqli_fetch_assoc($history)) { ?>
                                                                            <tr>
                                                                                <td><?= $history_item['created_at']; ?></td>
                                                                                <td>
                                                                                    <span class="status-badge status-success">
                                                                                        <?= $history_item['action']; ?>
                                                                                    </span>
                                                                                </td>
                                                                                <td>
                                                                                    <small><?= $history_item['nextfollowdate']; ?></small><br>
                                                                                    <small class="text-muted"><?= $history_item['nextfollowtime']; ?></small>
                                                                                </td>
                                                                                <td><?= $history_item['messages']; ?></td>
                                                                                <td>
                                                                                    <?php 
                                                                                    $employeeName = getEmployeeName($history_item['employee_id'], $con);
                                                                                    echo htmlspecialchars($employeeName);
                                                                                    ?>
                                                                                </td>
                                                                            </tr>
                                                                        <?php }
                                                                    } else {
                                                                        echo "<tr><td colspan='5' class='text-center text-muted'>No history found.</td></tr>";
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                $serialNumber++;
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center text-muted py-5'>
                                    <i class='fas fa-building fa-3x mb-3 d-block' style='opacity: 0.3;'></i>
                                    <h5>No Companies Found</h5>
                                    <p>Try adjusting your search criteria or add a new company.</p>
                                  </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="table-footer">
                <div class="per-page-selector">
                    <span>Show</span>
                    <select name="per_page" class="per-page-select selectpage">
                        <option value="7" <?= $driversPerPage == 7 ? 'selected' : '' ?>>7</option>
                        <option value="10" <?= $driversPerPage == 10 ? 'selected' : '' ?>>10</option>
                        <option value="50" <?= $driversPerPage == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $driversPerPage == 100 ? 'selected' : '' ?>>100</option>
                        <option value="200" <?= $driversPerPage == 200 ? 'selected' : '' ?>>200</option>
                    </select>
                    <span>entries</span>
                </div>

                <!-- Pagination -->
                <?php if ($total > 0) { ?>
                <div class="pagination-modern">
                   <?php
                   $totalPages = ceil($total / $driversPerPage);
                   $range = 1;
                   $start = max(1, $page - $range);
                   $end = min($totalPages, $page + $range);

                   if ($page > 1) { ?>
                        <a class="page-link-modern" href="allCompany.php?page=<?= $page - 1; ?>&<?= http_build_query($filters); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php } ?>

                    <?php for ($i = $start; $i <= $end; $i++) { ?>
                        <a class="page-link-modern <?= ($i == $page) ? 'active' : ''; ?>" 
                           href="allCompany.php?page=<?= $i; ?>&<?= http_build_query($filters); ?>">
                            <?= $i; ?>
                        </a>
                    <?php } ?>

                    <?php if ($page < $totalPages) { ?>
                       <a class="page-link-modern" href="allCompany.php?page=<?= $page + 1; ?>&<?= http_build_query($filters); ?>">
                           <i class="fas fa-chevron-right"></i>
                        </a>
                   <?php } ?>
               </div>
               <?php } ?>
           </div>
       </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        // Manual dropdown functionality
        function toggleDropdown(id) {
            // Close all other dropdowns first
            document.querySelectorAll('.dropdown-menu-custom').forEach(dropdown => {
                if (dropdown.id !== `dropdown-${id}`) {
                    dropdown.classList.remove('show');
                }
            });
            
            // Toggle the clicked dropdown
            const dropdown = document.getElementById(`dropdown-${id}`);
            dropdown.classList.toggle('show');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.action-dropdown')) {
                document.querySelectorAll('.dropdown-menu-custom').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });

        // Modal functions
        function openModal(modalId) {
            // Close dropdown first
            document.querySelectorAll('.dropdown-menu-custom').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
            
            // Open modal using Bootstrap
            const modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();
        }

        // Initialize date range picker
        $(function() {
            $('#daterange').daterangepicker({
                opens: 'left',
                locale: {
                    format: 'DD/MM/YYYY'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month')
            }, function(start, end, label) {
                const params = new URLSearchParams(window.location.search);
                params.set('fromdate', start.format('DD/MM/YYYY'));
                params.set('todate', end.format('DD/MM/YYYY'));
                window.location.search = params.toString();
            });
        });

        // Select all functionality
        document.getElementById('selectAll').onclick = function() {
            var checkboxes = document.getElementsByClassName('leadCheckbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = this.checked;
            }
        };

        // Reset filters
        document.querySelector('.reset-btn').addEventListener('click', function() {
            window.location.href = window.location.pathname;
        });

        // Auto-submit filters
        function setupAutoSubmit(selector, paramName) {
            document.querySelectorAll(selector).forEach(item => {
                item.addEventListener('change', function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.set(paramName, this.value);
                    window.location.search = urlParams.toString();
                });
            });
        }

        setupAutoSubmit('.leadsearch', 'search');
        setupAutoSubmit('.status', 'status');
        setupAutoSubmit('.categorization', 'categorization');
        setupAutoSubmit('.selectpage', 'per_page');

        // Loading states
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                this.classList.add('loading');
            });
        });

        // Smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.filter-card, .data-card, .page-header');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Prevent form submission when clicking dropdown items
        document.querySelectorAll('.dropdown-item-custom').forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.tagName === 'BUTTON') {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
    </script>
</body>
</html>