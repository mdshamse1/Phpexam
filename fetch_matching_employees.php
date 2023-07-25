<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Database connection details (Same as before)
  require 'connectDb.php';

  // Get the filter criteria from the AJAX request
  $business = $_POST['business'];
  $category = $_POST['category'];
  $department = $_POST['department'];
  $subDepartment = $_POST['subDepartment'];

  // Prepare and execute the SQL query to fetch matching employees
  $sql = "SELECT * FROM Employee WHERE Business_vertical = :business AND Broad_category = :category AND Department_name = :department AND Sub_department = :subDepartment";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':business', $business, PDO::PARAM_STR);
  $stmt->bindParam(':category', $category, PDO::PARAM_STR);
  $stmt->bindParam(':department', $department, PDO::PARAM_STR);
  $stmt->bindParam(':subDepartment', $subDepartment, PDO::PARAM_STR);
  $stmt->execute();
  $matchingEmployees = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Return the matching employees as JSON
  header('Content-Type: application/json');
  echo json_encode($matchingEmployees);
}
?>
