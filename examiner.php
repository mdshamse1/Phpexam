<?php

session_start();
// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin" || empty($_SESSION["user_id"]) ) {
    header("Location: login.php");
    exit();
}

// Include the database connection file
require 'connectDb.php';

// Fetch options from database tables
$stmt = $conn->prepare("SELECT * FROM BusinessVertical");
$stmt->execute();
$businessVerticals = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM BroadCategory");
$stmt->execute();
$broadCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM Department");
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM SubDepartment");
$stmt->execute();
$subDepartments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize variables
$examiner_id = "";
$examiner_name = "";
$business_vertical = "";
$broad_category = "";
$department_name = "";
$sub_department = "";
$password = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $examiner_id = $_POST["examiner_id"];
    $examiner_name = $_POST["examiner_name"];
    $business_vertical = $_POST["business_vertical"];
    $broad_category = $_POST["broad_category"];
    $department_name = $_POST["department_name"];
    $sub_department = $_POST["sub_department"];
    $password = $_POST["password"];

    // Check if examiner_id already exists
    $stmt = $conn->prepare("SELECT * FROM Examiner WHERE Examiner_id = :examiner_id");
    $stmt->bindParam(":examiner_id", $examiner_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Examiner with the same ID already exists
        echo '<script>alert("Examiner ID already exists. Please choose a different ID.");</script>';
    } else {
        // Insert new examiner into the database
        $stmt = $conn->prepare("INSERT INTO Examiner (Examiner_id, Examiner_name, Business_vertical, Broad_category, Department_name, Sub_department, Password) VALUES (:examiner_id, :examiner_name, :business_vertical, :broad_category, :department_name, :sub_department, :password)");
        $stmt->bindParam(":examiner_id", $examiner_id);
        $stmt->bindParam(":examiner_name", $examiner_name);
        $stmt->bindParam(":business_vertical", $business_vertical);
        $stmt->bindParam(":broad_category", $broad_category);
        $stmt->bindParam(":department_name", $department_name);
        $stmt->bindParam(":sub_department", $sub_department);
        $stmt->bindParam(":password", $password);
        $stmt->execute();

        // Redirect to the examiner list page
        // header("Location: examiner_list.php");
        // exit();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Examiner</title>
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
                        <h2>Create Examiner</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="examiner_id">Examiner ID:</label>
                                <input type="number" class="form-control" id="examiner_id" name="examiner_id" required>
                            </div>
                            <div class="form-group">
                                <label for="examiner_name">Examiner Name:</label>
                                <input type="text" class="form-control" id="examiner_name" name="examiner_name"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="business_vertical">Business Vertical:</label>
                                <select class="form-control" id="business_vertical" name="business_vertical" required>
                                    <?php foreach ($businessVerticals as $vertical) { ?>
                                        <option value="<?php echo $vertical['Business_vertical']; ?>"><?php echo $vertical['Business_vertical']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="broad_category">Broad Category:</label>
                                <select class="form-control" id="broad_category" name="broad_category" required>
                                    <?php foreach ($broadCategories as $category) { ?>
                                        <option value="<?php echo $category['Broad_category']; ?>"><?php echo $category['Broad_category']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="department_name">Department Name:</label>
                                <select class="form-control" id="department_name" name="department_name" required>
                                    <?php foreach ($departments as $department) { ?>
                                        <option value="<?php echo $department['Department_name']; ?>"><?php echo $department['Department_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sub_department">Subdepartment:</label>
                                <select class="form-control" id="sub_department" name="sub_department" required>
                                    <?php foreach ($subDepartments as $subDepartment) { ?>
                                        <option value="<?php echo $subDepartment['Sub_department']; ?>"><?php echo $subDepartment['Sub_department']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Examiner</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
