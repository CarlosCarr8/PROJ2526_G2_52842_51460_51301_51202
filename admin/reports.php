<?php 

// verifica se o utilizador está autenticado
include __DIR__ . "/../includes/auth_check.php";

// verifica se o utilizador tem permissões válidas
include __DIR__ . "/../includes/role_check.php";

// permite acesso apenas a administradores
requireRole(["administrator"]);

// define o título da página
$pageTitle = "Relatórios - PCU";

// define o caminho base do projeto
$basePath = "../";

// inclui o cabeçalho da página
include __DIR__ . "/../includes/header.php";

?>

<div class="app-layout">
    <!-- inclui a barra lateral -->
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>
    <main class="main-content">

        <!-- título principal -->
        <h2>Relatórios</h2>

        <!-- descrição da página -->
        <p class="text-muted">
            Acesso rápido aos relatórios e indicadores do sistema.
        </p>

        <!-- cartões de acesso rápido -->
        <div class="row g-4 mt-2">
            <!-- cartão de sustentabilidade -->
            <div class="col-md-4">
                <div class="card p-3 shadow-sm">

                    <h5>Sustentabilidade</h5>

                    <p>
                        Consumo energético, alertas e sugestões de otimização.
                    </p>

                    <!-- botão para abrir relatório -->
                    <a 
                        href="../pages/sustainability.php"
                        class="btn btn-primary"
                    >
                        Abrir
                    </a>
                </div>
            </div>

            <!-- cartão de alertas -->
            <div class="col-md-4">

                <div class="card p-3 shadow-sm">

                    <h5>Alertas</h5>

                    <p>
                        Consultar e resolver alertas gerados pelos sensores.
                    </p>

                    <!-- botão para abrir página de alertas -->
                    <a 
                        href="../pages/alerts.php"
                        class="btn btn-primary"
                    >
                        Abrir
                    </a>
                </div>
            </div>

            <!-- cartão de monitorização IoT -->
            <div class="col-md-4">

                <div class="card p-3 shadow-sm">

                    <h5>Monitorização IoT</h5>

                    <p>
                        Consultar as últimas leituras dos sensores.
                    </p>

                    <!-- botão para abrir dashboard IoT -->
                    <a 
                        href="../pages/iot_dashboard.php"
                        class="btn btn-primary"
                    >
                        Abrir
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- inclui o rodapé -->
<?php include __DIR__ . "/../includes/footer.php"; ?>
