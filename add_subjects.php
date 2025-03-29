<?php
include('includes/config.php');

try {
    // First, clear existing subjects
    $clearSql = "DELETE FROM subjects";
    $dbh->exec($clearSql);
    echo "Cleared existing subjects.<br>";

    // Add subjects for different semesters
    $subjects = array(
        // 3-2 Semester subjects
        array('Theory', 'Machine Learning', 'CS701', 'Dr. Anderson', 'CSE', 3, '3-2'),
        array('Theory', 'Compiler Design', 'CS702', 'Dr. Taylor', 'CSE', 3, '3-2'),
        array('Theory', 'Cryptography and Network Security', 'CS703', 'Dr. Martinez', 'CSE', 3, '3-2'),
        
        // 2-1 Semester subjects
        array('Theory', 'Data Structures', 'CS401', 'Dr. Wilson', 'CSE', 2, '2-1'),
        array('Theory', 'Computer Organisation', 'CS402', 'Dr. Brown', 'CSE', 2, '2-1'),
        array('Theory', 'Software Engineering', 'CS403', 'Dr. Davis', 'CSE', 2, '2-1'),
        array('Theory', 'Mathematical Foundations of Computer Science', 'CS404', 'Dr. Johnson', 'CSE', 2, '2-1'),
        
        // 2-2 Semester subjects
        array('Theory', 'Probability and Statistics', 'CS451', 'Dr. Smith', 'CSE', 2, '2-2'),
        array('Theory', 'DBMS', 'CS452', 'Dr. Lee', 'CSE', 2, '2-2'),
        array('Theory', 'Python', 'CS453', 'Dr. White', 'CSE', 2, '2-2'),
        array('Theory', 'DMGT', 'CS454', 'Dr. Black', 'CSE', 2, '2-2')
    );

    $sql = "INSERT INTO subjects (subjecttype, SubjectName, subjectcode, teachername, department, year, semester) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $dbh->prepare($sql);

    foreach ($subjects as $subject) {
        $stmt->execute($subject);
        echo "Added subject: " . $subject[1] . " (" . $subject[6] . ")<br>";
    }

    echo "<br>All subjects have been updated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 