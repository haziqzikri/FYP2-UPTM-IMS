<?php
session_start();

// Database connection settings
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "IMS"; // Your database name

try {
    // Use the correct variable names: $servername and $username
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch Supervisor's Student List
$supervisorID = $_SESSION['username']; // Assuming SupervisorID is stored in the session

$stmt = $pdo->prepare("SELECT StudentID, StudentName FROM student WHERE SupervisorID = :supervisorID");
$stmt->execute([':supervisorID' => $supervisorID]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = $_POST['studentID'];
    $companyName = $_POST['companyName'];
    $presentationMark = $_POST['presentationMark'];
    $evaluationMark = $_POST['evaluationMark'];
    $totalMark = $presentationMark + $evaluationMark;

    // Insert or update the evaluation record
    $stmt = $pdo->prepare("
        INSERT INTO EvaluateForm (StudentID, CompanyName, PresentationMark, IndustrialSupervisorEvaluationMark, TotalMark)
        VALUES (:studentID, :companyName, :presentationMark, :evaluationMark, :totalMark)
        ON DUPLICATE KEY UPDATE
            CompanyName = :companyName,
            PresentationMark = :presentationMark,
            IndustrialSupervisorEvaluationMark = :evaluationMark,
            TotalMark = :totalMark
    ");
    $stmt->execute([
        ':studentID' => $studentID,
        ':companyName' => $companyName,
        ':presentationMark' => $presentationMark,
        ':evaluationMark' => $evaluationMark,
        ':totalMark' => $totalMark,
    ]);

    $message = "Evaluation saved successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Evaluation</title>
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
        .evaluation-form { max-width: 700px; margin: 0 auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .form-control { border-radius: 5px; }
        .btn { border-radius: 5px; }
        .btn-primary { margin-right: 10px; }
        .alert { margin-top: 20px; }
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

    <h2 class="text-center mb-4">Evaluate Student</h2>
    <div class="evaluation-form">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <!-- Student Dropdown -->
            <div class="mb-3">
                <label for="studentID" class="form-label">Select Student</label>
                <select class="form-control" id="studentID" name="studentID" required>
                    <option value="" disabled selected>Select a student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['StudentID']; ?>"><?php echo $student['StudentName']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="companyName" class="form-label">Company Name</label>
                <input type="text" class="form-control" id="companyName" name="companyName" placeholder="Enter Company Name" required>
            </div>
            <div class="mb-3">
                <label for="presentationMark" class="form-label">Presentation Mark</label>
                <input type="number" class="form-control" id="presentationMark" name="presentationMark" placeholder="Enter Presentation Mark" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="evaluationMark" class="form-label">Industrial Supervisor Evaluation Mark</label>
                <input type="number" class="form-control" id="evaluationMark" name="evaluationMark" placeholder="Enter Evaluation Mark" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="totalMark" class="form-label">Total Mark</label>
                <input type="text" class="form-control" id="totalMark" name="totalMark" placeholder="Calculated Automatically" readonly>
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-primary" onclick="calculateTotal()">Calculate Total</button>
                <button type="submit" class="btn btn-success">Submit Evaluation</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Function to calculate the total mark
    function calculateTotal() {
        const presentationMark = parseFloat(document.getElementById('presentationMark').value) || 0;
        const evaluationMark = parseFloat(document.getElementById('evaluationMark').value) || 0;
        const totalMark = presentationMark + evaluationMark;
        document.getElementById('totalMark').value = totalMark.toFixed(2);
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
