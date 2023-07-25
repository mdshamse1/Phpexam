<?php
session_start();
// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin" || empty($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
// Include the database connection file
require 'connectDb.php';

// Create a new MySQLi object and establish the database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the sub-department options for filtering
$subDeptQuery = "SELECT DISTINCT Sub_department FROM Employee";
$subDeptResult = $conn->query($subDeptQuery);

if ($subDeptResult) {
    $subDeptCount = $subDeptResult->num_rows;
} else {
    $subDeptCount = 0;
}

// Retrieve the filter options for Business Vertical
$businessVerticalQuery = "SELECT * FROM BusinessVertical";
$businessVerticalResult = $conn->query($businessVerticalQuery);

// Retrieve the results of each employee for each test
$sql = "SELECT e.employee_id, e.employee_name, e.Broad_category, e.Department_name, e.Sub_department, t.test_id, t.test_name, r.marks_obtained, r.percentage, r.submission_datetime
        FROM Employee e
        JOIN Result r ON e.employee_id = r.employee_id
        JOIN Test t ON r.test_id = t.test_id";

// Apply filters if selected
if (isset($_GET['business_vertical'])) {
    $businessVertical = $_GET['business_vertical'];
    $sql .= " WHERE e.Business_vertical = '$businessVertical'";
}

$sql .= " GROUP BY e.employee_id, t.test_id";

$result = $conn->query($sql);

if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="employee_test_results.csv"');

    $output = fopen('php://output', 'w');

    // Write header row
    fputcsv($output, ['Employee ID', 'Employee Name', 'Broad Category', 'Department', 'Sub-department', 'Test ID', 'Test Name', 'Marks Obtain', 'percentage', 'Submission Date and Time']);

    // Write data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['employee_id'],
            $row['employee_name'],
            $row['Broad_category'],
            $row['Department_name'],
            $row['Sub_department'],
            $row['test_id'],
            $row['test_name'],
            $row['marks_obtained'],
            $row['percentage']
        ]);
    }

    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Employee Test Results</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-dark">
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="card">
            <div class="card-header bg-info">
                <h2 class="text-center">Test Results</h2>
            </div>
            <div class="card-body">
                <!-- Filter form -->
                <form action="" method="GET" class="mb-3">
                    <div class="form-row align-items-center">
                        <div class="col-auto">
                            <label for="business_vertical" class="mr-2">Business Vertical:</label>
                            <select class="form-control" id="business_vertical" name="business_vertical">
                                <option value="">All</option>
                                <?php
                                while ($row = $businessVerticalResult->fetch_assoc()) {
                                    $businessVertical = $row['Business_vertical'];
                                    echo "<option value='$businessVertical'>$businessVertical</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>

                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-success table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Broad Category</th>
                                    <th>Department</th>
                                    <th>Sub-department</th>
                                    <th>Test ID</th>
                                    <th>Test Name</th>
                                    <th>Marks Obtain</th>
                                    <th>Percentage</th>
                                    <th>Submission Date and Time</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo $row['employee_id']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['employee_name']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['Broad_category']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['Department_name']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['Sub_department']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['test_id']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['test_name']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['marks_obtained']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['percentage']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['submission_datetime']; ?>
                                        </td>

                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="?business_vertical=<?php echo isset($_GET['business_vertical']) ? $_GET['business_vertical'] : ''; ?>&export=1"
                            class="btn btn-success">Export to Excel</a>
                        <a href="index.php" class="btn btn-warning">Home</a>
                    </div>
                <?php else: ?>
                    <p>No results found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>