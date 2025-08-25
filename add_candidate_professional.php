<?php
include("connection.php");
include("header.php");

// Function to generate new Unique ID
function getUniqueId() {
    global $con;

    $query = "SELECT unique_id FROM candidate ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        preg_match('/(\d+)$/', $row['unique_id'], $matches);
        $lastNumber = isset($matches[0]) ? (int)$matches[0] : 2999;
        $newNumber = $lastNumber + 1;
        return 'MPKC-' . $newNumber;
    } else {
        return 'MPKC-3000'; // Starting number
    }
}

if (isset($_POST['add'])) {
    $name           = mysqli_real_escape_string($con, $_POST['name']);
    $contact_number = mysqli_real_escape_string($con, $_POST['contact_number']);
    $email          = mysqli_real_escape_string($con, $_POST['email']);
    $location       = mysqli_real_escape_string($con, $_POST['location']);
    $qulification   = mysqli_real_escape_string($con, $_POST['qulification']);
    $categorization = mysqli_real_escape_string($con, $_POST['categorization']);
    $experience     = mysqli_real_escape_string($con, $_POST['experience']); // Fixed the variable error

    // Check if email or contact already exists
    $checkQuery = "SELECT * FROM candidate WHERE contact_number = '$contact_number' OR email = '$email'";
    $checkResult = mysqli_query($con, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>alert('Contact number or email already exists!');</script>";
    } else {
        $unique_id = getUniqueId();

        $query = "INSERT INTO candidate (unique_id, name, contact_number, email, location, qulification, categorization, experience)
                  VALUES ('$unique_id', '$name', '$contact_number', '$email', '$location', '$qulification', '$categorization', '$experience')";

        if (mysqli_query($con, $query)) {
            echo "<script>alert('Candidate added successfully! Unique ID: $unique_id'); window.location='allCandidate.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Candidate - Professional Dashboard</title>
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
            max-width: 1000px;
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

        .unique-id-display {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
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
            margin-bottom: 2rem;
        }

        .section-title {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
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

        .experience-badge {
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-secondary);
            display: inline-block;
            margin-top: 0.25rem;
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

        .form-floating {
            position: relative;
        }

        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            opacity: .65;
            transform: scale(.85) translateY(-0.5rem) translateX(0.15rem);
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
    </style>
</head>
<body>
    <div class="main-container">
        <div class="form-container fade-in">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-user-plus"></i>
                    Add New Candidate
                </h1>
                <p class="page-subtitle">Register a new candidate profile with comprehensive details</p>
                <div class="unique-id-display">
                    <i class="fas fa-id-card"></i>
                    Next Unique ID: <span id="nextId"><?php echo getUniqueId(); ?></span>
                </div>
            </div>

            <!-- Info Card -->
            <div class="info-card">
                <h6 class="info-card-title">
                    <i class="fas fa-info-circle"></i>
                    System Information
                </h6>
                <p class="info-card-text">
                    Each candidate will be automatically assigned a unique ID starting with MPKC-. 
                    The system will check for duplicate email addresses and contact numbers before registration.
                </p>
            </div>

            <!-- Main Form -->
            <form action="" method="post" enctype="multipart/form-data" id="candidateForm">
                <div class="form-card">
                    <div class="form-card-header">
                        <h5>
                            <i class="fas fa-user-circle"></i>
                            Candidate Information
                        </h5>
                    </div>
                    
                    <div class="form-card-body">
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <h6 class="section-title">
                                <i class="fas fa-id-badge"></i>
                                Personal Details
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i>
                                        Full Name <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="name" 
                                           placeholder="Enter candidate's full name" required>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Contact Number <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="contact_number" 
                                           placeholder="Enter contact number" required 
                                           pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number">
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Email Address <span class="required-indicator">*</span>
                                    </label>
                                    <input type="email" class="form-control" name="email" 
                                           placeholder="Enter email address" required>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Location <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="location" 
                                           placeholder="Enter current location" required>
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
                                        <input type="text" class="form-control" name="qulification" 
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
                                        <i class="fas fa-clock"></i>
                                        Experience Level <span class="required-indicator">*</span>
                                    </label>
                                    <select class="form-select" name="experience" required>
                                        <option value="">Select Experience Level</option>
                                        <option value="Fresher">Fresher</option>
                                        <option value="0-1 Years">0-1 Years</option>
                                        <option value="1-3 Years">1-3 Years</option>
                                        <option value="3-5 Years">3-5 Years</option>
                                        <option value="5-8 Years">5-8 Years</option>
                                        <option value="8 Above">8+ Years</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-tags"></i>
                                        Category <span class="required-indicator">*</span>
                                    </label>
                                    <select class="form-select" name="categorization" required>
                                        <option value="">Select Category</option>
                                        <option value="Production">Production</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Quality">Quality</option>
                                        <option value="IT">IT</option>
                                        <option value="Non-IT">Non-IT</option>
                                        <option value="Banking">Banking</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="submit-section">
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="submit-btn" name="add">
                                    <i class="fas fa-user-check"></i>
                                    Register Candidate
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

        // Form validation enhancement
        document.getElementById('candidateForm').addEventListener('submit', function(e) {
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
        });

        // Phone number formatting
        document.querySelector('input[name="contact_number"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.slice(0, 10);
            }
            e.target.value = value;
        });

        // Auto-focus first input
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="name"]').focus();
        });

        // Add loading state to submit button
        document.getElementById('candidateForm').addEventListener('submit', function() {
            const submitBtn = document.querySelector('.submit-btn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>