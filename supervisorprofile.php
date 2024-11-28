<?php
// Start session to ensure the supervisor is logged in
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

// Fetch supervisor details using the logged-in supervisor's username (SupervisorID)
$supervisorID = $_SESSION['username'];

$supervisorSQL = "
    SELECT 
        Lecturer.LecturerName,
        Lecturer.LecturerEmail,
        Lecturer.LecturerPhoneNumber,
        Supervisor.SupervisorID,
        Lecturer.LecturerID
    FROM Supervisor
    JOIN Lecturer ON Supervisor.LecturerID = Lecturer.LecturerID
    WHERE Supervisor.SupervisorID = ?
";
$stmt = $conn->prepare($supervisorSQL);
$stmt->bind_param("s", $supervisorID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $supervisor = $result->fetch_assoc();
} else {
    echo "No supervisor found!";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            position: fixed;
            height: 100%;
            padding-top: 20px;
        }
        .sidebar h4 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link.active {
            background-color: #495057;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            background-color: #f8f9fa;
            flex-grow: 1;
        }
        .header {
            display: flex;
            justify-content: flex-end;
            padding: 10px 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 700px;
            margin: 3rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .profile-detail p {
            font-size: 16px;
            line-height: 1.6;
        }
        .profile-detail h4 {
            margin-bottom: 20px;
            font-size: 20px;
        }
        .profile-detail p strong {
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h4>Supervisor Dashboard</h4>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="supervisordashboard.php"><i class="bi bi-house-door-fill"></i> Home</a></li>
        <li class="nav-item"><a class="nav-link" href="evaluationform.php"><i class="bi bi-pencil-square"></i> Evaluation Form</a></li>
        <li class="nav-item"><a class="nav-link" href="logbookreview.php"><i class="bi bi-journal-text"></i> Logbook Review</a></li>
        <li class="nav-item"><a class="nav-link active" href="studentmarks.php"><i class="bi bi-bar-chart-line"></i> Student Marks</a></li>
    </ul>
</div>
<div class="content">
    <div class="header">
        <div class="dropdown">
            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i> Supervisor
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item" href="supervisorprofile.php">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Profile Details -->
    <div class="container">
        <h2>Supervisor Profile</h2>
        <div class="profile-detail">
            <h4>Personal Information</h4>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($supervisor['LecturerName']); ?></p>
            <p><strong>Lecturer ID:</strong> <?php echo htmlspecialchars($supervisor['LecturerID']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($supervisor['LecturerEmail']); ?></p>
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($supervisor['LecturerPhoneNumber']); ?></p>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
