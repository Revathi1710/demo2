<?php
include("connection.php");
include("header.php");
ini_set('display_errors',1);
if (isset($_POST['add'])) {
    $company_name   = mysqli_real_escape_string($con, $_POST['company_name']);
    $contact_number = mysqli_real_escape_string($con, $_POST['contact_number']);
    $email          = mysqli_real_escape_string($con, $_POST['email']);
    $contact_name   = mysqli_real_escape_string($con, $_POST['contact_name']);
    $location       = mysqli_real_escape_string($con, $_POST['location']);
    $status         = 1; // default active
    $category       = $_POST['categorization']; // set if needed
    $positions      = $_POST['position']; // This is an array

    if (!empty($positions) && is_array($positions)) {
        foreach ($positions as $position) {
            $position = mysqli_real_escape_string($con, $position);

            $sql = "INSERT INTO company (company_name, contact_number, email, contact_name, location, status, category, position)
                    VALUES ('$company_name', '$contact_number', '$email', '$contact_name', '$location', '$status', '$category', '$position')";
            mysqli_query($con, $sql);
        }
        echo "<script>alert('Company & positions added successfully'); window.location='allCompany.php';</script>";
    } else {
        echo "<script>alert('Please enter at least one position');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Company - Professional Dashboard</title>
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

        .position-container {
            background: #f8fafc;
            border: 2px dashed var(--border-color);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            transition: all 0.2s ease;
        }

        .position-container:hover {
            border-color: var(--primary-color);
            background: #f1f5f9;
        }

        .position-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .position-title {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-position-btn {
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .add-position-btn:hover {
            background: #047857;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .position-item {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            margin-bottom: 0.75rem;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .position-item:hover {
            box-shadow: var(--shadow-sm);
        }

        .position-input-group {
            display: flex;
            align-items: stretch;
        }

        .position-input {
            flex: 1;
            border: none;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            background: white;
        }

        .position-input:focus {
            outline: none;
            box-shadow: inset 0 0 0 2px var(--primary-color);
        }

        .remove-position-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
        }

        .remove-position-btn:hover {
            background: #b91c1c;
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

        .position-item {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="form-container fade-in">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-building"></i>
                    Add New Company
                </h1>
                <p class="page-subtitle">Create a comprehensive company profile with multiple positions</p>
            </div>

            <!-- Main Form -->
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-card">
                    <div class="form-card-header">
                        <h5>
                            <i class="fas fa-info-circle"></i>
                            Company Information
                        </h5>
                    </div>
                    
                    <div class="form-card-body">
                        <!-- Company Details Section -->
                        <div class="form-section">
                            <h6 class="section-title">
                                <i class="fas fa-building-user"></i>
                                Basic Information
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-building"></i>
                                        Company Name <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="company_name" 
                                           placeholder="Enter company name" required>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Contact Number <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="contact_number" 
                                           placeholder="Enter contact number" required>
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
                                        <i class="fas fa-user"></i>
                                        Contact Person <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="contact_name" 
                                           placeholder="Enter contact person name" required>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Location <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="location" 
                                           placeholder="Enter company location" required>
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

                        <!-- Positions Section -->
                        <div class="form-section">
                            <h6 class="section-title">
                                <i class="fas fa-briefcase"></i>
                                Available Positions
                            </h6>
                            
                            <div class="position-container">
                                <div class="position-header">
                                    <div class="position-title">
                                        <i class="fas fa-list"></i>
                                        Position List <span class="required-indicator">*</span>
                                    </div>
                                    <button type="button" class="add-position-btn" onclick="addPosition()">
                                        <i class="fas fa-plus"></i>
                                        Add Position
                                    </button>
                                </div>
                                
                                <div id="positionWrapper">
                                    <div class="position-item">
                                        <div class="position-input-group">
                                            <input type="text" class="position-input" name="position[]" 
                                                   placeholder="Enter position title" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="submit-section">
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="submit-btn" name="add">
                                    <i class="fas fa-save"></i>
                                    Save Company
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
        function addPosition() {
            const wrapper = document.getElementById("positionWrapper");
            const div = document.createElement("div");
            div.classList.add("position-item");
            div.innerHTML = `
                <div class="position-input-group">
                    <input type="text" class="position-input" name="position[]" 
                           placeholder="Enter position title" required>
                    <button type="button" class="remove-position-btn" onclick="removePosition(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            wrapper.appendChild(div);
        }

        function removePosition(button) {
            const positionItem = button.closest('.position-item');
            positionItem.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => {
                positionItem.remove();
            }, 300);
        }

        // Add slide out animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                to {
                    opacity: 0;
                    transform: translateX(-20px);
                }
            }
        `;
        document.head.appendChild(style);

        // Form validation enhancement
        document.querySelector('form').addEventListener('submit', function(e) {
            const positions = document.querySelectorAll('input[name="position[]"]');
            let hasValidPosition = false;
            
            positions.forEach(input => {
                if (input.value.trim() !== '') {
                    hasValidPosition = true;
                }
            });
            
            if (!hasValidPosition) {
                e.preventDefault();
                alert('Please enter at least one position');
                return false;
            }
        });

        // Auto-focus first input
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="company_name"]').focus();
        });
    </script>
</body>
</html>