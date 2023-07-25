<?php
session_start();
// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "examiner" || empty($_SESSION["user_id"]) ) {
    header("Location: login.php");
    exit();
}

// Include the database connection file
require 'connectDb.php';
// Update test status
if (isset($_POST['test_id']) && isset($_POST['status'])) {
    $test_id = $_POST['test_id'];
    $status = $_POST['status'];

    // Update the test status in the database
    $stmt = $conn->prepare("UPDATE Test SET Enabled = :status WHERE test_id = :test_id");
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":test_id", $test_id);
    $stmt->execute();

    echo '<script>alert("Test status updated successfully.");</script>';
}

// Retrieve tests from the database
$stmt = $conn->prepare("SELECT * FROM Test");
$stmt->execute();
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Test</title>
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
                        <h2>Manage Test</h2>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by test name" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped table-success">
                                <thead>
                                    <tr>
                                        <th>Test ID</th>
                                        <th>Test Name</th>
                                        <th>Business Vertical</th>
                                        <th>Broad Category</th>
                                        <th>Department</th>
                                        <th>Subdepartment</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tests as $test) { ?>
                                        <tr>
                                            <td><?php echo $test['test_id']; ?></td>
                                            <td><?php echo $test['test_name']; ?></td>
                                            <td><?php echo $test['Business_vertical']; ?></td>
                                            <td><?php echo $test['Broad_category']; ?></td>
                                            <td><?php echo $test['Department_name']; ?></td>
                                            <td><?php echo $test['Sub_department']; ?></td>
                                            <td>
                                                <form method="POST">
                                                    <input type="hidden" name="test_id" value="<?php echo $test['test_id']; ?>">
                                                    <select name="status" class="form-control" onchange="this.form.submit()">
                                                        <option value="1" <?php if ($test['Enabled'] == 1) echo 'selected'; ?>>Enabled</option>
                                                        <option value="0" <?php if ($test['Enabled'] == 0) echo 'selected'; ?>>Disabled</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
