<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ims";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Login logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supervisorId = $_POST['SupervisorID']; // Assuming SupervisorID is used for login
    $password = $_POST['Password'];

    // Check if the supervisor exists and the password is correct
    $sql = "SELECT * FROM supervisor WHERE SupervisorID = ? AND SupervisorPassword = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $supervisorId, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['username'] = $supervisorId; // Store supervisor ID in session
        header("Location: supervisordashboard.php"); // Redirect to the dashboard
        exit();
    } else {
        $error = "Invalid login credentials.";
    }
}

// Dashboard content (only shown if the user is logged in)
if (isset($_SESSION['username'])) {
    // Get the supervisor's ID from the session (SupervisorID)
    $supervisorId = $_SESSION['username'];

    // Search query (optional, based on the user's input)
    $searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%%'; // Default to no search if not set

    // Fetch students supervised by the supervisor
    $sql = "
        SELECT student.StudentID, student.StudentName, student.StudentEmail, student.StudentPhoneNumber, student.StudentProgramme, student.StudentSemester
        FROM student
        INNER JOIN supervisor ON student.SupervisorID = supervisor.SupervisorID
        WHERE supervisor.SupervisorID = ? AND (student.StudentName LIKE ? OR student.StudentEmail LIKE ?)
    ";

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $supervisorId, $searchTerm, $searchTerm); // Bind supervisor ID and search term
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the data
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    // Close the connection
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard</title>
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
        .welcome-message {
            font-size: 18px;
            font-weight: bold;
        }
        .dashboard-content {
            margin-top: 30px;
        }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['username'])): ?>
    <!-- Login Form -->
    <div class="container mt-5">
        <h2>Supervisor Login</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="SupervisorID" class="form-label">Supervisor ID</label>
                <input type="text" class="form-control" id="SupervisorID" name="SupervisorID" required>
            </div>
            <div class="mb-3">
                <label for="Password" class="form-label">Password</label>
                <input type="password" class="form-control" id="Password" name="Password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
<?php else: ?>
    <!-- Dashboard Content -->
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

        <div class="dashboard-content">
            <h2>Welcome, Supervisor</h2>
            <p>Access your courses, assignments, and schedule.</p>

            <!-- Search Bar -->
            <form method="get" action="supervisordashboard.php">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Search Student by Name or Email" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>

            <!-- Display Students -->
            <div class="mt-4">
                <h5>Your Students:</h5>
                <?php if (empty($students)): ?>
                    <p>No students found or no matching results for your search.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Programme</th>
                                <th>Semester</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['StudentID']); ?></td>
                                    <td><?= htmlspecialchars($student['StudentName']); ?></td>
                                    <td><?= htmlspecialchars($student['StudentEmail']); ?></td>
                                    <td><?= htmlspecialchars($student['StudentPhoneNumber']); ?></td>
                                    <td><?= htmlspecialchars($student['StudentProgramme']); ?></td>
                                    <td><?= htmlspecialchars($student['StudentSemester']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
