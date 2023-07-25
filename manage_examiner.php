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

// Check if the form is submitted for updating examiner data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the form data
    $examiner_id = $_POST['examiner_id'];
    $new_examiner_id = $_POST['new_examiner_id'];
    $examiner_name = $_POST['examiner_name'];
    $business_vertical = $_POST['business_vertical'];
    $broad_category = $_POST['broad_category'];
    $department_name = $_POST['department_name'];
    $sub_department = $_POST['sub_department'];
    $password = $_POST['password'];
    $enabled = isset($_POST['enabled']) ? 1 : 0; // Check if enabled checkbox is checked

    // Update the examiner data in the database
    $stmt = $conn->prepare("UPDATE Examiner SET Examiner_id = :new_id, Examiner_name = :name, Business_vertical = :vertical, Broad_category = :category, Department_name = :dept, Sub_department = :subdept, Password = :password, Enabled = :enabled WHERE Examiner_id = :id");
    $stmt->bindParam(':new_id', $new_examiner_id);
    $stmt->bindParam(':id', $examiner_id);
    $stmt->bindParam(':name', $examiner_name);
    $stmt->bindParam(':vertical', $business_vertical);
    $stmt->bindParam(':category', $broad_category);
    $stmt->bindParam(':dept', $department_name);
    $stmt->bindParam(':subdept', $sub_department);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':enabled', $enabled);
    $stmt->execute();

    // Redirect to the same page after the update
    header("Location: manage_examiner.php");
    exit();
}

// Retrieve the examiner list from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$stmt = $conn->prepare("SELECT * FROM Examiner WHERE Examiner_id LIKE :search");
$stmt->bindValue(':search', '%' . $search . '%');
$stmt->execute();
$examiners = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data including the enabled status
    $user_id = $_POST["user_id"];
    $enabled = isset($_POST["enabled"]) ? 1 : 0; // 1 if enabled checkbox is checked, 0 if not

    // Update the enabled status in the database for the specified user_id
    try {
        $stmt = $conn->prepare("UPDATE Examiner SET Enabled = :enabled WHERE Examiner_id = :user_id");
        $stmt->bindParam(":enabled", $enabled);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        // Check the affected rows to ensure the update was successful
        if ($stmt->rowCount() > 0) {
            // Update successful
            echo "Examiner account updated successfully.";
        } else {
            // Update failed
            echo "Failed to update examiner account.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Examiner</title>
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
                        <h2>Manage Examiner</h2>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Search by examiner id" value="<?php echo $search; ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </div>
                        </form>
                        <table class="table table-striped table-success">
                            <thead>
                                <tr>
                                    <th>Examiner ID</th>
                                    <th>Examiner Name</th>
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
                                <?php foreach ($examiners as $examiner): ?>
                                    <tr>
                                        <form method="POST" action="">
                                            <td>
                                                <input type="text" class="form-control" name="new_examiner_id"
                                                    value="<?php echo $examiner['Examiner_id']; ?>">
                                            </td>
                                            <td><input type="text" class="form-control" name="examiner_name"
                                                    value="<?php echo $examiner['Examiner_name']; ?>"></td>
                                            <td><input type="text" class="form-control" name="business_vertical"
                                                    value="<?php echo $examiner['Business_vertical']; ?>"></td>
                                            <td><input type="text" class="form-control" name="broad_category"
                                                    value="<?php echo $examiner['Broad_category']; ?>"></td>
                                            <td><input type="text" class="form-control" name="department_name"
                                                    value="<?php echo $examiner['Department_name']; ?>"></td>
                                            <td><input type="text" class="form-control" name="sub_department"
                                                    value="<?php echo $examiner['Sub_department']; ?>"></td>
                                            <td><input type="password" class="form-control" name="password"
                                                    value="<?php echo $examiner['Password']; ?>"></td>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="enabled" <?php if ($examiner['Enabled']) echo 'checked'; ?>>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="hidden" name="examiner_id"
                                                    value="<?php echo $examiner['Examiner_id']; ?>">
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
