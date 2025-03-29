<?php
require_once 'includes/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['student_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission';
    } else {
        try {
            $errors = [];
            $student_id = trim($_POST['student_id']);
            $name = trim($_POST['name']);
            $department = trim($_POST['department']);
            $year = (int)$_POST['year'];
            $semester = (int)$_POST['semester'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            // Validate student ID format (alphanumeric, no spaces)
            if (!preg_match('/^[A-Za-z0-9]+$/', $student_id)) {
                $errors[] = 'Student ID must contain only letters and numbers';
            }

            // Validate name (letters, spaces, and basic punctuation)
            if (!preg_match('/^[A-Za-z\s\'-]+$/', $name)) {
                $errors[] = 'Name can only contain letters, spaces, hyphens, and apostrophes';
            }

            // Validate department
            $departments = getDepartments();
            if (!array_key_exists($department, $departments)) {
                $errors[] = 'Invalid department selected';
            }

            // Validate year and semester
            if (!validateYear($year)) {
                $errors[] = 'Invalid year selected';
            }
            if (!validateSemester($semester)) {
                $errors[] = 'Invalid semester selected';
            }

            // Validate password
            if (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters long';
            }
            if ($password !== $confirm_password) {
                $errors[] = 'Passwords do not match';
            }

            // Check if student ID already exists
            $stmt = $dbh->prepare("SELECT COUNT(*) FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Student ID already exists';
            }

            if (empty($errors)) {
                // Insert new student
                $stmt = $dbh->prepare("
                    INSERT INTO students (student_id, name, department, year, semester, password) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $student_id,
                    $name,
                    $department,
                    $year,
                    $semester,
                    password_hash($password, PASSWORD_DEFAULT)
                ]);

                // Log successful registration
                logError("New student registered", [
                    'student_id' => $student_id,
                    'department' => $department,
                    'year' => $year,
                    'semester' => $semester
                ]);

                setFlashMessage('success', 'Registration successful! You can now login.');
                header('Location: login.php');
                exit();
            } else {
                $error = formatValidationErrors($errors);
            }
        } catch (Exception $e) {
            logError("Registration failed", ['error' => $e->getMessage()]);
            $error = 'An error occurred during registration. Please try again.';
        }
    }
}

// Get list of departments from config
$departments = getDepartments();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Feedback System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 500;
        }
        .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <h2 class="text-center mb-4">Student Registration</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <br>
                    <a href="login.php" class="alert-link">Click here to login</a>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="mb-3">
                    <label for="student_id" class="form-label">Student ID</label>
                    <input type="text" class="form-control" id="student_id" name="student_id" 
                           pattern="[A-Za-z0-9]+" required
                           value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>">
                    <div class="form-text">Only letters and numbers allowed, no spaces</div>
                </div>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required
                           pattern="[A-Za-z\s'-]+"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    <div class="form-text">Letters, spaces, hyphens, and apostrophes only</div>
                </div>
                
                <div class="mb-3">
                    <label for="department" class="form-label">Department</label>
                    <select class="form-select" id="department" name="department" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $code => $name): ?>
                            <option value="<?php echo htmlspecialchars($code); ?>"
                                    <?php echo (isset($_POST['department']) && $_POST['department'] === $code) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="row mb-3">
                    <div class="col">
                        <label for="year" class="form-label">Year</label>
                        <select class="form-select" id="year" name="year" required>
                            <option value="">Select Year</option>
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <option value="<?php echo $i; ?>"
                                        <?php echo (isset($_POST['year']) && (int)$_POST['year'] === $i) ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col">
                        <label for="semester" class="form-label">Semester</label>
                        <select class="form-select" id="semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <?php for ($i = 1; $i <= 2; $i++): ?>
                                <option value="<?php echo $i; ?>"
                                        <?php echo (isset($_POST['semester']) && (int)$_POST['semester'] === $i) ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           minlength="6" required>
                    <div class="form-text">Minimum 6 characters</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" 
                           name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            
            <div class="mt-3 text-center">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Form validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()

    // Password confirmation validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        if (this.value !== document.getElementById('password').value) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });

    document.getElementById('password').addEventListener('input', function() {
        var confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword.value !== this.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    });
    </script>
</body>
</html> 