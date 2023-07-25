<?php
session_start();

// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "employee" || empty($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MYCKBEXAM";

try {
    // Create a new PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submission to view questions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["view_questions"])) {
    // Retrieve form data
    $test_id = $_POST["test_id"];
    $password = $_POST['password'];

    // Check if the test ID exists in the database
    $stmt = $conn->prepare("SELECT * FROM Test WHERE test_id = :test_id AND Password = :password");
    $stmt->bindParam(":test_id", $test_id);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Test ID and password match, retrieve the questions for the test

        // Check if the user has already submitted the test
        session_start();
        $employee_id = $_SESSION["user_id"];
        $stmt = $conn->prepare("SELECT * FROM Result WHERE employee_id = :employee_id AND test_id = :test_id");
        $stmt->bindParam(":employee_id", $employee_id);
        $stmt->bindParam(":test_id", $test_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // User has already submitted the test
            $error_message = "You have already submitted this test.";
        } else {
            // User can view the questions
            $stmt = $conn->prepare("SELECT * FROM Question WHERE test_id = :test_id");
            $stmt->bindParam(":test_id", $test_id);
            $stmt->execute();
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        // Test ID does not exist
        $error_message = "Test ID does not exist.";
    }
}

// Handle form submission to submit the test
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_test"])) {
    // Retrieve form data
    $test_id = $_POST["test_id"];
    $answers = $_POST["answers"];

    // Check if the test ID exists in the database
    $stmt = $conn->prepare("SELECT * FROM Test WHERE test_id = :test_id");
    $stmt->bindParam(":test_id", $test_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Retrieve the employee ID from the session
        session_start();
        $employee_id = $_SESSION["user_id"];

        // Check if the user has already submitted the test
        $stmt = $conn->prepare("SELECT * FROM Result WHERE employee_id = :employee_id AND test_id = :test_id");
        $stmt->bindParam(":employee_id", $employee_id);
        $stmt->bindParam(":test_id", $test_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // User has already submitted the test
            $error_message = "You have already submitted this test.";
        } else {
            // Start a transaction for atomicity
            $conn->beginTransaction();

            try {
                // Prepare the SQL statement to store the answers
                $stmt = $conn->prepare("INSERT INTO Answer (employee_id, question_id, selected_option, test_id) VALUES (:employee_id, :question_id, :selected_option, :test_id)");

                // Loop through the submitted answers and store them
                foreach ($answers as $question_id => $selected_option) {
                    // Bind parameters and execute the SQL statement
                    $stmt->bindParam(":employee_id", $employee_id);
                    $stmt->bindParam(":question_id", $question_id);
                    $stmt->bindParam(":selected_option", $selected_option);
                    $stmt->bindParam(":test_id", $test_id);
                    $stmt->execute();
                }

                // Calculate the marks
                $stmt = $conn->prepare("SELECT COUNT(*) as total_questions FROM Question WHERE test_id = :test_id");
                $stmt->bindParam(":test_id", $test_id);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $total_questions = $result['total_questions'];

                $stmt = $conn->prepare("SELECT COUNT(*) as total_correct FROM Question q JOIN Answer a ON q.question_id = a.question_id WHERE a.test_id = :test_id AND a.employee_id = :employee_id AND q.correct_option = a.selected_option AND q.test_id = :test_id");
                $stmt->bindParam(":test_id", $test_id);
                $stmt->bindParam(":employee_id", $employee_id);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $total_correct = $result['total_correct'];


                $marks_obtained = $total_correct; // Assuming each correct answer carries 1 mark
                $percentage =$marks_obtained / $total_questions*100;
                // Store the marks in the database
                $stmt = $conn->prepare("INSERT INTO Result (employee_id, test_id, marks_obtained,percentage) VALUES (:employee_id, :test_id, :marks_obtained, :percentage)");
                $stmt->bindParam(":employee_id", $employee_id);
                $stmt->bindParam(":test_id", $test_id);
                $stmt->bindParam(":marks_obtained", $marks_obtained);
                $stmt->bindParam(":percentage", $percentage);
                $stmt->execute();

                // Commit the transaction
                $conn->commit();

                // Get the test information
                $stmt = $conn->prepare("SELECT * FROM Test WHERE test_id = :test_id");
                $stmt->bindParam(":test_id", $test_id);
                $stmt->execute();
                $test_info = $stmt->fetch(PDO::FETCH_ASSOC);

                // Test submitted successfully
                $success_message = "Test submitted successfully. Marks obtained: " . $marks_obtained . "/" . $total_questions . ' ('. $marks_obtained/$total_questions *100 .'%)';

                // Display test details
                $success_message .= "<br>Test details:<br>";
                $success_message .= "Test ID: " . $test_info['test_id'] . "<br>";
                $success_message .= "Test Name: " . $test_info['test_name'] . "<br>";
                // $success_message .= "Test Duration: " . $test_info['duration'] . " minutes<br>";
            } catch (PDOException $e) {
                // Rollback the transaction on error
                $conn->rollBack();
                $error_message = "An error occurred while submitting the test: " . $e->getMessage();
            }
        }
    } else {
        // Test ID does not exist
        $error_message = "Test ID does not exist.";
    }
}
?>




<!DOCTYPE html>
<html>

<head>
    <title>Start your Exam</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        .question-card {
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-secondary">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-info">
                        <h2>Take Exam</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php elseif (isset($success_message)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success_message; ?>
                            </div>
                        <?php else: ?>
                            <?php if (empty($questions)): ?>
                                <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                                    <div class="form-group">
                                        <label for="test_id">Test ID:</label>
                                        <input type="text" class="form-control" id="test_id" name="test_id" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password:</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary" name="view_questions">View Questions</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                                    <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">
                                    <?php foreach ($questions as $question): ?>
                                        <div class="card question-card">
                                            <div class="card-header">
                                                <h4>Question ID:
                                                    <?php echo $question['question_id']; ?>
                                                </h4>
                                            </div>
                                            <div class="card-body">
                                                <p>Question:
                                                    <?php echo $question['question_text']; ?>
                                                </p>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="answers[<?php echo $question['question_id']; ?>]" value="1">
                                                    <label class="form-check-label">
                                                        <?php echo $question['option_1']; ?>
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="answers[<?php echo $question['question_id']; ?>]" value="2">
                                                    <label class="form-check-label">
                                                        <?php echo $question['option_2']; ?>
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="answers[<?php echo $question['question_id']; ?>]" value="3">
                                                    <label class="form-check-label">
                                                        <?php echo $question['option_3']; ?>
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="answers[<?php echo $question['question_id']; ?>]" value="4">
                                                    <label class="form-check-label">
                                                        <?php echo $question['option_4']; ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <button type="submit" class="btn btn-primary" name="submit_test">Submit Test</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>