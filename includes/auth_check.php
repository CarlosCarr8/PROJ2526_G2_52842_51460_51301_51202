<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('pcu_base_url')) {
    function pcu_base_url() {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $dir = preg_replace('#/(admin|pages|actions)$#', '', $dir);
        return rtrim($dir, '/');
    }
}


if (!isset($_SESSION["user_id"])) {

    // THIS IS THE LOCATION OF THE PROJECT FILE LOGIN.PHP, CHANGE IT IF YOU MOVE THE FILE
    // TO ANOTHER LOCATION OR INPUT THE CORRECT PATH TO THE LOGIN.PHP FILE
    header("Location: /pcu-project/login.php");
    exit;
}
?>