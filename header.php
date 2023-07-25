<?php
session_start();

// Check if the admin is logged in, if not redirect to the login page
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id" ]) ) {
    header("Location: login.php");
    exit();
}
?>
<nav class="navbar navbar-expand bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand text-danger " href="index.php">
      <img src="images/ckb.png" alt="CKB" width="30" height="24" class="d-inline-block align-text-top">
      CKB HOSPITAL 
    </a>
    <a class="navbar-brand text-danger " href="index.php">
    BFI
      <img src="images/logo-1.png" alt="CKB" width="30" height="24" class="d-inline-block align-text-top">
    
    </a>
  </div>
  <div>
    <!-- <a href="index.php" class="navbar-brand">
    CKB HOSPITAL & BFI
  </a> -->
  </div>
</nav>