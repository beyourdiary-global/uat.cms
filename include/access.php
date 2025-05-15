<?php 
// Check access
    if (!isset($_SESSION['usr_pin_access'][$currentPagePin])) {
        // Redirect if access not allowed
        header("Location: index.php");
        exit;
    }

    // Assign access rights
    $accessActionKey = $_SESSION['usr_pin_access'][$currentPagePin];

?>