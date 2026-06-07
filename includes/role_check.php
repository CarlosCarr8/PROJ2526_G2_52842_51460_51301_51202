<?php

if (!function_exists('pcu_base_url')) { //verifica se a função já existe

    function pcu_base_url() {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); //obtém o diretório atual
        $dir = preg_replace('#/(admin|pages|actions)$#', '', $dir); //remove pastas específicas do caminho
        return rtrim($dir, '/'); //remove a barra final
    }
}

function requireRole(array $allowedRoles) { //verifica se o utilizador tem permissão

    if (session_status() === PHP_SESSION_NONE) { //se a sessão ainda não existir
        session_start(); //inicia a sessão
    }

    if (!isset($_SESSION["role"])) { //se não existir perfil autenticado
        header("Location: /pcu-project/login.php"); //redireciona para o login
        exit;
    }

    if (!in_array($_SESSION["role"], $allowedRoles)) { //se não tiver permissão
        header("Location: /pcu-project/dashboard.php"); //redireciona para o painel principal
        exit;
    }
}

?>