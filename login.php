<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #86b7fe;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h3 class="text-center mb-4">Login</h3>
    <!-- Login Form -->
    <form action="" method="POST">
        <!-- Username input -->
        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <!-- Password input -->
        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <!-- Submit button -->
        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
        <!-- Forgot Password link -->
        <div class="text-center mt-3">
            <a href="faq.html">FAQ</a>
        </div>
    </form>
</div>

<!-- PHP Login Logic -->
<?php
session_start(); // Start the session

if (isset($_POST['login'])) {
    // Database connection settings
    $servername = "localhost";
    $username = "root"; // Replace with your DB username
    $password = ""; // Replace with your DB password
    $dbname = "IMS";

    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form input
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Check if the user is an admin
    $sql = "SELECT * FROM ADMIN WHERE Username = '$user' AND Password = '$pass'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // Admin found
        $_SESSION['user_role'] = 'admin';
        $_SESSION['username'] = $user;
        header("Location: admindashboard.php");
        exit();
    }

    // Check if the user is a student
    $sql = "SELECT * FROM Student WHERE StudentID = '$user' AND '123' = '$pass'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // Student found
        $_SESSION['user_role'] = 'student';
        $_SESSION['username'] = $user;
        header("Location: studentdashboard.php");
        exit();
    }

    // Check if the user is a supervisor
    $sql = "SELECT * FROM Supervisor WHERE SupervisorID = '$user' AND '123' = '$pass'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // Supervisor found
        $_SESSION['user_role'] = 'supervisor';
        $_SESSION['username'] = $user;
        header("Location: supervisordashboard.php");
        exit();
    }

    // If no match, return to login page with an error
    echo "<div class='alert alert-danger text-center mt-3'>Invalid username or password.</div>";
    $conn->close();
}
?>

<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
