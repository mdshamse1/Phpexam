<?php
// Include the database connection file
require 'connectDb.php';


// Get the test_id from the URL parameter
if (isset($_GET['test_id'])) {
    $test_id = $_GET['test_id'];

    // Create a new MySQLi object and establish the database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare the SQL statement to fetch questions by test_id
    $sql = "SELECT * FROM `Question` WHERE `test_id` = '$test_id'";

    // Execute the SQL statement
    $result = $conn->query($sql);

    // Fetch all questions as an associative array
    $questions = array();
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }

    // Close the database connection
    $conn->close();

    // Return the questions as a JSON response
    header('Content-Type: application/json');
    echo json_encode($questions);
}
?>
