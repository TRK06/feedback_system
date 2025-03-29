<?php
require_once 'includes/config.php';

try {
    // Clear existing admin users
    $dbh->exec("DELETE FROM admins");
    
    // Create new admin users with properly hashed passwords
    $admin1_username = 'admin';
    $admin1_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $admin2_username = 'admin2';
    $admin2_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Insert admin users
    $stmt = $dbh->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->execute([$admin1_username, $admin1_password]);
    $stmt->execute([$admin2_username, $admin2_password]);
    
    echo "Admin users created successfully!<br>";
    echo "Admin 1:<br>";
    echo "Username: " . $admin1_username . "<br>";
    echo "Password: admin123<br><br>";
    echo "Admin 2:<br>";
    echo "Username: " . $admin2_username . "<br>";
    echo "Password: admin123<br>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 