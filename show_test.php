<?php
session_start();
// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "employee" || empty($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
// Retrieve the user ID and user type from the session
session_start();
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
// Check if the user is an employee
if ($user_type === "employee") {
    // Retrieve the employee's business vertical, broad category, department, and sub-department
    $stmt = $conn->prepare("SELECT Business_vertical, Broad_category, Department_name, Sub_department FROM Employee WHERE employee_id = :user_id");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($employee) {
        // Retrieve the tests that match the employee's business vertical, broad category, department, and sub-department
        $stmt = $conn->prepare("SELECT test_id, Password, test_date FROM Test WHERE Business_vertical = :business_vertical AND Broad_category = :broad_category AND Department_name = :department_name AND Sub_department = :sub_department ORDER BY test_date DESC");
        $stmt->bindParam(":business_vertical", $employee['Business_vertical']);
        $stmt->bindParam(":broad_category", $employee['Broad_category']);
        $stmt->bindParam(":department_name", $employee['Department_name']);
        $stmt->bindParam(":sub_department", $employee['Sub_department']);
        $stmt->execute();
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Show Test</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body class="bg-secondary">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-info">
                        <h2>Available Tests</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        // Check if the user has already taken a test
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM Answer WHERE employee_id = :user_id");
                        $stmt->bindParam(":user_id", $user_id);
                        $stmt->execute();
                        $hasTakenTest = $stmt->fetchColumn();

                        if ($hasTakenTest) {
                            // echo '<div class="alert alert-warning" role="alert">You have already taken a test.</div>';
                        }
                        ?>
                        <?php if ($tests && count($tests) > 0): ?>
                            <table class="table table-striped table-success">
                                <thead>
                                    <tr>
                                        <th>Test ID</th>
                                        <th>Password</th>
                                        <th>Test Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tests as $test): ?>
                                        <tr>
                                            <td>
                                                <?php echo $test['test_id']; ?>
                                            </td>
                                            <td>
                                                <?php echo $test['Password']; ?>
                                            </td>
                                            <td>
                                                <?php echo $test['test_date']; ?>
                                            </td>
                                            <td>
                                                <?php
                                                // Check if the user has already taken this specific test
                                                $stmt = $conn->prepare("SELECT COUNT(*) FROM Answer WHERE employee_id = :user_id AND test_id = :test_id");
                                                $stmt->bindParam(":user_id", $user_id);
                                                $stmt->bindParam(":test_id", $test['test_id']);
                                                $stmt->execute();
                                                $hasTakenSpecificTest = $stmt->fetchColumn();

                                                if ($hasTakenSpecificTest) {
                                                    echo '<button type="button" class="btn btn-secondary" disabled>Test Taken</button>';
                                                } else {
                                                    echo '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#testModal_' . $test['test_id'] . '">Take Test</button>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <h4 class="text-center">Oh my bad 😞 No Test available for you 😁</h4>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Add this outside the "card" div -->
    <?php foreach ($tests as $test): ?>
        <div class="modal fade" id="testModal_<?php echo $test['test_id']; ?>" tabindex="-1" role="dialog"
            aria-labelledby="testModalLabel_<?php echo $test['test_id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="testModalLabel_<?php echo $test['test_id']; ?>">Questions for Test <?php echo $test['test_id']; ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Here, you can display the questions for the current test -->
                        <?php
                        $test_id = $test['test_id'];
                        $stmt = $conn->prepare("SELECT * FROM Question WHERE test_id = :test_id");
                        $stmt->bindParam(":test_id", $test_id);
                        $stmt->execute();
                        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <?php if ($questions && count($questions) > 0): ?>
                            <form method="post" action="submit_test.php">
                                <!-- Assuming you have a separate PHP file to handle test submission -->
                                <?php foreach ($questions as $question): ?>
                                    <p>
                                        <?php echo $question['question_text']; ?>
                                    </p>
                                    <div>
                                        <label>
                                            <input type="radio" name="answer_<?php echo $question['question_id']; ?>" value="1">
                                            <?php echo $question['option_1']; ?>
                                        </label>
                                    </div>
                                    <div>
                                        <label>
                                            <input type="radio" name="answer_<?php echo $question['question_id']; ?>" value="2">
                                            <?php echo $question['option_2']; ?>
                                        </label>
                                    </div>
                                    <div>
                                        <label>
                                            <input type="radio" name="answer_<?php echo $question['question_id']; ?>" value="3">
                                            <?php echo $question['option_3']; ?>
                                        </label>
                                    </div>
                                    <div>
                                        <label>
                                            <input type="radio" name="answer_<?php echo $question['question_id']; ?>" value="4">
                                            <?php echo $question['option_4']; ?>
                                        </label>
                                    </div>
                                    <hr>
                                <?php endforeach; ?>
                                <input type="hidden" name="test_id" value="<?php echo $test['test_id']; ?>">
                                <button type="submit" class="btn btn-primary">Submit Test</button>
                            </form>
                        <?php else: ?>
                            <p>No questions available for this test.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</body>

</html>