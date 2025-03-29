<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

// Get student info from session
$student_id = $_SESSION['student_id'];

// Get student details from database
try {
    $stmt = $dbh->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        header('Location: logout.php');
        exit();
    }
    
    // Update session with current student info
    $_SESSION['name'] = $student['name'];
    $_SESSION['department'] = $student['department'];
    $_SESSION['year'] = $student['year'];
    $_SESSION['semester'] = $student['semester'];
    
    // Get available subjects
    $stmt = $dbh->prepare("SELECT * FROM subjects WHERE department = ? AND year = ? AND semester = ?");
    $stmt->execute([$student['department'], $student['year'], $student['semester']]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get submitted feedback to disable already rated subjects
    $stmt = $dbh->prepare("SELECT DISTINCT subject_code FROM feedback WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $submitted_feedback = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Check for success message
$success = isset($_GET['success']) ? 'Feedback submitted successfully!' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Feedback System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            margin: 30px auto;
        }
        .subject-card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .subject-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Usharama College</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_submitted.php">View Submitted</a>
                    </li>
                </ul>
                <div class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
                </div>
                <a href="logout.php" class="btn btn-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container dashboard-container">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col">
                <h2>Your Subjects</h2>
                <p>
                    Department: <?php echo htmlspecialchars($student['department']); ?> | 
                    Year: <?php echo htmlspecialchars($student['year']); ?> | 
                    Semester: <?php echo htmlspecialchars($student['semester']); ?>
                </p>
            </div>
        </div>

        <div class="row">
            <?php if (empty($subjects)): ?>
                <div class="col">
                    <div class="alert alert-info">
                        No subjects available for your semester at this time.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($subjects as $subject): ?>
                    <div class="col-md-4">
                        <div class="card subject-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($subject['subject_name']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($subject['subject_code']); ?></h6>
                                <p class="card-text">Faculty: <?php echo htmlspecialchars($subject['faculty_name']); ?></p>
                                
                                <?php if (in_array($subject['subject_code'], $submitted_feedback)): ?>
                                    <button class="btn btn-success" disabled>Feedback Submitted</button>
                                <?php else: ?>
                                    <a href="submit_feedback.php?subject=<?php echo urlencode($subject['subject_code']); ?>" 
                                       class="btn btn-primary">Submit Feedback</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 