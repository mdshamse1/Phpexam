<?php
// Retrieve the user ID and user type from the session
session_start();
// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"]) ) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION["user_id"];
$user_type = $_SESSION["user_type"];

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MYCKBEXAM";

// Create a new PDO connection
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<style>
    .cursor-pointer {
        cursor: pointer;
    }
</style>

<body>
    <div class="container-fluid">
        <div class="row" style="height: 100vh;">
            <div class="col-2 bg-dark text-white">
                <div class="text-center mt-3 border-bottom">
                    <h5>
                        <?php
                        // Retrieve the user's name based on the user type
                        $name = "";
                        switch ($user_type) {
                            case "admin":
                                $table = "Admin";
                                break;
                            case "examiner":
                                $table = "Examiner";
                                break;
                            case "employee":
                                $table = "Employee";
                                break;
                            default:
                                break;
                        }

                        if (!empty($table)) {
                            // Retrieve the user's name from the corresponding table
                            $stmt = $conn->prepare("SELECT ${table}_name FROM $table WHERE ${table}_id = :user_id");
                            $stmt->bindParam(":user_id", $user_id);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($result) {
                                $name = $result["${table}_name"];
                            }
                        }

                        echo $name;
                        ?>
                    </h5>
                </div>
                <ul class="nav flex-column mt-5">
                    <li class="nav-item mt-3">
                        <a class="nav-link text-white" href="index.php">Dashboard</a>
                    </li>
                    <!-- <li class="nav-item mt-3">
            <a class="nav-link text-white" href="#">Manage Broad Category</a>
        </li> -->

                    <!-- <li class="nav-item mt-3">
            <a class="nav-link text-white" href="#">Manage Department</a>
        </li> -->

                    <!--  <li class="nav-item mt-3">
            <a class="nav-link text-white" href="#">Manage Sub-Department</a>
        </li> -->
                    <?php if ($user_type == "admin"): ?>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="examiner.php">Examiner</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($user_type == "admin"): ?>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="manage_examiner.php">Manage Examiner</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($user_type == "admin"): ?>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="employee.php">Employee</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($user_type == "admin"): ?>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="add_emp.php">Add employee Bulk</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($user_type == "admin"): ?>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="manage_employee.php">Manage Employee</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($user_type == "examiner"): ?>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="add_test.php">Create Test</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($user_type == "admin"): ?>
                        <!-- <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="admin_test_view.php">view admin Test</a>
                        </li> -->
                    <?php endif; ?>
                    <?php if ($user_type == "examiner"): ?>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="manage_test.php">Manage Test</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($user_type == "examiner"): ?>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="Question.php">Add Question</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($user_type == "examiner"): ?>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="viewQuestins.php">View Question</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($user_type == "employee"): ?>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="show_test.php">Show Test</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($user_type == "employee"): ?>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="giveExam.php">Give exam</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($user_type == "admin"): ?>
                        <li class="nav-item mt-3">
                            <a href="show_result.php" class="nav-link text-white">Show Result</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <!-- ...existing code... -->
                <div class="w-50 mb-3" style="margin-top: 120vh;">
                    <a href="logout.php" class="btn btn-danger btn-block">Logout <i class="bi bi-box-arrow-left"></i></a>
                </div>
            </div>