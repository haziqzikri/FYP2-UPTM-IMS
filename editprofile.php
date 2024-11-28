<?php
// Start session to ensure the student is logged in
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Database connection settings
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "IMS"; // Your database name

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in student's ID
$studentID = $_SESSION['username'];

// Fetch student details from database
$studentSQL = "SELECT * FROM Student WHERE StudentID = '$studentID'";
$studentResult = $conn->query($studentSQL);
$student = $studentResult->fetch_assoc();

// Fetch company details associated with the student
$companySQL = "
    SELECT c.CompanyName, c.CompanyEmail, c.CompanyAddress, c.State, c.IndustrialSV_Name, 
           c.IndustrialSV_PhoneNumber, c.IndustrialSV_Email
    FROM StudentCompany sc
    JOIN Company c ON sc.CompanyName = c.CompanyName
    WHERE sc.StudentID = '$studentID'
";
$companyResult = $conn->query($companySQL);
$company = $companyResult->fetch_assoc();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form inputs for student and company details
    $studentName = mysqli_real_escape_string($conn, $_POST['studentName']);
    $studentEmail = mysqli_real_escape_string($conn, $_POST['studentEmail']);
    $companyName = mysqli_real_escape_string($conn, $_POST['companyName']);
    $companyEmail = mysqli_real_escape_string($conn, $_POST['companyEmail']);
    $companyAddress = mysqli_real_escape_string($conn, $_POST['companyAddress']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $supervisorName = mysqli_real_escape_string($conn, $_POST['supervisorName']);
    $supervisorEmail = mysqli_real_escape_string($conn, $_POST['supervisorEmail']);
    $supervisorPhone = mysqli_real_escape_string($conn, $_POST['supervisorPhone']);
    
    // Update student details in the database
    $updateStudentSQL = "
        UPDATE Student
        SET StudentName = '$studentName', StudentEmail = '$studentEmail'
        WHERE StudentID = '$studentID'
    ";
    
    if ($conn->query($updateStudentSQL) === TRUE) {
        // If student info is updated, now update the company info
        $updateCompanySQL = "
            UPDATE Company
            SET CompanyEmail = '$companyEmail', CompanyAddress = '$companyAddress', 
                State = '$state', IndustrialSV_Name = '$supervisorName', 
                IndustrialSV_Email = '$supervisorEmail', IndustrialSV_PhoneNumber = '$supervisorPhone'
            WHERE CompanyName = '$companyName'
        ";
        
        if ($conn->query($updateCompanySQL) === TRUE) {
            // Success message
            $_SESSION['message'] = "Profile and company details updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating company details.";
        }
    } else {
        $_SESSION['error'] = "Error updating student details.";
    }

    // Redirect back to profile page with success or error message
    header("Location: studentprofile.php");
    exit();
}

// Close the database connection
$conn->close();
?>
