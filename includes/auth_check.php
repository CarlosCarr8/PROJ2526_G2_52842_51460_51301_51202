<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {

    // THIS IS THE LOCATION OF THE PROJECT FILE LOGIN.PHP, CHANGE IT IF YOU MOVE THE FILE
    // TO ANOTHER LOCATION OR INPUT THE CORRECT PATH TO THE LOGIN.PHP FILE
    header("Location: /pcu-project/login.php");
    exit;
}
?>