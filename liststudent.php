<?php
// Include database connection
include 'db_connection.php';

// Handle search query
$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT * FROM Student 
            WHERE StudentName LIKE '%$search%' 
               OR StudentEmail LIKE '%$search%' 
               OR StudentID LIKE '%$search%'";
} else {
    $sql = "SELECT 
            Student.StudentID, 
            Student.StudentName, 
            Student.StudentEmail, 
            Student.StudentIC, 
            Student.StudentPhoneNumber, 
            Student.StudentProgramme, 
            Student.StudentSemester, 
            Student.SupervisorID, 
            Company.CompanyName, 
            Company.CompanyEmail, 
            Company.CompanyAddress,
            Company.State, 
            Company.IndustrialSV_Name, 
            Company.IndustrialSV_PhoneNumber, 
            Company.IndustrialSV_Email
        FROM Student
        LEFT JOIN StudentCompany ON Student.StudentID = StudentCompany.StudentID
        LEFT JOIN Company ON StudentCompany.CompanyName = Company.CompanyName";

}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: row;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            height: 100vh;
            position: fixed;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
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
            align-items: center;
            padding: 10px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        table {
            margin-top: 20px;
        }
        .search-bar {
            margin-bottom: 15px;
            display: flex;
            justify-content: flex-start;
            gap: 10px;
        }
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

    <h2>List of Students</h2>

    <!-- Search Bar -->
    <form class="search-bar" method="get" action="">
        <input 
            type="text" 
            name="search" 
            class="form-control w-50" 
            placeholder="Search by Name, Email, or ID" 
            value="<?php echo htmlspecialchars($search); ?>"
        >
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
    </form>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>IC</th>
                <th>Phone</th>
                <th>Programme</th>
                <th>Semester</th>
                <th>Supervisor ID</th>
                <th>Company Name</th>
                <th>Company Email</th>
                <th>Company Address</th>
                <th>State</th>
                <th>Industrial Supervisor Name</th>
                <th>Industrial Supervisor Email</th>
                <th>Industrial Supervisor Phone</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['StudentID']; ?></td>
                        <td><?php echo $row['StudentName']; ?></td>
                        <td><?php echo $row['StudentEmail']; ?></td>
                        <td><?php echo $row['StudentIC']; ?></td>
                        <td><?php echo $row['StudentPhoneNumber']; ?></td>
                        <td><?php echo $row['StudentProgramme']; ?></td>
                        <td><?php echo $row['StudentSemester']; ?></td>
                        <td><?php echo $row['SupervisorID']; ?></td>
                        <td><?php echo $row['CompanyName']; ?></td>
                        <td><?php echo $row['CompanyEmail']; ?></td>
                        <td><?php echo $row['CompanyAddress']; ?></td>
                        <td><?php echo $row['State']; ?></td>
                        <td><?php echo $row['IndustrialSV_Name']; ?></td>
                        <td><?php echo $row['IndustrialSV_Email']; ?></td>
                        <td><?php echo $row['IndustrialSV_PhoneNumber']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center">No students found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

