<?php
session_start();
// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin" || empty($_SESSION["user_id"]) ) {
    header("Location: login.php");
    exit();
}

// Database connection settings
$host = "localhost";
$username = "root";
$password = "";
$database = "MYCKBEXAM";

// Create a database connection
$conn = mysqli_connect($host, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to add an employee to the database 
function addEmployee($employeeData)
{
    global $conn;

    $employeeId = $employeeData[0];
    $employeeName = $employeeData[1];
    $businessVertical = $employeeData[2];
    $broadCategory = $employeeData[3];
    $departmentName = $employeeData[4];
    $subDepartment = $employeeData[5];
    $password = $employeeData[6];

    // Check if employee ID already exists
    $checkQuery = "SELECT employee_id FROM Employee WHERE employee_id = '$employeeId'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                $('#duplicateModal').modal('show');
            });
        </script>";
    } else {
        // Prepare the SQL statement
        $sql = "INSERT INTO Employee (employee_id, employee_name, Business_vertical, Broad_category, Department_name, Sub_department, Password) 
                VALUES ('$employeeId', '$employeeName', '$businessVertical', '$broadCategory', '$departmentName', '$subDepartment', '$password')";

        // Execute the SQL statement
        if (mysqli_query($conn, $sql)) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    $('#successModal').modal('show');
                });
            </script>";
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    $('#errorModal').modal('show');
                });
            </script>";
        }
    }
}

// Check if the CSV file is uploaded
if (isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, "r");

    // Skip the first row (header)
    $data = fgetcsv($handle, 1000, ",");

    // Process each row of the CSV file
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        addEmployee($data);
    }

    fclose($handle);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Employees from CSV</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-secondary">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <h1 class="mt-5">Add Employees from CSV</h1>
        <form method="post" enctype="multipart/form-data" class="mt-4">
            <div class="form-group">
                <label for="csv_file">CSV File:</label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-control-file">
            </div>
            <button type="submit" class="btn btn-primary">Import</button>
        </form>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Employee added successfully!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Error adding employee. Please try again.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Duplicate Modal -->
    <div class="modal fade" id="duplicateModal" tabindex="-1" role="dialog" aria-labelledby="duplicateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="duplicateModalLabel">Duplicate Entry</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Employee with the same ID already exists.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
