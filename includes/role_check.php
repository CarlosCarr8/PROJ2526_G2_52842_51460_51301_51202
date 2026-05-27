<?php
function requireRole(array $allowedRoles) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION["role"])) {
        header("Location: /pcu-project/login.php");
        exit;
    }

    if (!in_array($_SESSION["role"], $allowedRoles)) {
        header("Location: /pcu-project/dashboard.php");
        exit;
    }
}
?>  