<?php
// Include database connection
include 'db_connection.php';

// Fetch the count of students grouped by programme
$sql = "SELECT StudentProgramme, COUNT(*) AS total_students FROM student GROUP BY StudentProgramme";
$result = $conn->query($sql);

// Prepare data for the bar graph
$programmes = [];
$studentCounts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $programmes[] = $row['StudentProgramme'];
        $studentCounts[] = $row['total_students'];
    }
}

// Fetch the count of students grouped by state
$sqlState = "SELECT State, COUNT(*) AS total_students FROM company GROUP BY State";
$resultState = $conn->query($sqlState);

// Prepare data for the pie chart
$states = [];
$stateCounts = [];
if ($resultState->num_rows > 0) {
    while ($row = $resultState->fetch_assoc()) {
        $states[] = $row['State'];
        $stateCounts[] = $row['total_students'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { display: flex; min-height: 100vh; flex-direction: column; }
        .sidebar { width: 250px; background-color: #343a40; color: #fff; position: fixed; height: 100%; }
        .sidebar .nav-link { color: #fff; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #495057; }
        .content { margin-left: 250px; padding: 20px; background-color: #f8f9fa; flex-grow: 1; }
        .header { display: flex; justify-content: flex-end; padding: 10px 20px; background-color: #f8f9fa; }
        .chart-container { display: flex; gap: 20px; }
        .chart-container .card { flex: 1; }
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

    <h2>Welcome, Admin</h2>
    <p>Here you can manage users, view reports, and configure settings.</p>

    <!-- Graphs Section -->
    <div class="chart-container">
        <!-- Bar Chart Card -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Student Count by Programme</h5>
                <canvas id="programmeChart" width="400" height="200"></canvas>
            </div>
        </div>
        <!-- Pie Chart Card -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Student Internship Place Count by State</h5>
                <canvas id="stateChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    // Bar Chart
    const ctx1 = document.getElementById('programmeChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($programmes); ?>,
            datasets: [{
                label: 'Number of Students',
                data: <?php echo json_encode($studentCounts); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: { scales: { y: { beginAtZero: true } } }
    });

    // Pie Chart
    const ctx2 = document.getElementById('stateChart').getContext('2d');
    new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($states); ?>,
            datasets: [{
                data: <?php echo json_encode($stateCounts); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        }
    });
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
