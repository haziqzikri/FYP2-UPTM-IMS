<?php
session_start();
if ($_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "IMS";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if (isset($_POST['submit'])) {
    $studentID = $_POST['studentID'];
    $offerLetter = file_get_contents($_FILES['offerLetter']['tmp_name']);
    $organizationReplyForm = file_get_contents($_FILES['organizationReplyForm']['tmp_name']);
    $informationSheet = file_get_contents($_FILES['informationSheet']['tmp_name']);

    $sql = "INSERT INTO GenerateLetter (OfferLetter, OrganizationReplyForm, InformationSheet, StudentID) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $offerLetter, $organizationReplyForm, $informationSheet, $studentID);

    if ($stmt->execute()) {
        echo "<script>alert('Documents uploaded successfully!');</script>";
    } else {
        echo "<script>alert('Error uploading documents: {$stmt->error}');</script>";
    }

    $stmt->close();
}

// Fetch student name for dynamic display
if (isset($_GET['studentID'])) {
    $studentID = $_GET['studentID'];
    $stmt = $conn->prepare("SELECT studentName FROM Student WHERE studentID = ?");
    $stmt->bind_param('s', $studentID);
    $stmt->execute();
    $stmt->bind_result($studentName);
    $stmt->fetch();
    echo json_encode(['name' => $studentName]);
    $stmt->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Letters</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            display: flex; 
            min-height: 100vh; 
            flex-direction: column; 
            background-color: #e9ecef;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            height: 100vh;
            position: fixed;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #495057;
        }
        .container {
            max-width: 600px;
            margin-left: 270px;
            margin-top: 60px;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .content { margin-left: 250px; padding: 20px; }
        .header {
            display: flex;
            justify-content: flex-end;
            padding: 10px 20px;
            background-color: #f8f9fa;
            margin-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
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

    <div class="container">
        <h3 class="text-center my-4">Upload Letters for Student</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="studentID" class="form-label">Student ID</label>
                <input type="text" name="studentID" class="form-control" id="studentID" required>
                <div id="studentName" class="mt-2 text-success"></div>
            </div>
            <div class="mb-3">
                <label for="offerLetter" class="form-label">Offer Letter</label>
                <input type="file" name="offerLetter" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="organizationReplyForm" class="form-label">Organization Reply Form</label>
                <input type="file" name="organizationReplyForm" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="informationSheet" class="form-label">Information Sheet</label>
                <input type="file" name="informationSheet" class="form-control" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary w-100">Upload Documents</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('studentID').addEventListener('input', function () {
        const studentID = this.value;
        if (studentID.trim() !== "") {
            fetch(`?studentID=${studentID}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('studentName').textContent = data.name ? `Student Name: ${data.name}` : 'Student not found';
                })
                .catch(error => {
                    document.getElementById('studentName').textContent = 'Error fetching student name';
                });
        } else {
            document.getElementById('studentName').textContent = '';
        }
    });
</script>
</body>
</html>
