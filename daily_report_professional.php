<?php
include("connection.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
$report_date = $_GET['report_date'] ?? date('Y-m-d');
require_once __DIR__ . '/vendor/autoload.php';

if (!function_exists('getEmployeeName')) {
    function getEmployeeName($employee_id, $con) {
        if (empty($employee_id)) return "Not Assigned";
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

function getCompanyName($company_id, $con) {
    if (empty($company_id)) return "Unknown Company";
    $query = "SELECT company_name FROM company WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['company_name'];
    }
    return "Unknown Company";
}

function getCandidateName($candidate_id, $con) {
    if (empty($candidate_id)) return "Unknown Candidate";
    $query = "SELECT name FROM candidate WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['name'];
    }
    return "Unknown Candidate";
}

// Enhanced PDF styling function
function getProfessionalPDFStyles() {
    return "
    <style>
        body { font-family: 'Arial', sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
        .header { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 25px; text-align: center; margin: -20px -20px 30px -20px; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 700; }
        .header p { margin: 8px 0 0 0; font-size: 14px; opacity: 0.9; }
        .report-meta { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2563eb; }
        .report-meta h3 { margin: 0 0 10px 0; color: #1e293b; font-size: 16px; }
        .report-meta p { margin: 0; color: #64748b; font-size: 12px; }
        .summary-cards { display: flex; gap: 15px; margin-bottom: 30px; }
        .summary-card { flex: 1; background: white; padding: 20px; border-radius: 8px; text-align: center; border-top: 3px solid #2563eb; }
        .summary-card h4 { margin: 0 0 10px 0; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-card .number { font-size: 32px; font-weight: 700; color: #2563eb; margin: 0; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; margin-bottom: 30px; }
        .table-header { background: #f8fafc; padding: 15px 12px; font-weight: 600; color: #374151; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; }
        .table-cell { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 11px; color: #4b5563; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; text-transform: uppercase; }
        .status-not-open { background: #fef3c7; color: #92400e; }
        .status-ni { background: #fee2e2; color: #dc2626; }
        .status-success { background: #dcfce7; color: #16a34a; }
        .status-scheduled { background: #dbeafe; color: #2563eb; }
        .status-completed { background: #dcfce7; color: #16a34a; }
        .status-selected { background: #f0fdf4; color: #15803d; }
        .status-rejected { background: #fecaca; color: #dc2626; }
        .section-title { color: #1e293b; font-size: 18px; font-weight: 600; margin: 30px 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; }
        .no-data { text-align: center; color: #9ca3af; font-style: italic; padding: 40px; background: #f9fafb; }
        .footer { margin-top: 40px; text-align: center; color: #9ca3af; font-size: 10px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
    </style>";
}

if(isset($_POST['download_pdf'])){
    ob_clean();
    ob_start();

    $report_type   = $_POST['report_type'];
    $company_id    = $_POST['company_id'] ?? "";
    $candidate_id  = $_POST['candidate_id'] ?? "";
    $from_date     = $_POST['from_date'] ?? "";
    $to_date       = $_POST['to_date'] ?? "";
    $action        = $_POST['action'] ?? "";

    $dateRange = "";
    if($from_date && $to_date) {
        $dateRange = "From " . date('d M Y', strtotime($from_date)) . " to " . date('d M Y', strtotime($to_date));
    } elseif($from_date) {
        $dateRange = "From " . date('d M Y', strtotime($from_date));
    } elseif($to_date) {
        $dateRange = "Until " . date('d M Y', strtotime($to_date));
    }

    // -------- COMPANY REPORT --------
    if($report_type == "company"){
        $query = "SELECT * FROM company WHERE 1";
        if($company_id != "") $query .= " AND id='$company_id'";
        if($from_date && $to_date) $query .= " AND nextfollow_date BETWEEN '$from_date' AND '$to_date'";
        if($action != "") $query .= " AND action='$action'";
        $result = $con->query($query);

        $html = getProfessionalPDFStyles() . "
        <div class='header'>
            <h1>Company Report</h1>
            <p>Professional Business Intelligence Dashboard</p>
        </div>
        
        <div class='report-meta'>
            <h3>Report Details</h3>
            <p><strong>Generated:</strong> " . date('d M Y, H:i A') . "</p>
            <p><strong>Date Range:</strong> " . ($dateRange ?: 'All Records') . "</p>
            <p><strong>Filter:</strong> " . ($action ? ucfirst($action) . ' Status' : 'All Statuses') . "</p>
            <p><strong>Total Records:</strong> " . $result->num_rows . "</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th class='table-header'>Company Name</th>
                    <th class='table-header'>Contact Person</th>
                    <th class='table-header'>Phone</th>
                    <th class='table-header'>Email</th>
                    <th class='table-header'>Status</th>
                    <th class='table-header'>Assigned Employee</th>
                    <th class='table-header'>Next Follow Date</th>
                </tr>
            </thead>
            <tbody>";

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $statusClass = 'status-success';
                $statusText = htmlspecialchars($row['action']);
                
                if ($row['action'] == '') {
                    $statusClass = 'status-not-open';
                    $statusText = 'Not Opened';
                } elseif ($row['action'] == 'NI') {
                    $statusClass = 'status-ni';
                    $statusText = 'Not Interested';
                }

                $html .= "<tr>
                            <td class='table-cell'><strong>".htmlspecialchars($row['company_name'])."</strong></td>
                            <td class='table-cell'>".htmlspecialchars($row['contact_name'])."</td>
                            <td class='table-cell'>".htmlspecialchars($row['contact_number'])."</td>
                            <td class='table-cell'>".htmlspecialchars($row['email'])."</td>
                            <td class='table-cell'><span class='status-badge $statusClass'>$statusText</span></td>
                            <td class='table-cell'>".htmlspecialchars(getEmployeeName($row['employee_id'], $con))."</td>
                            <td class='table-cell'>".htmlspecialchars($row['nextfollow_date'] ?: 'Not Set')."</td>
                          </tr>";
            }
        } else {
            $html .= "<tr><td colspan='7' class='no-data'>No company records found matching the criteria</td></tr>";
        }
        
        $html .= "</tbody></table>
        <div class='footer'>
            <p>Generated by Professional CRM System | " . date('Y') . " | Confidential Business Report</p>
        </div>";

        $mpdf = new \Mpdf\Mpdf(['format' => 'A4-L']);
        $mpdf->WriteHTML($html);
        ob_end_clean();
        $mpdf->Output("Company_Report_".date('Y-m-d').".pdf", "D");
        exit;
    }

    // -------- CANDIDATE REPORT --------
    else if($report_type == "candidate"){
        $query = "SELECT * FROM candidate WHERE 1";
        if($candidate_id != "") $query .= " AND id='$candidate_id'";
        if($from_date && $to_date) $query .= " AND nextfollow_date BETWEEN '$from_date' AND '$to_date'";
        if($action != "") $query .= " AND action='$action'";
        $result = $con->query($query);

        $html = getProfessionalPDFStyles() . "
        <div class='header'>
            <h1>Candidate Report</h1>
            <p>Professional Talent Management Dashboard</p>
        </div>
        
        <div class='report-meta'>
            <h3>Report Details</h3>
            <p><strong>Generated:</strong> " . date('d M Y, H:i A') . "</p>
            <p><strong>Date Range:</strong> " . ($dateRange ?: 'All Records') . "</p>
            <p><strong>Filter:</strong> " . ($action ? ucfirst($action) . ' Status' : 'All Statuses') . "</p>
            <p><strong>Total Records:</strong> " . $result->num_rows . "</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th class='table-header'>Candidate Name</th>
                    <th class='table-header'>Email</th>
                    <th class='table-header'>Phone</th>
                    <th class='table-header'>Qualification</th>
                    <th class='table-header'>Status</th>
                    <th class='table-header'>Assigned Employee</th>
                    <th class='table-header'>Next Follow Date</th>
                    <th class='table-header'>Hiring Status</th>
                </tr>
            </thead>
            <tbody>";

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $statusClass = 'status-success';
                $statusText = htmlspecialchars($row['action']);
                
                if ($row['action'] == '') {
                    $statusClass = 'status-not-open';
                    $statusText = 'Not Opened';
                } elseif ($row['action'] == 'NI') {
                    $statusClass = 'status-ni';
                    $statusText = 'Not Interested';
                }

                $html .= "<tr>
                            <td class='table-cell'><strong>".htmlspecialchars($row['name'])."</strong></td>
                            <td class='table-cell'>".htmlspecialchars($row['email'])."</td>
                            <td class='table-cell'>".htmlspecialchars($row['contact_number'])."</td>
                            <td class='table-cell'>".htmlspecialchars($row['qulification'])."</td>
                            <td class='table-cell'><span class='status-badge $statusClass'>$statusText</span></td>
                            <td class='table-cell'>".htmlspecialchars(getEmployeeName($row['employee_id'], $con))."</td>
                            <td class='table-cell'>".htmlspecialchars($row['nextfollow_date'] ?: 'Not Set')."</td>
                            <td class='table-cell'>".htmlspecialchars($row['isHiring'] ?: 'Pending')."</td>
                          </tr>";
            }
        } else {
            $html .= "<tr><td colspan='8' class='no-data'>No candidate records found matching the criteria</td></tr>";
        }
        
        $html .= "</tbody></table>
        <div class='footer'>
            <p>Generated by Professional CRM System | " . date('Y') . " | Confidential Business Report</p>
        </div>";

        $mpdf = new \Mpdf\Mpdf(['format' => 'A4-L']);
        $mpdf->WriteHTML($html);
        ob_end_clean();
        $mpdf->Output("Candidate_Report_".date('Y-m-d').".pdf", "D");
        exit;
    }

    // -------- APPOINTMENT REPORT --------
    else if($report_type == "appointment"){
        $query = "SELECT * FROM appointment WHERE 1";
        if($from_date && $to_date) $query .= " AND date BETWEEN '$from_date' AND '$to_date'";
        $result = $con->query($query);

        $html = getProfessionalPDFStyles() . "
        <div class='header'>
            <h1>Appointment Report</h1>
            <p>Professional Schedule Management Dashboard</p>
        </div>
        
        <div class='report-meta'>
            <h3>Report Details</h3>
            <p><strong>Generated:</strong> " . date('d M Y, H:i A') . "</p>
            <p><strong>Date Range:</strong> " . ($dateRange ?: 'All Records') . "</p>
            <p><strong>Total Records:</strong> " . $result->num_rows . "</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th class='table-header'>Appointment Name</th>
                    <th class='table-header'>Date</th>
                    <th class='table-header'>Time</th>
                    <th class='table-header'>Day of Week</th>
                </tr>
            </thead>
            <tbody>";

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $dayOfWeek = date('l', strtotime($row['date']));
                $html .= "<tr>
                            <td class='table-cell'><strong>".htmlspecialchars($row['appoint_name'])."</strong></td>
                            <td class='table-cell'>".date("d M Y", strtotime($row['date']))."</td>
                            <td class='table-cell'>".htmlspecialchars($row['time'])."</td>
                            <td class='table-cell'>$dayOfWeek</td>
                          </tr>";
            }
        } else {
            $html .= "<tr><td colspan='4' class='no-data'>No appointment records found matching the criteria</td></tr>";
        }
        
        $html .= "</tbody></table>
        <div class='footer'>
            <p>Generated by Professional CRM System | " . date('Y') . " | Confidential Business Report</p>
        </div>";

        $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
        $mpdf->WriteHTML($html);
        ob_end_clean();
        $mpdf->Output("Appointment_Report_".date('Y-m-d').".pdf", "D");
        exit;
    }

    // -------- INTERVIEW REPORT --------
    else {
        $query = "SELECT * FROM interview_details WHERE 1";
        if($from_date && $to_date) $query .= " AND interview_date BETWEEN '$from_date' AND '$to_date'";
        if($action != "") $query .= " AND status='$action'";
        $result = $con->query($query);

        $html = getProfessionalPDFStyles() . "
        <div class='header'>
            <h1>Interview Report</h1>
            <p>Professional Recruitment Management Dashboard</p>
        </div>
        
        <div class='report-meta'>
            <h3>Report Details</h3>
            <p><strong>Generated:</strong> " . date('d M Y, H:i A') . "</p>
            <p><strong>Date Range:</strong> " . ($dateRange ?: 'All Records') . "</p>
            <p><strong>Filter:</strong> " . ($action ? ucfirst($action) . ' Status' : 'All Statuses') . "</p>
            <p><strong>Total Records:</strong> " . $result->num_rows . "</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th class='table-header'>Candidate Name</th>
                    <th class='table-header'>Company Name</th>
                    <th class='table-header'>Position</th>
                    <th class='table-header'>Interview Date</th>
                    <th class='table-header'>Interview Time</th>
                    <th class='table-header'>Assigned Employee</th>
                    <th class='table-header'>Status</th>
                </tr>
            </thead>
            <tbody>";

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $statusClass = 'status-scheduled';
                $statusText = htmlspecialchars($row['status'] ?: 'Scheduled');
                
                if (strtolower($row['status']) == 'completed') {
                    $statusClass = 'status-completed';
                } elseif (strtolower($row['status']) == 'selected') {
                    $statusClass = 'status-selected';
                } elseif (strtolower($row['status']) == 'rejected') {
                    $statusClass = 'status-rejected';
                }

                $html .= "<tr>
                            <td class='table-cell'><strong>".htmlspecialchars(getCandidateName($row['candidate_id'], $con))."</strong></td>
                            <td class='table-cell'>".htmlspecialchars(getCompanyName($row['company_id'], $con))."</td>
                            <td class='table-cell'>".htmlspecialchars($row['role'] ?: 'Not Specified')."</td>
                            <td class='table-cell'>".htmlspecialchars($row['interview_date'])."</td>
                            <td class='table-cell'>".htmlspecialchars($row['interview_time'])."</td>
                            <td class='table-cell'>".htmlspecialchars(getEmployeeName($row['employee_id'], $con))."</td>
                            <td class='table-cell'><span class='status-badge $statusClass'>$statusText</span></td>
                          </tr>";
            }
        } else {
            $html .= "<tr><td colspan='7' class='no-data'>No interview records found matching the criteria</td></tr>";
        }
        
        $html .= "</tbody></table>
        <div class='footer'>
            <p>Generated by Professional CRM System | " . date('Y') . " | Confidential Business Report</p>
        </div>";

        $mpdf = new \Mpdf\Mpdf(['format' => 'A4-L']);
        $mpdf->WriteHTML($html);
        ob_end_clean();
        $mpdf->Output("Interview_Report_".date('Y-m-d').".pdf", "D");
        exit;
    }
}

// ---------- SUMMARY COUNTS ----------
$stmt = $con->prepare("SELECT COUNT(*) as total FROM interview_details WHERE DATE(interview_date) = ?");
$stmt->bind_param("s", $report_date);
$stmt->execute();
$interviews_count = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $con->prepare("SELECT COUNT(*) as total FROM candidate WHERE DATE(nextfollow_date) = ?");
$stmt->bind_param("s", $report_date);
$stmt->execute();
$candidates_count = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $con->prepare("SELECT COUNT(*) as total FROM company WHERE DATE(nextfollow_date) = ?");
$stmt->bind_param("s", $report_date);
$stmt->execute();
$clients_count = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $con->prepare("SELECT COUNT(*) as total FROM appointment WHERE DATE(date) = ?");
$stmt->bind_param("s", $report_date);
$stmt->execute();
$appointment_count = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// ---------- INTERVIEW DETAILS ----------
$sql = "SELECT c.name AS candidate_name, i.role, cl.company_name, i.interview_date, i.status, i.interview_time, i.employee_id
        FROM interview_details i
        JOIN candidate c ON i.candidate_id = c.id
        JOIN company cl ON i.company_id = cl.id
        WHERE DATE(i.interview_date) = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $report_date);
$stmt->execute();
$interviews = $stmt->get_result();
$stmt->close();

// ---------- CANDIDATE PROSPECTS ----------
$sql = "SELECT * FROM candidate WHERE DATE(nextfollow_date) = ? AND action='Prospect'";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $report_date);
$stmt->execute();
$candidates = $stmt->get_result();
$stmt->close();

// ---------- CLIENT PROSPECTS ----------
$sql = "SELECT * FROM company WHERE DATE(nextfollow_date) = ? AND action='Prospect'";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $report_date);
$stmt->execute();
$clients = $stmt->get_result();
$stmt->close();

// ---------- APPOINTMENTS ----------
$sql = "SELECT * FROM appointment WHERE DATE(date) = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $report_date);
$stmt->execute();
$appointments = $stmt->get_result();
$stmt->close();

include('header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Report - Professional Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #059669;
            --danger-color: #dc2626;
            --warning-color: #d97706;
            --light-bg: #f8fafc;
            --white: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .main-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-subtitle {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }

        .date-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-section {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .filter-title {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .date-filter {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border: none;
            border-radius: var(--radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #047857);
            border: none;
            border-radius: var(--radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-hover));
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .summary-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .summary-card-title {
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            margin: 0;
        }

        .summary-card-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .summary-card-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        .data-section {
            background: var(--white);
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-title {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.125rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-count {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .table-container {
            overflow-x: auto;
        }

        .professional-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .professional-table th {
            background: #f8fafc;
            color: var(--text-primary);
            font-weight: 600;
            padding: 1rem 0.75rem;
            text-align: left;
            border-bottom: 2px solid var(--border-color);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .professional-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-secondary);
        }

        .professional-table tbody tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .status-not-open {
            background: #fef3c7;
            color: #92400e;
        }

        .status-ni {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-success {
            background: #dcfce7;
            color: #16a34a;
        }

        .no-data {
            text-align: center;
            color: var(--text-secondary);
            font-style: italic;
            padding: 3rem;
            background: #f9fafb;
        }

        .modal-content {
            border-radius: var(--radius-lg);
            border: none;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 600;
            margin: 0;
        }

        .btn-close {
            filter: invert(1);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-select {
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
            outline: none;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .date-filter {
                flex-direction: column;
                align-items: stretch;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="main-container fade-in">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-chart-line"></i>
                Daily Business Report
            </h1>
            <p class="page-subtitle">Comprehensive business intelligence and performance analytics</p>
            <div class="date-info">
                <i class="fas fa-calendar-alt"></i>
                <span>Report Date: <?= date('l, F j, Y', strtotime($report_date)) ?></span>
            </div>
        </div>

        <!-- Date Filter Section -->
        <div class="filter-section">
            <h6 class="filter-title">
                <i class="fas fa-filter"></i>
                Report Filters
            </h6>
            <form method="get" class="date-filter">
                <div>
                    <label class="form-label">Select Date:</label>
                    <input type="date" name="report_date" value="<?= $report_date ?>" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Generate Report
                </button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#downloadModal">
                    <i class="fas fa-download"></i>
                    Download Report
                </button>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-header">
                    <div>
                        <h6 class="summary-card-title">Today's Interviews</h6>
                        <p class="summary-card-number"><?= $interviews_count ?></p>
                    </div>
                    <div class="summary-card-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-header">
                    <div>
                        <h6 class="summary-card-title">Candidate Prospects</h6>
                        <p class="summary-card-number"><?= $candidates_count ?></p>
                    </div>
                    <div class="summary-card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-header">
                    <div>
                        <h6 class="summary-card-title">Client Prospects</h6>
                        <p class="summary-card-number"><?= $clients_count ?></p>
                    </div>
                    <div class="summary-card-icon">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-card-header">
                    <div>
                        <h6 class="summary-card-title">Appointments</h6>
                        <p class="summary-card-number"><?= $appointment_count ?></p>
                    </div>
                    <div class="summary-card-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interview Details Section -->
        <div class="data-section">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="fas fa-handshake"></i>
                    Today's Interview Schedule
                    <span class="section-count"><?= $interviews->num_rows ?></span>
                </h5>
            </div>
            <div class="table-container">
                <table class="professional-table">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Position</th>
                            <th>Company</th>
                            <th>Interview Date</th>
                            <th>Time</th>
                            <th>Assigned Employee</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($interviews->num_rows > 0): ?>
                            <?php while($row = $interviews->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['candidate_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['role'] ?: 'Not Specified') ?></td>
                                    <td><?= htmlspecialchars($row['company_name']) ?></td>
                                    <td><?= date("d M Y", strtotime($row['interview_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['interview_time']) ?></td>
                                    <td><?= htmlspecialchars(getEmployeeName($row['employee_id'], $con)) ?></td>
                                    <td>
                                        <span class="status-badge status-success">
                                            <?= htmlspecialchars($row['status'] ?: 'Scheduled') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                    No interviews scheduled for today
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Candidate Prospects Section -->
        <div class="data-section">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="fas fa-user-graduate"></i>
                    Candidate Prospects
                    <span class="section-count"><?= $candidates->num_rows ?></span>
                </h5>
            </div>
            <div class="table-container">
                <table class="professional-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Qualification</th>
                            <th>Phone</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Assigned Employee</th>
                            <th>Follow-up Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($candidates->num_rows > 0): ?>
                            <?php while($row = $candidates->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['qulification']) ?></td>
                                    <td><?= htmlspecialchars($row['contact_number']) ?></td>
                                    <td><?= htmlspecialchars($row['categorization']) ?></td>
                                    <td>
                                        <?php if ($row['action'] == ''): ?>
                                            <span class="status-badge status-not-open">Not Opened</span>
                                        <?php elseif ($row['action'] == 'NI'): ?>
                                            <span class="status-badge status-ni">Not Interested</span>
                                        <?php else: ?>
                                            <span class="status-badge status-success"><?= htmlspecialchars($row['action']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars(getEmployeeName($row['employee_id'], $con)) ?></td>
                                    <td><?= date("d M Y", strtotime($row['nextfollow_date'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">
                                    <i class="fas fa-user-times fa-2x mb-2"></i><br>
                                    No candidate prospects for today
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Client Prospects Section -->
        <div class="data-section">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="fas fa-building-user"></i>
                    Client Prospects
                    <span class="section-count"><?= $clients->num_rows ?></span>
                </h5>
            </div>
            <div class="table-container">
                <table class="professional-table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Assigned Employee</th>
                            <th>Follow-up Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($clients->num_rows > 0): ?>
                            <?php while($row = $clients->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['company_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['contact_name']) ?></td>
                                    <td><?= htmlspecialchars($row['contact_number']) ?></td>
                                    <td><?= htmlspecialchars($row['location']) ?></td>
                                    <td>
                                        <span class="status-badge status-success">Prospect</span>
                                    </td>
                                    <td><?= htmlspecialchars(getEmployeeName($row['employee_id'], $con)) ?></td>
                                    <td><?= date("d M Y", strtotime($row['nextfollow_date'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">
                                    <i class="fas fa-building-slash fa-2x mb-2"></i><br>
                                    No client prospects for today
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Appointments Section -->
        <div class="data-section">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="fas fa-calendar-alt"></i>
                    Today's Appointments
                    <span class="section-count"><?= $appointments->num_rows ?></span>
                </h5>
            </div>
            <div class="table-container">
                <table class="professional-table">
                    <thead>
                        <tr>
                            <th>Appointment</th>
                            <th>Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($appointments->num_rows > 0): ?>
                            <?php while($row = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['appoint_name']) ?></strong></td>
                                    <td><?= date("d M Y", strtotime($row['date'])) ?></td>
                                    <td><?= htmlspecialchars($row['time']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="no-data">
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                    No appointments scheduled for today
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Download Modal -->
    <div class="modal fade" id="downloadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-download"></i>
                            Download Professional Report
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Report Type</label>
                                <select name="report_type" id="selectTopic" class="form-select" required>
                                    <option value="">Select Report Type</option>
                                    <option value="company">Company Details</option>
                                    <option value="candidate">Candidate Details</option>
                                    <option value="interview">Interview Report</option>
                                    <option value="appointment">Appointment Report</option>
                                </select>
                            </div>

                            <div id="companyList" class="col-md-6 mb-3 d-none">
                                <label class="form-label">Select Company</label>
                                <select name="company_id" class="form-select">
                                    <option value="">All Companies</option>
                                    <?php
                                    $res = $con->query("SELECT id, company_name FROM company ORDER BY company_name");
                                    while($row = $res->fetch_assoc()){
                                        echo "<option value='{$row['id']}'>{$row['company_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div id="candidateList" class="col-md-6 mb-3 d-none">
                                <label class="form-label">Select Candidate</label>
                                <select name="candidate_id" class="form-select">
                                    <option value="">All Candidates</option>
                                    <?php
                                    $res2 = $con->query("SELECT id, name FROM candidate ORDER BY name");
                                    while($row = $res2->fetch_assoc()){
                                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div id="actionBox" class="col-md-6 mb-3 d-none">
                                <label class="form-label">Status Filter</label>
                                <select name="action" id="actionSelect" class="form-select">
                                    <option value="">All Statuses</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">From Date</label>
                                <input type="date" name="from_date" class="form-control">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">To Date</label>
                                <input type="date" name="to_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="download_pdf">
                            <i class="fas fa-file-pdf"></i>
                            Download PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Predefined actions for different report types
        const actions = {
            company: [
                {value: "NI", text: "Not Interested"},
                {value: "Callback", text: "Callback"},
                {value: "Followup", text: "Follow-up"},
                {value: "Prospect", text: "Prospect"},
                {value: "Converted", text: "Converted"},
                {value: "", text: "Not Opened"}
            ],
            candidate: [
                {value: "NI", text: "Not Interested"},
                {value: "Callback", text: "Callback"},
                {value: "Followup", text: "Follow-up"},
                {value: "Prospect", text: "Prospect"},
                {value: "Converted", text: "Converted"},
                {value: "", text: "Not Opened"}
            ],
            interview: [
                {value: "Scheduled", text: "Scheduled"},
                {value: "Completed", text: "Completed"},
                {value: "Selected", text: "Selected"},
                {value: "Rejected", text: "Rejected"}
            ]
        };

        document.getElementById("selectTopic").addEventListener("change", function() {
            const topic = this.value;

            // Hide all dynamic sections
            document.getElementById("companyList").classList.add("d-none");
            document.getElementById("candidateList").classList.add("d-none");
            document.getElementById("actionBox").classList.add("d-none");

            // Show relevant sections based on selection
            if (topic === "company") {
                document.getElementById("companyList").classList.remove("d-none");
                document.getElementById("actionBox").classList.remove("d-none");
            } else if (topic === "candidate") {
                document.getElementById("candidateList").classList.remove("d-none");
                document.getElementById("actionBox").classList.remove("d-none");
            } else if (topic === "interview") {
                document.getElementById("actionBox").classList.remove("d-none");
            }

            // Populate action dropdown
            const actionSelect = document.getElementById("actionSelect");
            actionSelect.innerHTML = '<option value="">All Statuses</option>';
            
            if (actions[topic]) {
                actions[topic].forEach(opt => {
                    const option = document.createElement("option");
                    option.value = opt.value;
                    option.textContent = opt.text;
                    actionSelect.appendChild(option);
                });
            }
        });

        // Auto-refresh functionality
        setInterval(() => {
            const currentDate = new Date().toISOString().split('T')[0];
            const reportDate = document.querySelector('input[name="report_date"]').value;
            
            if (reportDate === currentDate) {
                // Only refresh if we're viewing today's report
                // You can implement auto-refresh logic here if needed
            }
        }, 300000); // Check every 5 minutes
    </script>
</body>
</html>