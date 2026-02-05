-- ============================================
-- SIMPLIFIED COURSEWORK MANAGEMENT DATABASE SCHEMA
-- ============================================

-- Table: Students
CREATE TABLE Students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: Rubric Components (Assessment criteria for Coursework 2)
CREATE TABLE RubricComponents (
    component_id INT PRIMARY KEY AUTO_INCREMENT,
    component_name VARCHAR(255) NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    max_marks DECIMAL(6,2) DEFAULT 100.00,
    display_order INT,
    coursework_id INT NOT NULL
);

-- Table: Student Coursework Submissions
CREATE TABLE Submissions (
    submission_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    graded_by INT DEFAULT NULL,
    graded_date DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES Students(student_id) ON DELETE CASCADE
);

-- Table: Component Grades (Individual marks and feedback for each rubric component)
CREATE TABLE ComponentGrades (
    grade_id INT PRIMARY KEY AUTO_INCREMENT,
    submission_id INT NOT NULL,
    component_id INT NOT NULL,
    marks_obtained DECIMAL(6,2),
    feedback TEXT,
    graded_date DATETIME DEFAULT NULL,
    FOREIGN KEY (submission_id) REFERENCES Submissions(submission_id) ON DELETE CASCADE,
    FOREIGN KEY (component_id) REFERENCES RubricComponents(component_id) ON DELETE CASCADE
);

-- Table: Teachers
CREATE TABLE Teachers (
    teacher_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255),
    status ENUM('marker', 'administrator') DEFAULT 'marker',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- SAMPLE DATA INSERTION
-- ============================================

-- Insert fixed set of students
INSERT INTO Students (name, email) VALUES
('Alice Johnson', 'alice.johnson@example.com'),
('Bob Smith', 'bob.smith@example.com'),
('Charlie Brown', 'charlie.brown@example.com');

-- Insert Coursework 2 Rubric Components
INSERT INTO RubricComponents (component_name, weight, max_marks, display_order, coursework_id) VALUES
('Introduction', 10.00, 100.00, 1, 2),
('SOC Roles & Incident Handling Reflection', 10.00, 100.00, 2, 2),
('Installation & Data Preparation', 15.00, 100.00, 3, 2),
('Guided Questions', 40.00, 100.00, 4, 2),
('Conclusion, References and Professional Presentation', 5.00, 100.00, 5, 2),
('Video Presentation', 10.00, 100.00, 6, 2),
('Continuous Improvement (Github Commits)', 10.00, 100.00, 7, 2);

-- Modify the graded_by column in Submissions to match the data type of teacher_id in Teachers
ALTER TABLE Submissions MODIFY COLUMN graded_by INT;

-- Add foreign key constraint to link graded_by in Submissions to teacher_id in Teachers
ALTER TABLE Submissions
ADD CONSTRAINT fk_graded_by_teacher FOREIGN KEY (graded_by) REFERENCES Teachers(teacher_id) ON DELETE SET NULL;

-- Trigger to generate ComponentGrades for all submissions when a new student is added
DELIMITER $$
CREATE TRIGGER after_student_insert
AFTER INSERT ON Students
FOR EACH ROW
BEGIN
    INSERT INTO Submissions (student_id, created_at, updated_at)
    VALUES (NEW.student_id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

    INSERT INTO ComponentGrades (submission_id, component_id, marks_obtained)
    SELECT s.submission_id, rc.component_id, 0
    FROM Submissions s
    JOIN RubricComponents rc ON rc.component_id = rc.component_id
    WHERE s.student_id = NEW.student_id;
END$$
DELIMITER ;
