<?php
// Start the session
session_start();

// Database connection
$host = 'localhost'; // MySQL host
$user = 'root'; // MySQL username
$password = ''; // MySQL password
$dbname = 'IMS'; // Replace with your database name

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the form has already been submitted in the current session
    if (isset($_SESSION['submitted']) && $_SESSION['submitted'] == true) {
        echo "<script>alert('You have already submitted the form.');</script>";
    } else {
        // Get form data
        $studentID = $_SESSION['username']; // Get the student ID from session
        $companyName = $conn->real_escape_string($_POST['companyName']);
        $companyAddress = $conn->real_escape_string($_POST['companyAddress']);
        $companyEmail = $conn->real_escape_string($_POST['companyEmail']);
        $state = $conn->real_escape_string($_POST['state']);
        $supervisorName = $conn->real_escape_string($_POST['supervisorName']);
        $supervisorEmail = $conn->real_escape_string($_POST['supervisorEmail']);
        $supervisorPhone = $conn->real_escape_string($_POST['supervisorPhone']);

        // Check if the company already exists in the database
        $checkQuery = "SELECT * FROM Company WHERE CompanyName = '$companyName'";
        $result = $conn->query($checkQuery);

        if ($result->num_rows > 0) {
            echo "<script>alert('Company with this name already exists. Please choose another name.');</script>";
        } else {
            // Insert data into the Company table
            $sql = "INSERT INTO Company (CompanyName, CompanyEmail, CompanyAddress, State, IndustrialSV_Name, IndustrialSV_PhoneNumber, IndustrialSV_Email)
                    VALUES ('$companyName', '$companyEmail', '$companyAddress', '$state', '$supervisorName', '$supervisorPhone', '$supervisorEmail')";

            if ($conn->query($sql) === TRUE) {
                // Insert into StudentCompany table
                $studentCompanySQL = "INSERT INTO StudentCompany (StudentID, CompanyName)
                                      VALUES ('$studentID', '$companyName')";

                if ($conn->query($studentCompanySQL) === TRUE) {
                    // Set session variable to indicate that the form has been submitted
                    $_SESSION['submitted'] = true;
                    echo "<script>alert('Company details submitted successfully');</script>";
                } else {
                    echo "<script>alert('Error: " . $studentCompanySQL . "<br>" . $conn->error . "');</script>";
                }
            } else {
                echo "<script>alert('Error: " . $sql . "<br>" . $conn->error . "');</script>";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Details</title>
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
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .sidebar .nav-link {
            color: #fff;
            font-size: 1rem;
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            border-radius: 5px;
        }
        .sidebar .nav-link i {
            margin-right: 0.6rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #495057;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            flex-grow: 1;
        }
        .container {
            max-width: 600px;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: auto;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #495057;
        }
        .btn-primary {
            background-color: #343a40;
            border: none;
        }
        .btn-primary:hover {
            background-color: #495057;
        }
        .header {
            display: flex;
            justify-content: flex-end;
            padding: 10px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 20px;
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
    <!-- Header with Profile Dropdown -->
    <div class="header">
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
        <h3 class="text-center mb-4 text-secondary">Company Details Form</h3>
        <?php
        // If the form has already been submitted, show a message
        if (isset($_SESSION['submitted']) && $_SESSION['submitted'] == true) {
            echo "<p class='text-center text-success'>You have already submitted the form.</p>";
        } else {
        ?>
        <form action="" method="POST">
            <div class="mb-3">
                <label for="companyName" class="form-label">Company Name</label>
                <input type="text" class="form-control" id="companyName" name="companyName" required placeholder="Enter company name">
            </div>

            <div class="mb-3">
                <label for="companyEmail" class="form-label">Company Email</label>
                <input type="email" class="form-control" id="companyEmail" name="companyEmail" required placeholder="Enter company email">
            </div>
            
            <div class="mb-3">
                <label for="companyAddress" class="form-label">Company Address</label>
                <textarea class="form-control" id="companyAddress" name="companyAddress" rows="3" placeholder="Enter full address of the company" required></textarea>
            </div>
            
            <div class="mb-3">
                <label for="state" class="form-label">State</label>
                <input type="text" class="form-control" id="state" name="state" placeholder="Enter state" required>
            </div>

            <div class="mb-3">
                <label for="supervisorName" class="form-label">Supervisor's Name</label>
                <input type="text" class="form-control" id="supervisorName" name="supervisorName" required placeholder="Enter supervisor's name">
            </div>
            
            <div class="mb-3">
                <label for="supervisorEmail" class="form-label">Supervisor's Email</label>
                <input type="email" class="form-control" id="supervisorEmail" name="supervisorEmail" required placeholder="Enter supervisor's email">
            </div>
            
            <div class="mb-3">
                <label for="supervisorPhone" class="form-label">Supervisor's Phone</label>
                <input type="tel" class="form-control" id="supervisorPhone" name="supervisorPhone" required placeholder="Enter supervisor's phone number">
            </div>

            <button type="submit" name="submit" class="btn btn-primary w-100">Submit Company Details</button>
        </form>
        <?php } ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
