<?php
require_once 'includes/config.php';
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

try {
    // Get student info
    $stmt = $dbh->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$_SESSION['student_id']]);
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

    // Get the subject details if subject_code is in URL
    if (!isset($_GET['subject'])) {
        header('Location: dashboard.php');
        exit();
    }

    // Verify the subject exists and belongs to student's department/year/semester
    $stmt = $dbh->prepare("
        SELECT * FROM subjects 
        WHERE subject_code = ? 
        AND department = ? 
        AND year = ? 
        AND semester = ?
    ");
    $stmt->execute([
        $_GET['subject'],
        $student['department'],
        $student['year'],
        $student['semester']
    ]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subject) {
        setFlashMessage('error', 'Invalid subject selected');
        header('Location: dashboard.php');
        exit();
    }

    // Check if feedback already submitted
    $stmt = $dbh->prepare("
        SELECT COUNT(*) FROM feedback 
        WHERE student_id = ? AND subject_code = ?
    ");
    $stmt->execute([$_SESSION['student_id'], $subject['subject_code']]);
    if ($stmt->fetchColumn() > 0) {
        setFlashMessage('error', 'You have already submitted feedback for this subject');
        header('Location: dashboard.php');
        exit();
    }

    // Get feedback parameters
    $stmt = $dbh->query("SELECT * FROM parameters ORDER BY category, parameter_name");
    $parameters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group parameters by category
    $grouped_parameters = [];
    foreach ($parameters as $param) {
        $grouped_parameters[$param['category']][] = $param;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $error = 'Invalid form submission';
        } else {
            try {
                $dbh->beginTransaction();

                // Validate and insert feedback
                foreach ($_POST['ratings'] as $parameter_id => $rating) {
                    if (!validateRating($rating)) {
                        throw new Exception('Invalid rating value');
                    }
                    
                    $stmt = $dbh->prepare("
                        INSERT INTO feedback (student_id, subject_code, parameter_id, rating) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $_SESSION['student_id'],
                        $subject['subject_code'],
                        $parameter_id,
                        $rating
                    ]);
                }

                // Insert suggestion if provided
                if (!empty($_POST['suggestion'])) {
                    $stmt = $dbh->prepare("
                        INSERT INTO suggestions (student_id, subject_code, message) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([
                        $_SESSION['student_id'],
                        $subject['subject_code'],
                        trim($_POST['suggestion'])
                    ]);
                }

                $dbh->commit();
                setFlashMessage('success', 'Feedback submitted successfully!');
                header('Location: dashboard.php');
                exit();

            } catch (Exception $e) {
                $dbh->rollBack();
                logError("Feedback submission error", ['error' => $e->getMessage()]);
                $error = 'An error occurred while submitting feedback';
            }
        }
    }
} catch (Exception $e) {
    logError("Page error", ['error' => $e->getMessage()]);
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback - <?php echo htmlspecialchars($subject['subject_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Student Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Submit Feedback</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_submitted.php">View Submitted</a>
                    </li>
                </ul>
                <div class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars($student['name']); ?>
                </div>
                <a href="logout.php" class="btn btn-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Submit Feedback</h2>
        
        <div class="alert alert-info">
            <strong>Subject:</strong> <?php echo htmlspecialchars($subject['subject_name']); ?> 
            (<?php echo htmlspecialchars($subject['subject_code']); ?>)<br>
            <strong>Faculty:</strong> <?php echo htmlspecialchars($subject['faculty_name']); ?>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="mb-4">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <?php foreach ($grouped_parameters as $category => $params): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo htmlspecialchars($category); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($params as $param): ?>
                            <div class="mb-3">
                                <label class="form-label"><?php echo htmlspecialchars($param['parameter_name']); ?></label>
                                <div class="btn-group w-100" role="group">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" class="btn-check" name="ratings[<?php echo $param['id']; ?>]" 
                                               id="rating_<?php echo $param['id']; ?>_<?php echo $i; ?>" 
                                               value="<?php echo $i; ?>" required>
                                        <label class="btn btn-outline-primary" 
                                               for="rating_<?php echo $param['id']; ?>_<?php echo $i; ?>"><?php echo $i; ?></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Additional Feedback</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="suggestion" class="form-label">Suggestions (Optional)</label>
                        <textarea class="form-control" id="suggestion" name="suggestion" rows="3" 
                                  placeholder="Enter your suggestions for improvement"></textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Submit Feedback</button>
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 