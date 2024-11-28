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

// Fetch the company name related to the student
$companyName = '';
$sql = "SELECT CompanyName FROM StudentCompany WHERE StudentID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();
if ($company) {
    $companyName = $company['CompanyName']; // Get company name from StudentCompany table
}

// Handle form submission for saving a logbook entry
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $week = $_POST['week'];
    $day = $_POST['day'];
    $date = $_POST['date'];
    $activity = $_POST['activity'];

    // Insert logbook entry into the database
    $insertSql = "INSERT INTO LogBook (LBWeek, LBDate, LBActivity, CompanyName, StudentID, LBDay) VALUES (?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("isssss", $week, $date, $activity, $companyName, $userId, $day);
    $insertStmt->execute();
}

// Initialize the array to store logbook entries
$logbookEntries = [];

// Fetch previous logbook entries for the user, sorted by week and day
$sql = "SELECT * FROM LogBook WHERE StudentID = ? ORDER BY LBWeek ASC, LBDay ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Check if there are results and populate the array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $logbookEntries[] = $row;
    }
}

// Function to check if a logbook entry already exists for the given week and day
function isDayFilled($week, $day, $logbookEntries) {
    foreach ($logbookEntries as $entry) {
        if ($entry['LBWeek'] == $week && $entry['LBDay'] == $day) {
            return true;
        }
    }
    return false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logbook Submission</title>
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
        .content {
            margin-left: 250px;
            padding: 20px;
        }
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
        .container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container {
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .logbook-table th, .logbook-table td {
            text-align: center;
        }
        .logbook-table tbody tr:hover {
            background-color: #f1f1f1;
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
        <h3 class="text-center mb-4 text-secondary">Logbook Submission</h3>

        <!-- Accordion for Weeks -->
        <div class="row">
            <!-- Left Column: Weeks Accordion -->
            <div class="col-md-4">
                <div class="accordion" id="logbookAccordion">
                    <?php for ($i = 1; $i <= 14; $i++): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="week<?= $i ?>Heading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#week<?= $i ?>Collapse" aria-expanded="false" aria-controls="week<?= $i ?>Collapse">
                                    Week <?= $i ?>
                                </button>
                            </h2>
                            <div id="week<?= $i ?>Collapse" class="accordion-collapse collapse" aria-labelledby="week<?= $i ?>Heading" data-bs-parent="#logbookAccordion">
                                <div class="accordion-body">
                                    <?php for ($day = 1; $day <= 7; $day++): ?>
                                        <?php if (isDayFilled($i, $day, $logbookEntries)): ?>
                                            <button class="btn btn-outline-secondary mb-2" disabled>
                                                Day <?= $day ?> (Filled)
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary mb-2" onclick="showForm(<?= $i ?>, <?= $day ?>)">
                                                Day <?= $day ?>
                                            </button>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Right Column: Logbook Submission Form -->
            <div class="col-md-8">
                <div class="form-container">
                    <form method="POST" action="logbooksubmission.php">
                        <h5 class="mb-4 text-center">Submit Your Logbook Entry</h5>

                        <!-- Hidden inputs for Week and Day -->
                        <input type="hidden" id="week" name="week" value="">
                        <input type="hidden" id="day" name="day" value="">

                        <!-- Display Week and Day -->
                        <div class="mb-3">
                            <label for="weekDisplay" class="form-label">Week</label>
                            <input type="text" class="form-control" id="weekDisplay" value="" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="dayDisplay" class="form-label">Day</label>
                            <input type="text" class="form-control" id="dayDisplay" value="" readonly>
                        </div>

                        <!-- Pre-fill the form if there is an existing entry for the selected week and day -->
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="activity" class="form-label">Activity</label>
                            <textarea class="form-control" id="activity" name="activity" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Entry</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Logbook Entries Table -->
        <h5 class="mt-5 text-center">Previous Logbook Entries</h5>
        <table class="table table-bordered logbook-table">
            <thead>
                <tr>
                    <th>Week</th>
                    <th>Day</th>
                    <th>Date</th>
                    <th>Activity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logbookEntries as $entry): ?>
                    <tr>
                        <td><?= $entry['LBWeek'] ?></td>
                        <td><?= $entry['LBDay'] ?></td>
                        <td><?= $entry['LBDate'] ?></td>
                        <td><?= $entry['LBActivity'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
    function showForm(week, day) {
        // Set the form week and day dynamically
        document.getElementById('week').value = week;
        document.getElementById('day').value = day;
        document.getElementById('weekDisplay').value = 'Week ' + week;
        document.getElementById('dayDisplay').value = 'Day ' + day;
    }
</script>

</body>
</html>
