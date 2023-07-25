<?php
session_start();
// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "examiner" || empty($_SESSION["user_id"]) ) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Questions</title>
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
                        <h2>View Questions</h2>
                    </div>
                    <div class="card-body">
                        <form id="questionForm" action="updateQuestionId.php" method="POST">
                            <div class="form-group">
                                <label>Test ID:</label>
                                <input type="text" class="form-control" id="testIdInput" required>
                            </div>
                            <button type="submit" class="btn btn-primary">View Questions</button>
                        </form>

                        <div id="questionContainer" style="display: none;">
                            <h3>Questions:</h3>
                            <div id="questionsList"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Event listener for form submission
        document.getElementById('questionForm').addEventListener('submit', function (e) {
            e.preventDefault();
            var testId = document.getElementById('testIdInput').value;
            retrieveQuestions(testId);
        });

        // Function to retrieve questions via AJAX request
        function retrieveQuestions(testId) {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    displayQuestions(JSON.parse(this.responseText));
                }
            };
            xhttp.open('GET', 'getQuestions.php?test_id=' + testId, true);
            xhttp.send();
        }

        // Function to display questions on the page
        function displayQuestions(questions) {
            var questionContainer = document.getElementById('questionContainer');
            var questionsList = document.getElementById('questionsList');

            // Clear previous questions if any
            questionsList.innerHTML = '';

            if (questions.length > 0) {
                questionContainer.style.display = 'block';

                // Loop through each question and display its details
                questions.forEach(function (question) {
                    var questionId = question.question_id;
                    var questionText = question.question_text;
                    var option1 = question.option_1;
                    var option2 = question.option_2;
                    var option3 = question.option_3;
                    var option4 = question.option_4;
                    var correctOption = question.correct_option;

                    // Create HTML elements to display question details
                    var questionDiv = document.createElement('div');
                    questionDiv.className = 'card mt-3';
                    questionDiv.innerHTML = `
                        <div class="card-body">
                            <h5>Question ID: ${questionId}</h5>
                            <p>Question Text: ${questionText}</p>
                            <p>Option 1: ${option1}</p>
                            <p>Option 2: ${option2}</p>
                            <p>Option 3: ${option3}</p>
                            <p>Option 4: ${option4}</p>
                            <p>Correct Option: ${correctOption}</p>
                        </div>
                    `;

                    questionsList.appendChild(questionDiv);
                });
            } else {
                questionContainer.style.display = 'none';
                alert('No questions found for the specified test.');
            }
        }
    </script>
</body>

</html>