<?php
session_start();

// Ensure the user is logged in as a supervisor
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ims";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the supervisor's ID from the session
$supervisorId = $_SESSION['username'];

// Fetch students under the supervisor
$students = [];
$sql = "
    SELECT s.StudentID, s.StudentName 
    FROM Student s
    JOIN StudentCompany sc ON s.StudentID = sc.StudentID
    WHERE s.SupervisorID = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supervisorId); // Assuming SupervisorID is INT
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Initialize logbook entries
$logbookEntries = [];

// Fetch logbook entries for a selected student
if (isset($_GET['studentId'])) {
    $studentId = $_GET['studentId'];

    $logbookSql = "SELECT * FROM LogBook WHERE StudentID = ? ORDER BY LBWeek ASC, LBDay ASC";
    $logbookStmt = $conn->prepare($logbookSql);
    $logbookStmt->bind_param("i", $studentId);
    $logbookStmt->execute();
    $logbookResult = $logbookStmt->get_result();

    while ($entry = $logbookResult->fetch_assoc()) {
        $logbookEntries[] = $entry;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logbook Review</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
        }
        .sidebar {
            background-color: #343a40;
            color: white;
            padding: 15px;
            height: 100vh;
            width: 250px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            margin-bottom: 10px;
            display: block;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
            padding: 5px;
            border-radius: 5px;
        }
        .content {
            padding: 20px;
            flex-grow: 1;
        }
        .header {
            display: flex;
            justify-content: flex-end;
            padding: 10px 20px;
            background-color: #f8f9fa;
        }
        .dropdown-menu-end {
            right: 0;
            left: auto;
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

    <h3 class="mb-4">Review Logbook Entries</h3>

    <form method="GET" action="logbookreview.php" class="mb-4">
        <label for="studentId" class="form-label">Select Student:</label>
        <select name="studentId" id="studentId" class="form-select" onchange="this.form.submit()">
            <option value="">-- Choose a Student --</option>
            <?php foreach ($students as $student): ?>
                <option value="<?= $student['StudentID'] ?>" <?= isset($_GET['studentId']) && $_GET['studentId'] == $student['StudentID'] ? 'selected' : '' ?>>
                    <?= $student['StudentName'] ?> (<?= $student['StudentID'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (!empty($logbookEntries)): ?>
        <h5>Logbook Entries for Student ID: <?= htmlspecialchars($_GET['studentId']) ?></h5>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
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
                        <td><?= htmlspecialchars($entry['LBActivity']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (isset($_GET['studentId'])): ?>
        <p>No logbook entries found for this student.</p>
    <?php else: ?>
        <p>Select a student to view their logbook entries.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
