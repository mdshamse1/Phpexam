<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MYCKBEXAM";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a new PDO connection
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $user_id = $_POST["user_id"];
    $password = $_POST["password"];
    $user_type = $_POST["user_type"];
    
    // Determine the table and enabled column based on the user type 
    $table = "";
    $enabledColumn = "";
    switch ($user_type) {
        case "admin":
            $table = "Admin";
            $enabledColumn = "Enabled";
            break;
        case "examiner":
            $table = "Examiner";
            $enabledColumn = "Enabled";
            break;
        case "employee":
            $table = "Employee";
            $enabledColumn = "Enabled";
            break;
        default:
            echo '<script>alert("Invalid user type.");</script>';
            break;
    }
    
    if (!empty($table)) {
        // Check if the user exists in the corresponding table
        $stmt = $conn->prepare("SELECT * FROM $table WHERE ${table}_id = :user_id AND Password = :password");
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":password", $password);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($result) {
            // Check if the account is enabled
            $enabled = $result[$enabledColumn];
            if ($enabled) {
                // Login successful, redirect to the index.php page
                session_start();
                $_SESSION["user_id"] = $user_id;
                $_SESSION["user_type"] = $user_type;
                header("Location: index.php");
                exit();
            } else {
                // Account is disabled
                $error_message = "Your account is disabled. Please contact the administrator.";
            }
        } else {
            // Invalid credentials
            $error_message = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
    .error-message {
        color: red;
        text-align: center;
    }
</style>
</head>
<body class="bg-secondary">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>Login</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="login.php">
                        <div class="error-message"><?php echo isset($error_message) ? $error_message : ""; ?></div>
                            <div class="form-group">
                                <label for="user_id">User ID:</label>
                                <input type="text" class="form-control" placeholder="Enter your Employee id" id="user_id" name="user_id" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="user_type">User Type:</label>
                                <select class="form-control" id="user_type" name="user_type" required>
                                    <option value="admin">Admin</option>
                                    <option value="examiner">Examiner</option>
                                    <option value="employee">Employee</option>
                                </select>
                            </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
