<?php
session_start();
// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin" || empty($_SESSION["user_id"]) ) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Function to start the countdown timer
        function startTimer(duration, display) {
            var timer = duration, minutes, seconds;
            setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(timer);
                    document.getElementById("examForm").submit(); // Auto submit the form when time is over
                }
            }, 1000);
        }

        // Function to start the timer on form submission
        function startTest() {
            var duration = 10 * 60; // Set the duration of the test in seconds (e.g., 10 minutes)
            var display = document.querySelector('#timer');
            startTimer(duration, display);
        }
    </script>
</head>
<body class="bg-secondary">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>Test Login</h2>
                    </div>
                    <div class="card-body">
                    <form action="giveExam.php" method="post">
            <div class="mb-3">
                <label for="test_id" class="form-label">Test ID:</label>
                <input type="text" class="form-control" id="test_id" name="test_id" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Start Exam</button>
    
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>



