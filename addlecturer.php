<?php
// Include database connection
include 'db_connection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['excelFile'])) {
        // Handle bulk upload via Excel file
        include 'upload_lecturers.php'; // Assumes this script handles the Excel import
    } else {
        // Handle single lecturer form submission
        $lecturerID = $_POST['lecturerID'];
        $lecturerName = $_POST['lecturerName'];
        $lecturerEmail = $_POST['lecturerEmail'];
        $lecturerPhone = $_POST['lecturerPhone'];

        // Prepare and execute SQL query
        $sql = "INSERT INTO Lecturer (LecturerID, LecturerName, LecturerEmail, LecturerPhoneNumber) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $lecturerID, $lecturerName, $lecturerEmail, $lecturerPhone);

        if ($stmt->execute()) {
            echo "<script>alert('Lecturer added successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Lecturer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { display: flex; min-height: 100vh; flex-direction: column; }
        .sidebar { width: 250px; background-color: #343a40; color: #fff; position: fixed; height: 100%; }
        .sidebar .nav-link { color: #fff; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #495057; }
        .content { margin-left: 250px; padding: 20px; background-color: #f8f9fa; flex-grow: 1; }
        .header { display: flex; justify-content: flex-end; padding: 10px 20px; background-color: #f8f9fa; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar p-3">
    <h4 class="text-center">Admin Dashboard</h4>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link active" href="admindashboard.php"><i class="bi bi-house-door-fill"></i> Dashboard</a></li>
        
        <!-- Student Dropdown -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#studentMenu" role="button" aria-expanded="false" aria-controls="studentMenu">
                <i class="bi bi-person-fill"></i> Student
            </a>
            <div class="collapse" id="studentMenu">
                <ul class="list-unstyled ps-3">
                    <li><a class="nav-link" href="addstudent.php"><i class="bi bi-person-plus-fill"></i> Add Student</a></li>
                    <li><a class="nav-link" href="liststudent.php"><i class="bi bi-list-ul"></i> List Students</a></li>
                    <li><a class="nav-link" href="listmarksstudent.php"><i class="bi bi-clipboard-data"></i> List Student Marks</a></li>
                </ul>
            </div>
        </li>

        <!-- Lecturer Dropdown -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#lecturerMenu" role="button" aria-expanded="false" aria-controls="lecturerMenu">
                <i class="bi bi-mortarboard-fill"></i> Lecturer
            </a>
            <div class="collapse" id="lecturerMenu">
                <ul class="list-unstyled ps-3">
                    <li><a class="nav-link" href="addlecturer.php"><i class="bi bi-person-plus-fill"></i> Add Lecturer</a></li>
                    <li><a class="nav-link" href="listlecturer.php"><i class="bi bi-list-ul"></i> List Lecturers</a></li>
                </ul>
            </div>
        </li>

        <!-- Supervisor Dropdown -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#supervisorMenu" role="button" aria-expanded="false" aria-controls="supervisorMenu">
                <i class="bi bi-people-fill"></i> Supervisor
            </a>
            <div class="collapse" id="supervisorMenu">
                <ul class="list-unstyled ps-3">
                    <li><a class="nav-link" href="assignsupervisor.php"><i class="bi bi-person-plus-fill"></i> Assign Supervisor</a></li>
                    <li><a class="nav-link" href="listsupervisor.php"><i class="bi bi-list-ul"></i> List Supervisors</a></li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item"><a class="nav-link" href="adminuploadletters.php"><i class="bi bi-envelope-fill"></i> Generate Letters</a></li>
    </ul>
</div>

<!-- Main Content Area -->
<div class="content">
    <!-- Header with Profile -->
    <div class="header">
        <div class="dropdown">
            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i> Admin
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>

    <h2>Add Lecturer</h2>
    <p>Fill out the form below to add a lecturer or upload an Excel file for bulk uploads.</p>

    <!-- Add Lecturer Form -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Add Lecturer Form</h5>
            <form action="addlecturer.php" method="POST">
                <div class="mb-3">
                    <label for="lecturerID" class="form-label">Lecturer ID</label>
                    <input type="text" class="form-control" id="lecturerID" name="lecturerID" required>
                </div>
                <div class="mb-3">
                    <label for="lecturerName" class="form-label">Lecturer Name</label>
                    <input type="text" class="form-control" id="lecturerName" name="lecturerName" required>
                </div>
                <div class="mb-3">
                    <label for="lecturerEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="lecturerEmail" name="lecturerEmail" required>
                </div>
                <div class="mb-3">
                    <label for="lecturerPhone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="lecturerPhone" name="lecturerPhone" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Lecturer</button>
            </form>
        </div>
    </div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
