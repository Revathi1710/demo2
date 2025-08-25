<?php
include('connection.php'); // Include your database connection
include('header.php');
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $employee_name = mysqli_real_escape_string($con, $_POST['employee_name']);
    $father_name = mysqli_real_escape_string($con, $_POST['father_name']);
    $mobile_number = mysqli_real_escape_string($con, $_POST['mobile_number']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $designation = mysqli_real_escape_string($con, $_POST['designation']); // Fixed: was using $role but form sends designation
    $dob = mysqli_real_escape_string($con, $_POST['dob']);
    $doj = mysqli_real_escape_string($con, $_POST['doj']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $altermobile_number = mysqli_real_escape_string($con, $_POST['altermobile_number']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $qualification = mysqli_real_escape_string($con, $_POST['qualification']);
    $aadhar_number = mysqli_real_escape_string($con, $_POST['aadhar_number']);

    // Check if mobile or email already exists
    $checkSql = "SELECT * FROM employees WHERE mobile_number = '$mobile_number' OR email = '$email'";
    $checkResult = mysqli_query($con, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>alert('Mobile number or Email already exists!'); window.history.back();</script>";
        exit;
    }

    // Unique ID generation for employees
    function getEmployeeUniqueId() {
        global $con;
        $query = "SELECT username FROM employees WHERE username LIKE 'MPKE-%' ORDER BY id DESC LIMIT 1";
        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            preg_match('/(\d+)$/', $row['username'], $matches);
            $lastNumber = isset($matches[0]) ? (int)$matches[0] : 2999;
            $newNumber = $lastNumber + 1;
            return 'MPKE-' . $newNumber;
        } else {
            return 'MPKE-3000';
        }
    }
    $username = getEmployeeUniqueId();

    // Generate random password
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = substr(str_shuffle($characters), 0, 8);

    // Insert data into database
    $sql = "INSERT INTO employees (employee_name, father_name, mobile_number, qualification, username, password, designation, altermobile_number, email, dob, doj, gender, permanentaddress, aadhar_number)
            VALUES ('$employee_name', '$father_name', '$mobile_number', '$qualification', '$username', '$password', '$designation', '$altermobile_number', '$email', '$dob', '$doj', '$gender', '$address', '$aadhar_number')";

    if ($con->query($sql) === TRUE) {
        echo "<script>alert('Employee added successfully!\\n\\nUsername: $username\\nPassword: $password\\n\\nPlease save these credentials safely.');</script>";
        echo '<script>window.location.href = "allEmployee.php";</script>';
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee - Professional Dashboard</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .main-container {
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .form-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .page-title {
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1.875rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .page-subtitle {
            color: var(--text-secondary);
            margin: 0.5rem 0 0 0;
            font-size: 1rem;
            font-weight: 400;
        }

        .credentials-info {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: var(--radius-md);
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .credentials-info i {
            font-size: 1.25rem;
        }

        .form-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .form-card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .form-card-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.125rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-card-body {
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2.5rem;
        }

        .section-title {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--primary-color);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .form-label i {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }

        .form-select {
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: var(--white);
            cursor: pointer;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
            outline: none;
        }

        .gender-options {
            display: flex;
            gap: 2rem;
            margin-top: 0.5rem;
        }

        .gender-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
            background: var(--white);
        }

        .gender-option:hover {
            border-color: var(--primary-color);
            background: #f8fafc;
        }

        .gender-option input[type="radio"] {
            margin: 0;
            width: 1.125rem;
            height: 1.125rem;
            accent-color: var(--primary-color);
        }

        .gender-option.selected {
            border-color: var(--primary-color);
            background: rgb(37 99 235 / 0.05);
        }

        .gender-option label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
            text-transform: none;
            letter-spacing: normal;
            font-size: 0.875rem;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .submit-section {
            background: #f8fafc;
            margin: 2rem -2rem -2rem;
            padding: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 0.875rem 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-width: 200px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgb(37 99 235 / 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .required-indicator {
            color: var(--danger-color);
            font-weight: 700;
        }

        .info-card {
            background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
            border: 1px solid #81d4fa;
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-card-title {
            color: #01579b;
            font-weight: 600;
            font-size: 0.875rem;
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-card-text {
            color: #0277bd;
            font-size: 0.8rem;
            margin: 0;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .form-card-body {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .submit-section {
                margin: 1.5rem -1.5rem -1.5rem;
                padding: 1.5rem;
            }

            .gender-options {
                flex-direction: column;
                gap: 0.75rem;
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

        .qualification-input {
            position: relative;
        }

        .qualification-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--border-color);
            border-top: none;
            border-radius: 0 0 var(--radius-md) var(--radius-md);
            box-shadow: var(--shadow-md);
            z-index: 1000;
            display: none;
            max-height: 200px;
            overflow-y: auto;
        }

        .qualification-suggestion {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
        }

        .qualification-suggestion:hover {
            background: #f8fafc;
        }

        .qualification-suggestion:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="form-container fade-in">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-user-tie"></i>
                    Add New Employee
                </h1>
                <p class="page-subtitle">Create a comprehensive employee profile with automatic credential generation</p>
                <div class="credentials-info">
                    <i class="fas fa-key"></i>
                    <span>System will automatically generate unique username and password for the new employee</span>
                </div>
            </div>

            <!-- Info Card -->
            <div class="info-card">
                <h6 class="info-card-title">
                    <i class="fas fa-info-circle"></i>
                    System Information
                </h6>
                <p class="info-card-text">
                    Each employee will receive a unique ID starting with MPKE- and an auto-generated 8-character password. 
                    The system prevents duplicate mobile numbers and email addresses.
                </p>
            </div>

            <!-- Main Form -->
            <form action="" method="post" id="employeeForm">
                <div class="form-card">
                    <div class="form-card-header">
                        <h5>
                            <i class="fas fa-user-circle"></i>
                            Employee Registration
                        </h5>
                    </div>
                    
                    <div class="form-card-body">
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <h6 class="section-title">
                                <i class="fas fa-id-badge"></i>
                                Personal Information
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i>
                                        Employee Name <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="employee_name" 
                                           placeholder="Enter employee's full name" required>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-user-friends"></i>
                                        Father's Name <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="father_name" 
                                           placeholder="Enter father's name" required>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-venus-mars"></i>
                                        Gender <span class="required-indicator">*</span>
                                    </label>
                                    <div class="gender-options">
                                        <div class="gender-option">
                                            <input type="radio" id="male" name="gender" value="Male" required>
                                            <label for="male">Male</label>
                                        </div>
                                        <div class="gender-option">
                                            <input type="radio" id="female" name="gender" value="Female" required>
                                            <label for="female">Female</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt"></i>
                                        Date of Birth <span class="required-indicator">*</span>
                                    </label>
                                    <input type="date" class="form-control" name="dob" required>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-id-card"></i>
                                        Aadhar Number <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="aadhar_number" 
                                           placeholder="Enter 12-digit Aadhar number" required
                                           pattern="[0-9]{12}" title="Please enter a valid 12-digit Aadhar number">
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Address <span class="required-indicator">*</span>
                                    </label>
                                    <textarea class="form-control" name="address" 
                                              placeholder="Enter permanent address" required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Section -->
                        <div class="form-section">
                            <h6 class="section-title">
                                <i class="fas fa-address-book"></i>
                                Contact Information
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Mobile Number <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="mobile_number" 
                                           placeholder="Enter primary mobile number" required
                                           pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number">
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-phone-alt"></i>
                                        Alternate Mobile Number
                                    </label>
                                    <input type="text" class="form-control" name="altermobile_number" 
                                           placeholder="Enter alternate mobile number"
                                           pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number">
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Email Address
                                    </label>
                                    <input type="email" class="form-control" name="email" 
                                           placeholder="Enter email address">
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information Section -->
                        <div class="form-section">
                            <h6 class="section-title">
                                <i class="fas fa-briefcase"></i>
                                Professional Details
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-graduation-cap"></i>
                                        Qualification <span class="required-indicator">*</span>
                                    </label>
                                    <div class="qualification-input">
                                        <input type="text" class="form-control" name="qualification" 
                                               placeholder="Enter highest qualification" required
                                               id="qualificationInput" autocomplete="off">
                                        <div class="qualification-suggestions" id="qualificationSuggestions">
                                            <div class="qualification-suggestion" data-value="Bachelor of Engineering (BE)">Bachelor of Engineering (BE)</div>
                                            <div class="qualification-suggestion" data-value="Bachelor of Technology (BTech)">Bachelor of Technology (BTech)</div>
                                            <div class="qualification-suggestion" data-value="Master of Engineering (ME)">Master of Engineering (ME)</div>
                                            <div class="qualification-suggestion" data-value="Master of Technology (MTech)">Master of Technology (MTech)</div>
                                            <div class="qualification-suggestion" data-value="Bachelor of Computer Applications (BCA)">Bachelor of Computer Applications (BCA)</div>
                                            <div class="qualification-suggestion" data-value="Master of Computer Applications (MCA)">Master of Computer Applications (MCA)</div>
                                            <div class="qualification-suggestion" data-value="Bachelor of Science (BSc)">Bachelor of Science (BSc)</div>
                                            <div class="qualification-suggestion" data-value="Master of Science (MSc)">Master of Science (MSc)</div>
                                            <div class="qualification-suggestion" data-value="Bachelor of Commerce (BCom)">Bachelor of Commerce (BCom)</div>
                                            <div class="qualification-suggestion" data-value="Master of Business Administration (MBA)">Master of Business Administration (MBA)</div>
                                            <div class="qualification-suggestion" data-value="Diploma">Diploma</div>
                                            <div class="qualification-suggestion" data-value="ITI">ITI</div>
                                            <div class="qualification-suggestion" data-value="12th Pass">12th Pass</div>
                                            <div class="qualification-suggestion" data-value="10th Pass">10th Pass</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-user-tag"></i>
                                        Designation <span class="required-indicator">*</span>
                                    </label>
                                    <select name="designation" class="form-select" required>
                                        <option value="">Select Designation</option>
                                        <option value="Manager">Manager</option>
                                        <option value="Project Manager">Project Manager</option>
                                        <option value="Team Leader">Team Leader</option>
                                        <option value="Assistant Manager">Assistant Manager</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Developer">Developer</option>
                                        <option value="Digital Marketing">Digital Marketing</option>
                                        <option value="Sales">Sales</option>
                                        <option value="HR">HR</option>
                                        <option value="Intern">Intern</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-check"></i>
                                        Date of Joining <span class="required-indicator">*</span>
                                    </label>
                                    <input type="date" class="form-control" name="doj" required>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="submit-section">
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="submit-btn" name="add">
                                    <i class="fas fa-user-plus"></i>
                                    Add Employee
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gender selection styling
        document.querySelectorAll('input[name="gender"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.gender-option').forEach(option => {
                    option.classList.remove('selected');
                });
                this.closest('.gender-option').classList.add('selected');
            });
        });

        // Qualification autocomplete functionality
        const qualificationInput = document.getElementById('qualificationInput');
        const qualificationSuggestions = document.getElementById('qualificationSuggestions');
        const suggestions = document.querySelectorAll('.qualification-suggestion');

        qualificationInput.addEventListener('input', function() {
            const value = this.value.toLowerCase();
            let hasVisibleSuggestions = false;

            suggestions.forEach(suggestion => {
                const text = suggestion.textContent.toLowerCase();
                if (text.includes(value) && value.length > 0) {
                    suggestion.style.display = 'block';
                    hasVisibleSuggestions = true;
                } else {
                    suggestion.style.display = 'none';
                }
            });

            qualificationSuggestions.style.display = hasVisibleSuggestions ? 'block' : 'none';
        });

        suggestions.forEach(suggestion => {
            suggestion.addEventListener('click', function() {
                qualificationInput.value = this.dataset.value;
                qualificationSuggestions.style.display = 'none';
            });
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!qualificationInput.contains(e.target) && !qualificationSuggestions.contains(e.target)) {
                qualificationSuggestions.style.display = 'none';
            }
        });

        // Phone number formatting
        document.querySelectorAll('input[pattern="[0-9]{10}"]').forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 10) {
                    value = value.slice(0, 10);
                }
                e.target.value = value;
            });
        });

        // Aadhar number formatting
        document.querySelector('input[name="aadhar_number"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 12) {
                value = value.slice(0, 12);
            }
            e.target.value = value;
        });

        // Form validation enhancement
        document.getElementById('employeeForm').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }

            // Show loading state
            const submitBtn = document.querySelector('.submit-btn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
        });

        // Set default date constraints
        document.addEventListener('DOMContentLoaded', function() {
            // Set max date for DOB (18 years ago)
            const dobInput = document.querySelector('input[name="dob"]');
            const eighteenYearsAgo = new Date();
            eighteenYearsAgo.setFullYear(eighteenYearsAgo.getFullYear() - 18);
            dobInput.max = eighteenYearsAgo.toISOString().split('T')[0];

            // Set default DOJ to today
            const dojInput = document.querySelector('input[name="doj"]');
            const today = new Date().toISOString().split('T')[0];
            dojInput.value = today;

            // Auto-focus first input
            document.querySelector('input[name="employee_name"]').focus();
        });
    </script>
</body>
</html>