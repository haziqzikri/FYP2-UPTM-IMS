<?php
// Include database connection
include 'db_connection.php';

// Fetch data for lecturers and students
$lecturers = $conn->query("SELECT LecturerID, LecturerName FROM Lecturer");
$students = $conn->query("SELECT StudentID, StudentName FROM Student WHERE SupervisorID IS NULL");

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $studentID = $_POST['studentID'];
    $lecturerID = $_POST['lecturerID'];

    // Check if lecturer is already a supervisor
    $sql_check = "SELECT SupervisorID FROM Supervisor WHERE LecturerID = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $lecturerID);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Lecturer is already a supervisor, retrieve SupervisorID
        $stmt_check->bind_result($supervisorID);
        $stmt_check->fetch();
    } else {
        // Lecturer is not a supervisor, create a new supervisor entry
        $sql_supervisor = "INSERT INTO Supervisor (LecturerID) VALUES (?)";
        $stmt_supervisor = $conn->prepare($sql_supervisor);
        $stmt_supervisor->bind_param("s", $lecturerID);

        if ($stmt_supervisor->execute()) {
            $supervisorID = $conn->insert_id; // Get newly created SupervisorID
        } else {
            $message = "Error creating supervisor: " . $conn->error;
            $messageType = "danger";
            $supervisorID = null;
        }
    }

    if ($supervisorID) {
        // Assign the supervisor to the student
        $sql_student = "UPDATE Student SET SupervisorID = ? WHERE StudentID = ?";
        $stmt_student = $conn->prepare($sql_student);
        $stmt_student->bind_param("is", $supervisorID, $studentID);

        if ($stmt_student->execute()) {
            $message = "Supervisor assigned successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating student: " . $conn->error;
            $messageType = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Supervisor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { display: flex; min-height: 100vh; flex-direction: column; }
        .sidebar { width: 250px; background-color: #343a40; color: #fff; position: fixed; height: 100%; }
        .sidebar .nav-link { color: #fff; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #495057; }
        .content { margin-left: 250px; padding: 20px; background-color: #f8f9fa; flex-grow: 1; }
        .header { display: flex; justify-content: flex-end; padding: 10px 20px; background-color: #f8f9fa; }
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


    <h2>Assign Supervisor</h2>
    <p>Select a student and assign them a supervisor.</p>

    <!-- Success or Error Message -->
    <?php if (isset($message)): ?>
        <div class="alert alert-<?= $messageType; ?>"><?= $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Assign Supervisor Form</h5>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="studentID" class="form-label">Student</label>
                    <select class="form-select" id="studentID" name="studentID" required>
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <option value="<?= $student['StudentID'] ?>"><?= $student['StudentName'] ?> (<?= $student['StudentID'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="lecturerID" class="form-label">Supervisor</label>
                    <select class="form-select" id="lecturerID" name="lecturerID" required>
                        <?php while ($lecturer = $lecturers->fetch_assoc()): ?>
                            <option value="<?= $lecturer['LecturerID'] ?>"><?= $lecturer['LecturerName'] ?> (<?= $lecturer['LecturerID'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Assign Supervisor</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
