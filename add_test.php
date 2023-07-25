<?php
// Start a session to manage user authentication
session_start();

// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "examiner" || empty($_SESSION["user_id"]) ) {
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

// if (!empty($table)) {
//     // Check if the user exists in the corresponding table
//     $stmt = $conn->prepare("SELECT * FROM $table WHERE ${table}_id = :user_id AND Password = :password");
//     $stmt->bindParam(":user_id", $user_id);
//     $stmt->bindParam(":password", $password);
//     $stmt->execute();
//     $result = $stmt->fetch(PDO::FETCH_ASSOC);

//     if ($result) {
//         // Login successful, redirect to the index.php page
//         session_start();
//         $_SESSION["user_id"] = $user_id;
//         $_SESSION["user_type"] = $user_type;
//         header("Location: index.php");
//         exit();
//     } else {
//         echo '<script>
//                 alert("Invalid credentials. Please try again.");
//                 setTimeout(function() {
//                     window.location.href = "login.php";
//                 }, 1000); // Delay the redirection by 1 second (1000 milliseconds)
//             </script>';
//         exit();
//     }
// }

// Retrieve data for dropdown menus
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $business_verticals = $_POST["business_vertical"];
    $broad_categories = $_POST["broad_category"];
    $departments = $_POST["department_name"];
    $sub_departments = $_POST["sub_department"];
    $test_name = $_POST["test_name"];
    $test_id = $_POST["test_id"];
    $password = $_POST["password"];

    // Check if the test ID already exists in the database
    $stmt = $conn->prepare("SELECT * FROM Test WHERE test_id = :test_id");
    $stmt->bindParam(":test_id", $test_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo '<script>alert("Test ID already exists. Please enter a unique Test ID.");</script>';
    } else {
        // Insert the test data into the database for each selected Business vertical, Broad category, department, and sub-department
        foreach ($business_verticals as $business_vertical) {
            foreach ($broad_categories as $broad_category) {
                foreach ($departments as $department) {
                    foreach ($sub_departments as $sub_department) {
                        $stmt = $conn->prepare("INSERT INTO Test (Business_vertical, Broad_category, Department_name, Sub_department, test_name, test_id, Password, Enabled) VALUES (:business_vertical, :broad_category, :department_name, :sub_department, :test_name, :test_id, :password, 1)");
                        $stmt->bindParam(":business_vertical", $business_vertical);
                        $stmt->bindParam(":broad_category", $broad_category);
                        $stmt->bindParam(":department_name", $department);
                        $stmt->bindParam(":sub_department", $sub_department);
                        $stmt->bindParam(":test_name", $test_name);
                        $stmt->bindParam(":test_id", $test_id);
                        $stmt->bindParam(":password", $password);
                        $stmt->execute();
                    }
                }
            }
        }

        echo '<script>alert("Test(s) added successfully.");</script>';
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Add Test</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .select-all-label {
            font-weight: bold;
        }
    </style>
</head>

<body class="bg-secondary">
<?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info">
                        <h2>Add Test</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Business Vertical:</label>
                                <div class="col-sm-10">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="select-all-business-vertical">
                                        <label class="form-check-label select-all-label" for="select-all-business-vertical">Select All</label>
                                    </div>
                                    <?php foreach ($businessVerticals as $vertical) { ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input business-vertical-checkbox" type="checkbox" name="business_vertical[]" value="<?php echo $vertical['Business_vertical']; ?>" id="business_vertical_<?php echo $vertical['Business_vertical']; ?>">
                                            <label class="form-check-label" for="business_vertical_<?php echo $vertical['Business_vertical']; ?>">
                                                <?php echo $vertical['Business_vertical']; ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Broad Category:</label>
                                <div class="col-sm-10">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="select-all-broad-category">
                                        <label class="form-check-label select-all-label" for="select-all-broad-category">Select All</label>
                                    </div>
                                    <?php foreach ($broadCategories as $category) { ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input broad-category-checkbox" type="checkbox" name="broad_category[]" value="<?php echo $category['Broad_category']; ?>" id="broad_category_<?php echo $category['Broad_category']; ?>">
                                            <label class="form-check-label" for="broad_category_<?php echo $category['Broad_category']; ?>">
                                                <?php echo $category['Broad_category']; ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Department Name:</label>
                                <div class="col-sm-10">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="select-all-department">
                                        <label class="form-check-label select-all-label" for="select-all-department">Select All</label>
                                    </div>
                                    <?php foreach ($departments as $department) { ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input department-checkbox" type="checkbox" name="department_name[]" value="<?php echo $department['Department_name']; ?>" id="department_name_<?php echo $department['Department_name']; ?>">
                                            <label class="form-check-label" for="department_name_<?php echo $department['Department_name']; ?>">
                                                <?php echo $department['Department_name']; ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Sub Department:</label>
                                <div class="col-sm-10">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="select-all-sub-department">
                                        <label class="form-check-label select-all-label" for="select-all-sub-department">Select All</label>
                                    </div>
                                    <?php foreach ($subDepartments as $subDepartment) { ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input sub-department-checkbox" type="checkbox" name="sub_department[]" value="<?php echo $subDepartment['Sub_department']; ?>" id="sub_department_<?php echo $subDepartment['Sub_department']; ?>">
                                            <label class="form-check-label" for="sub_department_<?php echo $subDepartment['Sub_department']; ?>">
                                                <?php echo $subDepartment['Sub_department']; ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="test_name">Test Name:</label>
                                <input type="text" class="form-control" id="test_name" name="test_name" required>
                            </div>
                            <div class="form-group">
                                <label for="test_id">Test ID:</label>
                                <input type="text" class="form-control" id="test_id" name="test_id" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Add Test</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
$(document).ready(function() {
    // Handler for the "Select All" checkbox change event
    $("#select-all-business-vertical").change(function() {
        // Set all business vertical checkboxes' checked property to the value of the "Select All" checkbox
        $(".business-vertical-checkbox").prop("checked", $(this).prop("checked"));
    });

    $("#select-all-broad-category").change(function() {
        // Set all broad category checkboxes' checked property to the value of the "Select All" checkbox
        $(".broad-category-checkbox").prop("checked", $(this).prop("checked"));
    });

    $("#select-all-department").change(function() {
        // Set all department checkboxes' checked property to the value of the "Select All" checkbox
        $(".department-checkbox").prop("checked", $(this).prop("checked"));
    });

    $("#select-all-sub-department").change(function() {
        // Set all sub department checkboxes' checked property to the value of the "Select All" checkbox
        $(".sub-department-checkbox").prop("checked", $(this).prop("checked"));
    });
});
</script>





</body>
</html>
