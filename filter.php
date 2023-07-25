<?php
session_start();
// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "examiner" || empty($_SESSION["user_id"]) ) {
  header("Location: login.php");
  exit();
}
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

// Function to fetch dropdown data from the Test table
function fetchDropdownData($conn, $column)
{
  try {
    $stmt = $conn->query("SELECT DISTINCT $column FROM Test");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  } catch (PDOException $e) {
    die("Error fetching dropdown data: " . $e->getMessage());
  }
}

// Function to fetch filtered test details from the database
function getFilteredTestsFromDatabase($conn, $filters)
{
  $sql = "SELECT * FROM Test WHERE 1";

  if (isset($filters['business']) && !empty($filters['business'])) {
    $business = $filters['business'];
    $sql .= " AND Business_vertical IN ('" . implode("','", $business) . "')";
  }

  if (isset($filters['broad-category']) && $filters['broad-category'] !== "all") {
    $broadCategory = $filters['broad-category'];
    $sql .= " AND Broad_category = '$broadCategory'";
  }

  if (isset($filters['department']) && $filters['department'] !== "all") {
    $department = $filters['department'];
    $sql .= " AND Department_name = '$department'";
  }

  if (isset($filters['sub-department']) && $filters['sub-department'] !== "all") {
    $subDepartment = $filters['sub-department'];
    $sql .= " AND Sub_department = '$subDepartment'";
  }

  try {
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    die("Error fetching filtered test details: " . $e->getMessage());
  }
}

// Fetch dropdown data from the Test table for Business Vertical, Broad Category, Department Name, and Sub Department
$businessVerticalOptions = fetchDropdownData($conn, "Business_vertical");
$broadCategoryOptions = fetchDropdownData($conn, "Broad_category");
$departmentOptions = fetchDropdownData($conn, "Department_name");
$subDepartmentOptions = fetchDropdownData($conn, "Sub_department");

// Process the form submission and fetch filtered test details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply_filters'])) {
  // Get the selected filter options from the $_POST array
  $filters = array(
    'business' => isset($_POST['business']) ? $_POST['business'] : array(),
    'broad-category' => isset($_POST['broad-category']) ? $_POST['broad-category'] : "all",
    'department' => isset($_POST['department']) ? $_POST['department'] : "all",
    'sub-department' => isset($_POST['sub-department']) ? $_POST['sub-department'] : "all"
  );

  // Fetch filtered test details from the database
  $filteredTests = getFilteredTestsFromDatabase($conn, $filters);


  // Fetch filtered employees from the database
  $filteredEmployees = getFilteredEmployeesFromDatabase($conn, $filters);
}
// Function to fetch filtered employees from the database, including email addresses
function getFilteredEmployeesFromDatabase($conn, $filters)
{
  $sql = "SELECT * FROM Employee WHERE 1";

  if (isset($filters['business']) && !empty($filters['business'])) {
    $business = $filters['business'];
    $sql .= " AND Business_vertical IN ('" . implode("','", $business) . "')";
  }

  if (isset($filters['broad-category']) && $filters['broad-category'] !== "all") {
    $broadCategory = $filters['broad-category'];
    $sql .= " AND Broad_category = '$broadCategory'";
  }

  if (isset($filters['department']) && $filters['department'] !== "all") {
    $department = $filters['department'];
    $sql .= " AND Department_name = '$department'";
  }

  if (isset($filters['sub-department']) && $filters['sub-department'] !== "all") {
    $subDepartment = $filters['sub-department'];
    $sql .= " AND Sub_department = '$subDepartment'";
  }

  try {
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    die("Error fetching filtered employees: " . $e->getMessage());
  }
}

