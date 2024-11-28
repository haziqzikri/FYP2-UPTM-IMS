<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ims";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the user ID from session
$userId = $_SESSION['username'];

// Fetch total logbook entries for the user
$sql = "SELECT COUNT(*) as totalEntries FROM LogBook WHERE StudentID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalEntries = $row['totalEntries'];

// Assuming there are 14 weeks, each having 7 days to fill in, the total possible entries are 14 * 7 = 98
$totalPossibleEntries = 14 * 7;

// Calculate progress percentage
$logbookProgress = ($totalEntries / $totalPossibleEntries) * 100;
$logbookProgress = round($logbookProgress); // Round it to a whole number for the progress bar
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Report Submission</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            display: flex; 
            min-height: 100vh; 
            flex-direction: column; 
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
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
            margin-bottom: 1rem;
            text-align: center;
        }
        .sidebar .nav-link {
            color: #fff;
            font-size: 1rem;
            padding: 0.8rem 1rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #495057;
            border-radius: 5px;
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
        .welcome-message {
            font-size: 1.2rem;
            font-weight: 600;
            color: #495057;
        }
        .progress {
            height: 30px; /* Increased height for better visibility */
        }
        .progress-bar {
            font-weight: bold;
            font-size: 1.1rem; /* Making the percentage text larger */
            color: white; /* Ensure the text inside the progress bar is visible */
        }
        .progress-wrapper {
            display: flex;
            align-items: center;
        }
        .progress-text {
            font-size: 1.2rem;
            font-weight: bold;
            color: #495057;
            margin-left: 15px; /* Add some space between the progress bar and percentage text */
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-body {
            padding: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .card-title {
            font-size: 1.2rem;
        }
        .icon {
            font-size: 2rem;
            margin-right: 10px;
            color: #495057;
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
        <span class="welcome-message">Welcome, Student!</span>
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

    <div class="container">
        <h3 class="text-center mb-4 text-secondary">Progress Overview</h3>

        <!-- Logbook Submission Progress Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-journal-check icon"></i> Logbook Submission Progress
            </div>
            <div class="card-body">
                <div class="progress-wrapper">
                    <div class="progress" style="flex-grow: 1;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $logbookProgress ?>%;" aria-valuenow="<?= $logbookProgress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="progress-text"><?= $logbookProgress ?>%</div>
                </div>
            </div>
        </div>

        <!-- User Manual Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-book icon"></i> User Manual
            </div>
            <div class="card-body">
                <p class="card-title">Welcome to the Student Dashboard! Below is a brief guide to help you navigate through the system:</p>
                <ul>
                    <li><strong>Home</strong>: View your logbook progress and access other pages.</li>
                    <li><strong>Company</strong>: Fill details about your internship company.</li>
                    <li><strong>Letters</strong>: Download any letters uploaded by the admin.</li>
                    <li><strong>Logbook</strong>: Record your daily activities and progress throughout your internship.</li>
                    <li><strong>Profile</strong>: View Your Information details.</li>
                </ul>
                <p>If you have any questions or issues, feel free to contact the support team.</p>
            </div>
        </div>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
