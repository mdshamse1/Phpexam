<?php
session_start();
// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin" || empty($_SESSION["user_id"]) ) {
    header("Location: login.php");
    exit();
}

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

// Check if the form is submitted for updating employee data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the form data
    $employee_id = $_POST['employee_id'];
    $new_employee_id = $_POST['new_employee_id'];
    $employee_name = $_POST['employee_name'];
    $business_vertical = $_POST['business_vertical'];
    $broad_category = $_POST['broad_category'];
    $department_name = $_POST['department_name'];
    $sub_department = $_POST['sub_department'];
    $password = $_POST['password'];
    $enabled = isset($_POST['enabled']) ? 1 : 0; // Check if the employee is enabled

    // Update the employee data in the database
    $stmt = $conn->prepare("UPDATE Employee SET employee_id = :new_id, employee_name = :name, Business_vertical = :vertical, Broad_category = :category, Department_name = :dept, Sub_department = :subdept, Password = :password, Enabled = :enabled WHERE employee_id = :id");
    $stmt->bindParam(':new_id', $new_employee_id);
    $stmt->bindParam(':id', $employee_id);
    $stmt->bindParam(':name', $employee_name);
    $stmt->bindParam(':vertical', $business_vertical);
    $stmt->bindParam(':category', $broad_category); 
    $stmt->bindParam(':dept', $department_name);
    $stmt->bindParam(':subdept', $sub_department);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':enabled', $enabled, PDO::PARAM_INT);
    $stmt->execute();

    // Redirect to the same page after the update
    header("Location: manage_employee.php");
    exit();
}

// Retrieve the employee list from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$stmt = $conn->prepare("SELECT * FROM Employee WHERE employee_id LIKE :search");
$stmt->bindValue(':search', '%' . $search . '%');
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Employee List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body class="bg-secondary">
<?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info">
                        <h2>Manage Employee</h2>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by employee id" value="<?php echo $search; ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </div>
                        </form>
                        <table class="table table-striped table-success">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Business Vertical</th>
                                    <th>Broad Category</th>
                                    <th>Department Name</th>
                                    <th>Subdepartment</th>
                                    <th>Password</th>
                                    <th>Enabled</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <form method="POST" action="">
                                            <td>
                                                <input type="text" class="form-control" name="new_employee_id" value="<?php echo $employee['employee_id']; ?>">
                                            </td>
                                            <td><input type="text" class="form-control" name="employee_name" value="<?php echo $employee['employee_name']; ?>"></td>
                                            <td><input type="text" class="form-control" name="business_vertical" value="<?php echo $employee['Business_vertical']; ?>"></td>
                                            <td><input type="text" class="form-control" name="broad_category" value="<?php echo $employee['Broad_category']; ?>"></td>
                                            <td><input type="text" class="form-control" name="department_name" value="<?php echo $employee['Department_name']; ?>"></td>
                                            <td><input type="text" class="form-control" name="sub_department" value="<?php echo $employee['Sub_department']; ?>"></td>
                                            <td><input type="password" class="form-control" name="password" value="<?php echo $employee['Password']; ?>"></td>
                                            <td>
                                                <input type="checkbox" name="enabled" <?php if ($employee['Enabled']) echo 'checked'; ?>>
                                            </td>
                                            <td>
                                                <input type="hidden" name="employee_id" value="<?php echo $employee['employee_id']; ?>">
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
