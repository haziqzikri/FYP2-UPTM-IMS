<?php
session_start();
if ($_SESSION['user_role'] != 'student') {
    header("Location: login.php");
    exit();
}

$studentID = $_SESSION['username'];  // Assuming the session stores the student ID

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "IMS";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve letters from the database
$sql = "SELECT * FROM GenerateLetter WHERE StudentID = '$studentID'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetching the data only if a record is found
    $row = $result->fetch_assoc();
    $offerLetter = $row['OfferLetter'];
    $organizationReplyForm = $row['OrganizationReplyForm'];
    $informationSheet = $row['InformationSheet'];
} else {
    // No records found
    $offerLetter = $organizationReplyForm = $informationSheet = null;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Letters</title>
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
        .file-upload-icon {
            font-size: 1.5rem;
            margin-right: 0.5rem;
            color: #6c757d;
        }
        .message { font-size: 1.1rem; color: #dc3545; }
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

<!-- Main Content Area -->
<div class="content">
    <div class="header">
        <div class="dropdown">
            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i> Profile
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item" href="studentprofile.php">View Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="container">
        <h3 class="text-center mb-4">Your Letters</h3>

        <!-- Offer Letter Section -->
        <div class="d-flex justify-content-between align-items-center">
            <span>Offer Letter</span>
            <?php if ($offerLetter): ?>
                <a href="data:application/pdf;base64,<?php echo base64_encode($offerLetter); ?>" 
                   class="btn btn-success mb-3" download="OfferLetter.pdf">
                   <i class="bi bi-check-circle"></i> Download
                </a>
            <?php else: ?>
                <span class="text-danger"><i class="bi bi-x-circle"></i> Not Available</span>
            <?php endif; ?>
        </div>
        
        <!-- Organization Reply Form Section -->
        <div class="d-flex justify-content-between align-items-center">
            <span>Organization Reply Form</span>
            <?php if ($organizationReplyForm): ?>
                <a href="data:application/pdf;base64,<?php echo base64_encode($organizationReplyForm); ?>" 
                   class="btn btn-success mb-3" download="OrganizationReplyForm.pdf">
                   <i class="bi bi-check-circle"></i> Download
                </a>
            <?php else: ?>
                <span class="text-danger"><i class="bi bi-x-circle"></i> Not Available</span>
            <?php endif; ?>
        </div>
        
        <!-- Information Sheet Section -->
        <div class="d-flex justify-content-between align-items-center">
            <span>Information Sheet</span>
            <?php if ($informationSheet): ?>
                <a href="data:application/pdf;base64,<?php echo base64_encode($informationSheet); ?>" 
                   class="btn btn-success mb-3" download="InformationSheet.pdf">
                   <i class="bi bi-check-circle"></i> Download
                </a>
            <?php else: ?>
                <span class="text-danger"><i class="bi bi-x-circle"></i> Not Available</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
