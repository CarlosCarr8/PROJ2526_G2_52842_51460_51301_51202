<?php
$role = $_SESSION["role"] ?? "";
$basePath = $basePath ?? "";
?>

<aside class="sidebar">
    <div class="sidebar-title">
        Menu
    </div>

    <a href="<?= $basePath ?>dashboard.php">Dashboard</a>

    <?php if ($role === "student" || $role === "professor"): ?>
        <a href="<?= $basePath ?>pages/spaces.php">Espaços</a>
        <a href="<?= $basePath ?>pages/equipment.php">Equipamentos</a>
        <a href="<?= $basePath ?>pages/mobility.php">Mobilidade</a>
        <a href="<?= $basePath ?>pages/my_reservations.php">Minhas Reservas</a>
        <a href="<?= $basePath ?>pages/lss.php">LSS</a>
    <?php endif; ?>

    <?php if ($role === "funcionario"): ?>
        <a href="<?= $basePath ?>pages/iot_dashboard.php">Monitorização IoT</a>
        <a href="<?= $basePath ?>pages/alerts.php">Alertas</a>
        <a href="<?= $basePath ?>pages/sustainability.php">Sustentabilidade</a>
    <?php endif; ?>

    <?php if ($role === "administrator"): ?>
        <a href="<?= $basePath ?>admin/users.php">Utilizadores</a>
        <a href="<?= $basePath ?>admin/resources.php">Recursos</a>
        <a href="<?= $basePath ?>admin/sensors.php">Sensores</a>
        <a href="<?= $basePath ?>pages/alerts.php">Alertas</a>
        <a href="<?= $basePath ?>admin/reports.php">Relatórios</a>
        <a href="<?= $basePath ?>pages/lss.php">LSS</a>
    <?php endif; ?>
</aside>  