// Function to send an alert email to an employee
function sendAlertEmail($email)
{
  $to = $email;
  $subject = "Alert: Important Information";
  $message = "Dear Employee, \n\nThis is an alert regarding important information. Please take note. \n\nBest regards, \nYour Company";

  // You can use PHP's built-in mail function or a third-party library like PHPMailer to send emails.
  // Here's an example using PHP's mail function:

  // Additional headers
  $headers = "From: mdshamse79860@gmail.com" . "\r\n";

  // Send the email
  mail($to, $subject, $message, $headers);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dropdown Example</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-secondary">
<?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <!-- Filter Section -->
  <div class="col-md-10">
    <div class="container mt-5">
      <h2>Filter Options</h2>
      <form id="filter-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="row mb-3">
          <div class="col">
            <label for="business-vertical">Business Vertical:</label>
            <select class="form-control" name="business[]" id="business-vertical" multiple>
              <?php
              foreach ($businessVerticalOptions as $option) {
                $selected = in_array($option, $_POST['business'] ?? array()) ? 'selected' : '';
                echo '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
              }
              ?>
            </select>
          </div>

          <!-- Broad Category -->
          <div class="col">
            <label for="broad-category">Broad Category:</label>
            <select class="form-control" name="broad-category" id="broad-category">
              <!-- Update the name attribute here -->
              <option value="all" <?php if (isset($_POST['broad-category']) && $_POST['broad-category'] === 'all')
                echo 'selected'; ?>>All</option>
              <?php
              foreach ($broadCategoryOptions as $option) {
                $selected = ($_POST['broad-category'] ?? '') === $option ? 'selected' : '';
                echo '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
              }
              ?>
            </select>
          </div>

          <!-- Department -->
          <div class="col">
            <label for="department">Department:</label>
            <select class="form-control" name="department" id="department">
              <option value="all" <?php if (isset($_POST['department']) && $_POST['department'] === 'all')
                echo 'selected'; ?>>All</option>
              <?php
              foreach ($departmentOptions as $option) {
                $selected = ($_POST['department'] ?? '') === $option ? 'selected' : '';
                echo '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
              }
              ?>
            </select>
          </div>

          <!-- Sub Department -->
          <div class="col">
            <label for="sub-department">Sub Department:</label>
            <select class="form-control" name="sub-department" id="sub-department">
              <option value="all" <?php if (isset($_POST['sub-department']) && $_POST['sub-department'] === 'all')
                echo 'selected'; ?>>All</option>
              <?php
              foreach ($subDepartmentOptions as $option) {
                $selected = ($_POST['sub-department'] ?? '') === $option ? 'selected' : '';
                echo '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
              }
              ?>
            </select>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" name="apply_filters">Apply Filters</button>
      </form>
    </div>
    <!-- Modal to display matching employees -->
    <div class="modal" id="employeeModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Matching Employees</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <ul id="matchingEmployeesList">
              <!-- List of matching employees will be populated here -->
            </ul>
          </div>
        </div>
      </div>
    </div>


    <!-- Display filtered test details (if any) -->
    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($filteredTests)): ?>
      <div class="container mt-5">
        <?php if (count($filteredTests) > 0): ?>
          <h2>Filtered Test Details<!-- "See Employee" button outside the table -->
            <button type="button" class="btn btn-info btn-sm float-right" id="see-employee-outside">See Employee</button>
          </h2>

          <table class="table">
            <thead>
              <tr>
                <th>Business Vertical</th>
                <th>Broad Category</th>
                <th>Department Name</th>
                <th>Sub Department</th>
                <th>Test Name</th>
                <th>Test ID</th>
                <th>Enabled</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($filteredTests as $test): ?>
                <tr>
                  <td>
                    <?php echo $test['Business_vertical']; ?>
                  </td>
                  <td>
                    <?php echo $test['Broad_category']; ?>
                  </td>
                  <td>
                    <?php echo $test['Department_name']; ?>
                  </td>
                  <td>
                    <?php echo $test['Sub_department']; ?>
                  </td>
                  <td>
                    <?php echo $test['test_name']; ?>
                  </td>
                  <td>
                    <?php echo $test['test_id']; ?>
                  </td>
                  <td>
                    <?php echo $test['Enabled']; ?>
                  </td>
                  <td>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <!-- Add a hidden input field inside the table row -->
          <input type="hidden" id="row-business-<?php echo $test['test_id']; ?>"
            value="<?php echo $test['Business_vertical']; ?>">
          <input type="hidden" id="row-category-<?php echo $test['test_id']; ?>"
            value="<?php echo $test['Broad_category']; ?>">
          <input type="hidden" id="row-department-<?php echo $test['test_id']; ?>"
            value="<?php echo $test['Department_name']; ?>">
          <input type="hidden" id="row-subDepartment-<?php echo $test['test_id']; ?>"
            value="<?php echo $test['Sub_department']; ?>">
        <?php else: ?>
          <p>No filtered test details found.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Bootstrap JS (jQuery and Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
    <script>
      // Event listener for the "See Employee" button outside the table
      document.getElementById('see-employee-outside').addEventListener('click', function () {
        // Get the selected filter options from the form
        const business = document.getElementById('business-vertical').value;
        const category = document.getElementById('broad-category').value;
        const department = document.getElementById('department').value;
        const subDepartment = document.getElementById('sub-department').value;

        // Call the openEmployeeModal function with the selected filter options
        openEmployeeModal(business, category, department, subDepartment);
      });

      function openEmployeeModal(business, category, department, subDepartment) {
        // Show loading indicator while the request is in progress
        var modalBody = document.getElementById('matchingEmployeesList');
        modalBody.innerHTML = '<li>Loading...</li>';

        // Send the filter criteria to the server using AJAX
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
          if (this.readyState == 4 && this.status == 200) {
            // The server successfully fetched the matching employees.
            var employees = JSON.parse(this.responseText);

            // Populate the modal with the employee data
            modalBody.innerHTML = ''; // Clear loading indicator
            employees.forEach(function (employee) {
              modalBody.innerHTML += '<li><strong>Employee ID:</strong> ' + employee.employee_id + ', <strong>Name:</strong> ' + employee.employee_name + '</li>';
            });

            // Show the modal
            var employeeModal = new bootstrap.Modal(document.getElementById('employeeModal'));
            employeeModal.show();
          } else if (this.readyState == 4 && this.status != 200) {
            // Some error occurred while fetching the employees.
            modalBody.innerHTML = ''; // Clear loading indicator
            alert("Failed to fetch matching employees.");
          }
        };
        xhttp.open("POST", "fetch_matching_employees.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("business=" + business + "&category=" + category + "&department=" + department + "&subDepartment=" + subDepartment);
      }
    </script>
  </div>


</body>

</html>
