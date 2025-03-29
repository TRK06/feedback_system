<?php
require_once 'includes/config.php';
requireLogin();

$student_id = $_SESSION['student_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission';
    } else {
        try {
            $message = trim($_POST['message']);
            
            if (empty($message)) {
                throw new Exception('Please enter your suggestion/feedback.');
            }
            
            $stmt = $dbh->prepare("INSERT INTO suggestions (student_id, message) VALUES (?, ?)");
            $stmt->execute([$student_id, $message]);
            
            $success = 'Thank you for your feedback!';
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Feedback System - Suggestions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .suggestions-container {
            margin: 30px auto;
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="suggestions.php">Suggestions</a>
                    </li>
                </ul>
                <div class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
                </div>
                <a href="logout.php" class="btn btn-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container suggestions-container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Submit Your Suggestions</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Feedback/Suggestions</label>
                                <textarea class="form-control" id="message" name="message" rows="5" 
                                          placeholder="Please share your thoughts, suggestions, or any general feedback about the college, facilities, or curriculum." 
                                          required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Submit Suggestion</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 