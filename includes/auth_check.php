<?php

if (session_status() === PHP_SESSION_NONE) { //se a sessão ainda não existir
    session_start(); //inicia a sessão
}

if (!function_exists('pcu_base_url')) { //verifica se a função já existe
    function pcu_base_url() {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); //obtém o diretório atual
        $dir = preg_replace('#/(admin|pages|actions)$#', '', $dir); //remove pastas específicas do caminho
        return rtrim($dir, '/'); //remove a barra final
    }
}

if (!isset($_SESSION["user_id"])) { //se o utilizador não estiver autenticado
    header("Location: /pcu-project/login.php");
    exit;
}

?>