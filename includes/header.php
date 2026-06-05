<?php

if (session_status() === PHP_SESSION_NONE) { //se a sessão ainda não existir
    session_start(); //inicia a sessão
}

$pageTitle = $pageTitle ?? "PCU"; //define o título da página
$basePath = $basePath ?? ""; //define o caminho base

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title> <!-- título da página -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS próprio -->
    <link href="<?= $basePath ?>assets/css/style.css" rel="stylesheet">
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
<a class="navbar-brand" href="/PROJ2526_G2_52842_51460_51301_51202/dashboard.php">PCU</a>
    <div class="ms-auto">
        <?php if (isset($_SESSION["user_id"])): //se o utilizador estiver autenticado ?>
            <span class="text-white me-3">
                <?= htmlspecialchars($_SESSION["name"]) ?>
                |
                <?= htmlspecialchars($_SESSION["role"]) ?>
            </span>

            <a href="<?= $basePath ?>logout.php" class="btn btn-outline-light btn-sm">
                Terminar sessão
            </a>

        <?php else: //se o utilizador não estiver autenticado ?>
            <a href="<?= $basePath ?>login.php" class="btn btn-outline-light btn-sm">
                Iniciar sessão
            </a>
        <?php endif; ?>
    </div>
</nav>