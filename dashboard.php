<?php
include "includes/auth_check.php";

$pageTitle = "Dashboard - PCU";
$basePath = "";

$name = $_SESSION["name"];
$role = $_SESSION["role"];

include "includes/header.php";
?>

<div class="app-layout">
    <?php include "includes/sidebar.php"; ?>

    <main class="main-content">
        <h2>Bem-vindo, <?= htmlspecialchars($name) ?></h2>
        <p class="text-muted">
            Tipo de utilizador: <?= htmlspecialchars($role) ?>
        </p>

        <div class="row mt-4 g-4">

            <?php if ($role === "student"): ?>

                <div class="col-md-4">
                    <div class="card p-3 shadow-sm">
                        <h5>Reservar Espaços</h5>
                        <p>Consultar e reservar salas disponíveis.</p>
                        <a href="pages/spaces.php" class="btn btn-primary">Abrir</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3 shadow-sm">
                        <h5>Equipamentos</h5>
                        <p>Consultar e reservar equipamentos.</p>
                        <a href="pages/equipment.php" class="btn btn-primary">Abrir</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3 shadow-sm">
                        <h5>Minhas Reservas</h5>
                        <p>Ver e cancelar as suas reservas.</p>
                        <a href="pages/my_reservations.php" class="btn btn-primary">Abrir</a>
                    </div>
                </div>

            <?php elseif ($role === "professor"): ?>

                <div class="col-md-4">
                    <div class="card p-3 shadow-sm">
                        <h5>Salas e Laboratórios</h5>
                        <p>Reservar espaços para atividades letivas.</p>
                        <a href="pages/spaces.php" class="btn btn-primary">Abrir</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3 shadow-sm">
                        <h5>Equipamentos</h5>
                        <p>Reservar equipamentos disponíveis.</p>
                        <a href="pages/equipment.php" class="btn btn-primary">Abrir</a>
                    </div>
                </div>

            <?php elseif ($role === "funcionario"): ?>

                <div class="col-md-4">
                    <div class="card p-3 shadow-sm">
                        <h5>Monitorização IoT</h5>
                        <p>Consultar sensores e dados do campus.</p>
                        <a href="pages/iot_dashboard.php" class="btn btn-primary">Abrir</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3 shadow-sm">
                        <h5>Alertas</h5>
                        <p>Consultar alertas ativos.</p>
                        <a href="pages/alerts.php" class="btn btn-primary">Abrir</a>
                    </div>
                </div>

            <?php elseif ($role === "administrator"): ?>

                <div class="col-md-4">
                    <div class="card p-3 shadow-sm">
                        <h5>Gestão de Utilizadores</h5>
                        <p>Registar, editar e desativar utilizadores.</p>
                        <a href="admin/users.php" class="btn btn-primary">Abrir</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3 shadow-sm">
                        <h5>Gestão de Recursos</h5>
                        <p>Gerir salas, laboratórios, equipamentos e mobilidade.</p>
                        <a href="admin/resources.php" class="btn btn-primary">Abrir</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3 shadow-sm">
                        <h5>Sensores</h5>
                        <p>Gerir sensores IoT e limites de alerta.</p>
                        <a href="admin/sensors.php" class="btn btn-primary">Abrir</a>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </main>
</div>

<?php include "includes/footer.php"; ?>