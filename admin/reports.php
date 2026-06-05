<?php
include __DIR__ . "/../includes/auth_check.php";
include __DIR__ . "/../includes/role_check.php";
requireRole(["administrator"]);

$pageTitle = "Relatórios - PCU";
$basePath = "../";

include __DIR__ . "/../includes/header.php";
?>

<div class="app-layout">
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="main-content">
        <h2>Relatórios</h2>
        <p class="text-muted">Acesso rápido aos relatórios e indicadores do sistema.</p>

        <div class="row g-4 mt-2">
            <div class="col-md-4">
                <div class="card p-3 shadow-sm">
                    <h5>Sustentabilidade</h5>
                    <p>Consumo energético, alertas e sugestões de otimização.</p>
                    <a href="../pages/sustainability.php" class="btn btn-primary">Abrir</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 shadow-sm">
                    <h5>Alertas</h5>
                    <p>Consultar e resolver alertas gerados pelos sensores.</p>
                    <a href="../pages/alerts.php" class="btn btn-primary">Abrir</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 shadow-sm">
                    <h5>Monitorização IoT</h5>
                    <p>Consultar as últimas leituras dos sensores.</p>
                    <a href="../pages/iot_dashboard.php" class="btn btn-primary">Abrir</a>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
