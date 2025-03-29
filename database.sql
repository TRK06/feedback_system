-- Drop existing database and create new one
DROP DATABASE IF EXISTS feedback_system;
CREATE DATABASE feedback_system;
USE feedback_system;

-- Admin table
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin with password 'admin123'
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Students table
CREATE TABLE students (
    student_id VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department ENUM('CSE', 'AI', 'ECE', 'EEE', 'CIVIL', 'MECH') NOT NULL,
    year INT NOT NULL CHECK (year BETWEEN 1 AND 4),
    semester INT NOT NULL CHECK (semester IN (1, 2)),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subjects table
CREATE TABLE subjects (
    subject_code VARCHAR(10) PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    department ENUM('CSE', 'AI', 'ECE', 'EEE', 'CIVIL', 'MECH') NOT NULL,
    year INT NOT NULL CHECK (year BETWEEN 1 AND 4),
    semester INT NOT NULL CHECK (semester IN (1, 2)),
    faculty_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Parameters table
CREATE TABLE parameters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parameter_name VARCHAR(100) NOT NULL,
    category ENUM('Teaching', 'Subject', 'Lab', 'General') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Feedback table
CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(20) NOT NULL,
    subject_code VARCHAR(10) NOT NULL,
    parameter_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comments TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_code) REFERENCES subjects(subject_code) ON DELETE CASCADE,
    FOREIGN KEY (parameter_id) REFERENCES parameters(id) ON DELETE CASCADE,
    UNIQUE KEY unique_feedback (student_id, subject_code, parameter_id)
);

-- Suggestions table
CREATE TABLE suggestions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(20) NOT NULL,
    subject_code VARCHAR(10) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_code) REFERENCES subjects(subject_code) ON DELETE CASCADE
);

-- Insert default parameters
INSERT INTO parameters (parameter_name, category) VALUES
('Subject Knowledge', 'Teaching'),
('Communication Skills', 'Teaching'),
('Teaching Methodology', 'Teaching'),
('Class Control', 'Teaching'),
('Punctuality', 'Teaching'),
('Course Coverage', 'Subject'),
('Clarity of Course Objectives', 'Subject'),
('Quality of Study Material', 'Subject'),
('Lab Equipment Availability', 'Lab'),
('Lab Assistant Support', 'Lab'),
('Practical Exposure', 'Lab'),
('Overall Experience', 'General');

-- Insert sample subjects
INSERT INTO subjects (subject_code, subject_name, department, year, semester, faculty_name) VALUES
-- CSE Department
('CS101', 'Introduction to Programming', 'CSE', 1, 1, 'Dr. Smith'),
('CS102', 'Data Structures', 'CSE', 1, 2, 'Dr. Johnson'),
('CS201', 'Database Management Systems', 'CSE', 2, 1, 'Prof. Wilson'),
('CS202', 'Operating Systems', 'CSE', 2, 2, 'Dr. Brown'),

-- ECE Department
('EC101', 'Basic Electronics', 'ECE', 1, 1, 'Dr. Davis'),
('EC102', 'Digital Circuits', 'ECE', 1, 2, 'Prof. Miller'),
('EC201', 'Signals and Systems', 'ECE', 2, 1, 'Dr. Taylor'),
('EC202', 'Communication Systems', 'ECE', 2, 2, 'Prof. Anderson'),

-- EEE Department
('EE101', 'Electric Circuits', 'EEE', 1, 1, 'Dr. White'),
('EE102', 'Electromagnetic Theory', 'EEE', 1, 2, 'Prof. Clark'),

-- CIVIL Department
('CV101', 'Engineering Mechanics', 'CIVIL', 1, 1, 'Dr. Lewis'),
('CV102', 'Construction Materials', 'CIVIL', 1, 2, 'Prof. Hall'),

-- MECH Department
('ME101', 'Engineering Drawing', 'MECH', 1, 1, 'Dr. Young'),
('ME102', 'Thermodynamics', 'MECH', 1, 2, 'Prof. King'),

-- AI Department
('AI101', 'Introduction to AI', 'AI', 1, 1, 'Dr. Lee'),
('AI102', 'Machine Learning Basics', 'AI', 1, 2, 'Prof. Chen');
