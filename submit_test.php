<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if the user is logged in and is an employee
    if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "employee" || empty($_SESSION["user_id"])) {
        die("Access denied.");
    }

    // Database connection details
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "MYCKBEXAM";

    // Create a new PDO connection with error handling
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // Retrieve the user ID and user type from the session
    $user_id = $_SESSION["user_id"];

    // Validate and sanitize the test ID from the form data
    $test_id = filter_input(INPUT_POST, "test_id", FILTER_SANITIZE_STRING);
    if (empty($test_id)) {
        die("Invalid test ID.");
    }

    // Check if the user has already given the test
    $stmt = $conn->prepare("SELECT * FROM Answer WHERE employee_id = :user_id AND test_id = :test_id");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":test_id", $test_id);
    $stmt->execute();
    $previous_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($previous_answers)) {
        // If the user has already submitted answers for this test, display a popup message
        echo '<script>alert("You have already given this test."); window.location.href="show_test.php";</script>';
        exit();
    }

    // Loop through the submitted answers and save them in the database
    $correct_answers = 0;
    $total_questions = 0;
    foreach ($_POST as $key => $value) {
        if (strpos($key, "answer_") === 0) {
            $question_id = substr($key, strlen("answer_"));
            $selected_option = (int)$value;

            // Validate the selected option (must be an integer between 1 and 4)
            if (!is_int($selected_option) || $selected_option < 1 || $selected_option > 4) {
                die("Invalid selected option for question ID: $question_id");
            }

            // Retrieve the correct option for the current question from the Question table
            $stmt = $conn->prepare("SELECT correct_option FROM Question WHERE question_id = :question_id AND test_id = :test_id");
            $stmt->bindParam(":question_id", $question_id);
            $stmt->bindParam(":test_id", $test_id);
            $stmt->execute();
            $question_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $correct_option = ($question_data && isset($question_data['correct_option'])) ? (int)$question_data['correct_option'] : null;

            // Compare the selected option with the correct option
            if ($correct_option !== null && $correct_option === $selected_option) {
                // If the selected option matches the correct option, store it as the correct answer in the Answer table
                $correct_answers++;
            }       
            
            // Insert the selected option along with the correct option into the database table `Answer` using a prepared statement
            $stmt = $conn->prepare("INSERT INTO Answer (employee_id, question_id, selected_option, test_id, correct_option) VALUES (:employee_id, :question_id, :selected_option, :test_id, :correct_option)");
            $stmt->bindParam(":employee_id", $user_id);
            $stmt->bindParam(":question_id", $question_id);
            $stmt->bindParam(":selected_option", $selected_option);
            $stmt->bindParam(":test_id", $test_id);
            $stmt->bindParam(":correct_option", $correct_option);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                die("Error while saving the answer for question ID: $question_id");
            }
            

            // Increment the total questions count
            $total_questions++;
        }
    }

    // Calculate the marks obtained and the percentage for the test
    $marks_obtained = $correct_answers;
    $percentage = ($total_questions > 0) ? (($correct_answers / $total_questions) * 100) : 0;

    // Insert the test result into the `Result` table along with the date and time of submission
    $stmt = $conn->prepare("INSERT INTO Result (employee_id, test_id, marks_obtained, percentage, submission_datetime) VALUES (:employee_id, :test_id, :marks_obtained, :percentage, NOW())");
    $stmt->bindParam(":employee_id", $user_id);
    $stmt->bindParam(":test_id", $test_id);
    $stmt->bindParam(":marks_obtained", $marks_obtained);
    $stmt->bindParam(":percentage", $percentage);

    try {
        $stmt->execute();
    } catch(PDOException $e) {
        die("Error while saving the test result.");
    }

    // Format the percentage to display only two digits after the decimal point
    $formatted_percentage = number_format($percentage, 2);

    // Display the success message as a popup modal
    echo '<script>alert("Test submitted successfully! You obtained ' . $marks_obtained . ' out of ' . $total_questions . ' questions. (' . $formatted_percentage . '%)"); window.location.href="show_test.php";</script>';
    exit();
} else {
    // If the form was not submitted via POST, redirect the user back to the main page or show an error message.
    header("Location: show_test.php");
    exit();
}

// Function to sanitize user inputs
function sanitize_input($input) {
    return htmlspecialchars(stripslashes(trim($input)));
}
?>
