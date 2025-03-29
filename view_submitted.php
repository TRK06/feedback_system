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

// Get student's submitted feedback
try {
    $stmt = $dbh->prepare("
        SELECT f.*, s.subject_name, s.faculty_name 
        FROM feedback f 
        JOIN subjects s ON f.subject_code = s.subject_code 
        WHERE f.student_id = ? 
        ORDER BY f.submitted_at DESC
    ");
    $stmt->execute([$_SESSION['student_id']]);
    $feedback_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submitted Feedback - Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Usharama College</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="view_submitted.php">View Submitted</a>
                    </li>
                </ul>
                <div class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
                </div>
                <a href="logout.php" class="btn btn-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Your Submitted Feedback</h2>
        
        <?php if (empty($feedback_list)): ?>
            <div class="alert alert-info">
                You haven't submitted any feedback yet.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Faculty</th>
                            <th>Rating</th>
                            <th>Comments</th>
                            <th>Submitted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedback_list as $feedback): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($feedback['subject_name']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['faculty_name']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['rating']); ?>/5</td>
                                <td><?php echo htmlspecialchars($feedback['comments']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($feedback['submitted_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 