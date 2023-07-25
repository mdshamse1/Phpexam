<?php
// Include the database connection file
require 'connectDb.php';

// Create a new MySQLi object and establish the database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create a new question
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $questions = $_POST['questions'];
    $test_id = $_POST['test_id'];

    // Prepare the SQL statement
    $sql = "INSERT INTO `Question` (`question_id`, `question_text`, `option_1`, `option_2`, `option_3`, `option_4`, `correct_option`, `test_id`) VALUES ";

    foreach ($questions as $question) {
        $question_id = $question['question_id']; 
        $question_text = $conn->real_escape_string($question['question_text']);
        $option_1 = $conn->real_escape_string($question['option_1']);
        $option_2 = $conn->real_escape_string($question['option_2']);
        $option_3 = $conn->real_escape_string($question['option_3']);
        $option_4 = $conn->real_escape_string($question['option_4']);
        $correct_option = $question['correct_option'];

        $sql .= "('$question_id', '$question_text', '$option_1', '$option_2', '$option_3', '$option_4', '$correct_option', '$test_id'),";
    }

    $sql = rtrim($sql, ","); // Remove the trailing comma

    // Execute the SQL statement
    if ($conn->query($sql) === TRUE) {
        // Successful message as a popup
        echo '<script>alert("Questions added successfully.");</script>';
    } else {
        // Error message as a popup
        echo '<script>alert("Error: ' . $sql . '\n' . $conn->error . '");</script>';
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Questions</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-secondary">
<?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info">
                        <h2>Add Questions</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                            <div id="questions-container">
                                <div class="form-group">
                                    <label>Question ID:</label>
                                    <input type="number" class="form-control" name="questions[0][question_id]"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label>Question Text:</label>
                                    <input type="text" class="form-control" name="questions[0][question_text]"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label>Option 1:</label>
                                    <input type="text" class="form-control" name="questions[0][option_1]" required>
                                </div>

                                <div class="form-group">
                                    <label>Option 2:</label>
                                    <input type="text" class="form-control" name="questions[0][option_2]" required>
                                </div>

                                <div class="form-group">
                                    <label>Option 3:</label>
                                    <input type="text" class="form-control" name="questions[0][option_3]" required>
                                </div>

                                <div class="form-group">
                                    <label>Option 4:</label>
                                    <input type="text" class="form-control" name="questions[0][option_4]" required>
                                </div>

                                <div class="form-group">
                                    <label>Correct Option (1-4):</label>
                                    <input type="number" class="form-control" name="questions[0][correct_option]"
                                        min="1" max="4" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Test ID:</label>
                                <input type="text" class="form-control" name="test_id" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Add Questions</button>
                            <button type="button" class="btn btn-secondary" onclick="addQuestion()">Add Another
                                Question</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        var questionIndex = 1;
        function addQuestion() {
            var questionContainer = document.getElementById('questions-container');

            var newQuestion = document.createElement('div');
            newQuestion.innerHTML = `
                <hr>
                <div class="form-group">
                    <label>Question ID:</label>
                    <input type="number" class="form-control" name="questions[${questionIndex}][question_id]" required>
                </div>

                <div class="form-group">
                    <label>Question Text:</label>
                    <input type="text" class="form-control" name="questions[${questionIndex}][question_text]" required>
                </div>

                <div class="form-group">
                    <label>Option 1:</label>
                    <input type="text" class="form-control" name="questions[${questionIndex}][option_1]" required>
                </div>

                <div class="form-group">
                    <label>Option 2:</label>
                    <input type="text" class="form-control" name="questions[${questionIndex}][option_2]" required>
                </div>

                <div class="form-group">
                    <label>Option 3:</label>
                    <input type="text" class="form-control" name="questions[${questionIndex}][option_3]" required>
                </div>

                <div class="form-group">
                    <label>Option 4:</label>
                    <input type="text" class="form-control" name="questions[${questionIndex}][option_4]" required>
                </div>

                <div class="form-group">
                    <label>Correct Option (1-4):</label>
                    <input type="number" class="form-control" name="questions[${questionIndex}][correct_option]"
                        min="1" max="4" required>
                </div>
            `;
            questionContainer.appendChild(newQuestion);
            questionIndex++;
        }
    </script>
</body>
</html>
