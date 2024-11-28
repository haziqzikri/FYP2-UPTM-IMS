<?php
session_start();

// Database connection settings
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "IMS"; // Your database name

try {
    // Establish a PDO connection to the database
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get supervisor's ID from session
$supervisorID = $_SESSION['username']; // Assuming SupervisorID is stored in the session

// Fetch student names for the dropdown filter
$stmt = $pdo->prepare("SELECT StudentID, StudentName FROM student WHERE SupervisorID = :supervisorID");
$stmt->execute([':supervisorID' => $supervisorID]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize filter variables
$studentIDFilter = '';
if (isset($_GET['studentID'])) {
    $studentIDFilter = $_GET['studentID'];
}

// Fetch evaluation data based on the selected student filter
$stmt = $pdo->prepare("
    SELECT 
        EvaluateForm.StudentID, 
        Student.StudentName, 
        EvaluateForm.CompanyName, 
        EvaluateForm.PresentationMark, 
        EvaluateForm.IndustrialSupervisorEvaluationMark, 
        EvaluateForm.TotalMark
    FROM EvaluateForm
    INNER JOIN Student ON EvaluateForm.StudentID = Student.StudentID
    WHERE Student.SupervisorID = :supervisorID
    AND (:studentIDFilter = '' OR EvaluateForm.StudentID = :studentIDFilter)
");
$stmt->execute([
    ':supervisorID' => $supervisorID,
    ':studentIDFilter' => $studentIDFilter
]);
$evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Marks</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { display: flex; min-height: 100vh; flex-direction: column; }
        .sidebar { width: 250px; background-color: #343a40; color: #fff; position: fixed; height: 100%; }
        .sidebar .nav-link { color: #fff; }
        .sidebar .nav-link.active { background-color: #495057; }
        .content { margin-left: 250px; padding: 20px; background-color: #f8f9fa; flex-grow: 1; }
        .header { display: flex; justify-content: flex-end; padding: 10px 20px; background-color: #f8f9fa; }
        .filter-form { max-width: 300px; margin-bottom: 20px; }
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

    <h2>Student Marks</h2>
    
    <!-- Filter Form -->
    <form method="GET" action="" class="filter-form">
        <label for="studentID" class="form-label">Filter by Student</label>
        <select class="form-control" id="studentID" name="studentID">
            <option value="">All Students</option>
            <?php foreach ($students as $student): ?>
                <option value="<?php echo $student['StudentID']; ?>" <?php echo $student['StudentID'] == $studentIDFilter ? 'selected' : ''; ?>>
                    <?php echo $student['StudentName']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary mt-2">Filter</button>
    </form>

    <!-- Display Evaluations Table -->
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Company Name</th>
                <th>Presentation Mark</th>
                <th>Supervisor Evaluation Mark</th>
                <th>Total Mark</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($evaluations)): ?>
                <?php foreach ($evaluations as $evaluation): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($evaluation['StudentName']); ?></td>
                        <td><?php echo htmlspecialchars($evaluation['CompanyName']); ?></td>
                        <td><?php echo htmlspecialchars($evaluation['PresentationMark']); ?></td>
                        <td><?php echo htmlspecialchars($evaluation['IndustrialSupervisorEvaluationMark']); ?></td>
                        <td><?php echo htmlspecialchars($evaluation['TotalMark']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No evaluations found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
