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

// Fetch student details using the logged-in student's username (StudentID)
$studentID = $_SESSION['username'];

$studentSQL = "SELECT * FROM Student WHERE StudentID = '$studentID'";
$studentResult = $conn->query($studentSQL);
if ($studentResult->num_rows > 0) {
    $student = $studentResult->fetch_assoc();
} else {
    echo "No student found!";
    exit();
}

// Fetch company details associated with the student
$companySQL = "
    SELECT c.CompanyName, c.CompanyEmail, c.CompanyAddress, c.State, c.IndustrialSV_Name, 
           c.IndustrialSV_PhoneNumber, c.IndustrialSV_Email
    FROM StudentCompany sc
    JOIN Company c ON sc.CompanyName = c.CompanyName
    WHERE sc.StudentID = '$studentID'
";
$companyResult = $conn->query($companySQL);
$company = $companyResult->num_rows > 0 ? $companyResult->fetch_assoc() : null;

// Fetch academic supervisor details associated with the student
$supervisorSQL = "
    SELECT l.LecturerName, l.LecturerEmail, l.LecturerPhoneNumber
    FROM Supervisor s
    JOIN Lecturer l ON s.LecturerID = l.LecturerID
    WHERE s.SupervisorID = (SELECT SupervisorID FROM Student WHERE StudentID = '$studentID')
";
$supervisorResult = $conn->query($supervisorSQL);
$supervisor = $supervisorResult->num_rows > 0 ? $supervisorResult->fetch_assoc() : null;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            display: flex; 
            min-height: 100vh; 
            flex-direction: column; 
            background-color: #f8f9fa;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            position: fixed;
            height: 100vh;
            padding: 1rem;
        }
        .sidebar h4 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .sidebar .nav-link {
            color: #fff;
            font-size: 1rem;
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            border-radius: 5px;
        }
        .sidebar .nav-link i {
            margin-right: 0.6rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #495057;
        }
        .content { 
            margin-left: 270px; 
            padding: 20px; 
            flex-grow: 1; 
        }
        .container {
            max-width: 800px;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: auto;
        }
        .content { margin-left: 250px; padding: 20px; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #f8f9fa;
            margin-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        .profile-detail {
            margin-bottom: 20px;
        }
        .profile-detail h5 {
            margin-bottom: 10px;
            color: #343a40;
        }
        .modal-content {
            padding: 2rem;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h4>Student Dashboard</h4>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link active" href="studentdashboard.php"><i class="bi bi-house-door-fill"></i> Home</a></li>
        <li class="nav-item"><a class="nav-link" href="companydetail.php"><i class="bi bi-person-workspace"></i> Company</a></li>
        <li class="nav-item"><a class="nav-link" href="getletters.php"><i class="bi bi-envelope"></i> Letters</a></li>
        <li class="nav-item"><a class="nav-link" href="logbooksubmission.php"><i class="bi bi-journal-check"></i> Logbook</a></li>
    </ul>
</div>

<div class="content">
    <div class="header">
        <span class="welcome-message"></span>
        <div class="dropdown">
            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i> Student
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item" href="studentprofile.php">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>

<div class="content">
    <div class="container">
        <h3 class="text-center mb-4 text-secondary">Student Profile</h3>

        <!-- Student Details Section -->
        <div class="profile-detail">
            <h5>Personal Information</h5>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($student['StudentName']); ?></p>
            <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['StudentID']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($student['StudentEmail']); ?></p>
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($student['StudentPhoneNumber']); ?></p>
        </div>

        <!-- Company Details Section -->
        <div class="profile-detail">
            <h5>Company Information</h5>
            <?php if ($company): ?>
                <p><strong>Company Name:</strong> <?php echo htmlspecialchars($company['CompanyName']); ?></p>
                <p><strong>Company Email:</strong> <?php echo htmlspecialchars($company['CompanyEmail']); ?></p>
                <p><strong>Company Address:</strong> <?php echo htmlspecialchars($company['CompanyAddress']); ?></p>
                <p><strong>State:</strong> <?php echo htmlspecialchars($company['State']); ?></p>
                <p><strong>Supervisor's Name:</strong> <?php echo htmlspecialchars($company['IndustrialSV_Name']); ?></p>
                <p><strong>Supervisor's Email:</strong> <?php echo htmlspecialchars($company['IndustrialSV_Email']); ?></p>
                <p><strong>Supervisor's Phone:</strong> <?php echo htmlspecialchars($company['IndustrialSV_PhoneNumber']); ?></p>
            <?php else: ?>
                <p class="text-warning">No company details found. Please update your profile.</p>
            <?php endif; ?>
        </div>

        <!-- Academic Supervisor Details Section -->
        <div class="profile-detail">
            <h5>Academic Supervisor Information</h5>
            <?php if ($supervisor): ?>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($supervisor['LecturerName']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($supervisor['LecturerEmail']); ?></p>
                <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($supervisor['LecturerPhoneNumber']); ?></p>
            <?php else: ?>
                <p class="text-warning">No academic supervisor details found.</p>
            <?php endif; ?>
        </div>

        <!-- Edit Profile Button -->
        <div class="text-center">
            <!-- Trigger Edit Modal -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                Edit Profile
            </button>
        </div>

    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="editprofile.php" method="POST">
<!-- Add form fields for editing company details here -->
<div class="mb-3">
    <label for="companyName" class="form-label">Company Name</label>
    <input type="text" class="form-control" id="companyName" name="companyName" value="<?php echo htmlspecialchars($company['CompanyName']); ?>" readonly>
</div>
<div class="mb-3">
    <label for="companyEmail" class="form-label">Company Email</label>
    <input type="email" class="form-control" id="companyEmail" name="companyEmail" value="<?php echo htmlspecialchars($company['CompanyEmail']); ?>" required>
</div>
<div class="mb-3">
    <label for="companyAddress" class="form-label">Company Address</label>
    <textarea class="form-control" id="companyAddress" name="companyAddress" rows="3" required><?php echo htmlspecialchars($company['CompanyAddress']); ?></textarea>
</div>
<div class="mb-3">
    <label for="state" class="form-label">State</label>
    <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($company['State']); ?>" required>
</div>

<!-- Supervisor Details -->
<div class="mb-3">
    <label for="supervisorName" class="form-label">Supervisor's Name</label>
    <input type="text" class="form-control" id="supervisorName" name="supervisorName" value="<?php echo htmlspecialchars($company['IndustrialSV_Name']); ?>" required>
</div>
<div class="mb-3">
    <label for="supervisorEmail" class="form-label">Supervisor's Email</label>
    <input type="email" class="form-control" id="supervisorEmail" name="supervisorEmail" value="<?php echo htmlspecialchars($company['IndustrialSV_Email']); ?>" required>
</div>
<div class="mb-3">
    <label for="supervisorPhone" class="form-label">Supervisor's Phone</label>
    <input type="tel" class="form-control" id="supervisorPhone" name="supervisorPhone" value="<?php echo htmlspecialchars($company['IndustrialSV_PhoneNumber']); ?>" required>
</div>

                    <!-- Include fields for company and supervisor details as needed -->
                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
