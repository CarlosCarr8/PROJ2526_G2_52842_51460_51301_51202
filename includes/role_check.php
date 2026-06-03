<?php

if (!function_exists('pcu_base_url')) {
    function pcu_base_url() {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $dir = preg_replace('#/(admin|pages|actions)$#', '', $dir);
        return rtrim($dir, '/');
    }
}

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