<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    // Redirect to the login page
    header("Location: login.php");
    exit();
}

?>
<body class="bg-secondary">
<div class="col-10">
    <div class="row mt-3">
        <div class="col-md-6 col-lg-3 mt-3">
            <div class="card bg-success">
                <div class="card-body cursor-pointer text-center">
                    <h5 class="card-title">Examiner</h5>
                    <?php
                        // Fetch the count of examiners from the database
                        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM Examiner");
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $examinerCount = $row['count'];
                    ?>
                    <p class="card-text"><?php echo $examinerCount; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mt-3">
            <div class="card bg-info">
                <div class="card-body cursor-pointer text-center">
                    <h5 class="card-title">Employee</h5>
                    <?php
                        // Fetch the count of employees from the database
                        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM Employee");
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $employeeCount = $row['count'];
                    ?>
                    <p class="card-text"><?php echo $employeeCount; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mt-3">
            <div class="card bg-warning">
                <div class="card-body cursor-pointer text-center">
                    <h5 class="card-title">Test</h5>
                    <?php 
            // Fetch the count of tests from the database
            $stmt = $conn->prepare("SELECT COUNT(DISTINCT test_id)  AS count FROM Test");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $testCount = $row['count'];
            ?>
            <p class="card-text"><?php echo $testCount; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>