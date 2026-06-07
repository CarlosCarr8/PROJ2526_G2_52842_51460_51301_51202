<?php 

// verifica se o utilizador está autenticado
include __DIR__ . "/../includes/auth_check.php";

// verifica se o utilizador tem permissões válidas
include __DIR__ . "/../includes/role_check.php";

// permite acesso apenas a administradores e funcionários
requireRole(["administrator", "funcionario"]);

// ligação à base de dados
require_once __DIR__ . "/../config/db.php";

// título da página
$pageTitle = "Sustentabilidade - PCU";

// caminho base do projeto
$basePath = "../";

// obtém a data inicial enviada pelo formulário
// caso não exista, usa o primeiro dia do mês atual
$periodStart = $_GET["start"] ?? date("Y-m-01");

// obtém a data final enviada pelo formulário
// caso não exista, usa o último dia do mês atual
$periodEnd = $_GET["end"] ?? date("Y-m-t");

// consulta o consumo energético total no período selecionado
$totalStmt = $pdo->prepare("
    SELECT COALESCE(SUM(sr.value), 0)
    FROM sensor_readings sr
    INNER JOIN sensors s 
        ON sr.sensor_id = s.sensor_id
    INNER JOIN sensor_types st 
        ON s.sensor_type_id = st.sensor_type_id
    WHERE st.type_name = 'energy'
      AND DATE(sr.reading_time) BETWEEN ? AND ?
");

// executa a consulta
$totalStmt->execute([$periodStart, $periodEnd]);

// obtém o valor total de energia
$totalEnergy = $totalStmt->fetchColumn();

// consulta os 5 recursos com maior consumo energético
$topStmt = $pdo->prepare("
    SELECT
        r.name AS resource_name,
        r.code AS resource_code,
        COALESCE(SUM(sr.value), 0) AS total_energy
    FROM sensor_readings sr
    INNER JOIN sensors s 
        ON sr.sensor_id = s.sensor_id
    INNER JOIN sensor_types st 
        ON s.sensor_type_id = st.sensor_type_id
    INNER JOIN resources r 
        ON s.resource_id = r.resource_id
    WHERE st.type_name = 'energy'
      AND DATE(sr.reading_time) BETWEEN ? AND ?
    GROUP BY 
        r.resource_id,
        r.name,
        r.code
    ORDER BY total_energy DESC
    LIMIT 5
");

// executa a consulta
$topStmt->execute([$periodStart, $periodEnd]);

// obtém os recursos encontrados
$topResources = $topStmt->fetchAll();

// consulta o número de alertas abertos no período
$alertStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM alerts
    WHERE DATE(created_at) BETWEEN ? AND ?
      AND status = 'open'
");

// executa a consulta
$alertStmt->execute([$periodStart, $periodEnd]);

// obtém o número de alertas abertos
$openAlerts = $alertStmt->fetchColumn();

// mensagem padrão de sugestão
$suggestion = "Consumo dentro dos valores esperados. Continuar a acompanhar os indicadores.";

// verifica se o consumo energético é elevado
if ($totalEnergy > 500) 
{
    $suggestion = "
        Consumo energético elevado.
        Recomenda-se verificar iluminação,
        climatização e ocupação dos espaços
        com maior consumo.
    ";
}

// verifica se existem alertas abertos
if ($openAlerts > 0) 
{
    $suggestion .= "
        Existem alertas abertos que devem
        ser analisados pela equipa operacional.
    ";
}

// inclui o cabeçalho da página
include __DIR__ . "/../includes/header.php";

?>

<div class="app-layout">

    <!-- inclui a barra lateral -->
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>
    <main class="main-content">

        <!-- cabeçalho principal -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Sustentabilidade</h2>

                <p class="text-muted">
                    Indicadores energéticos e sugestões de otimização.
                </p>
            </div>

            <!-- botão para exportar ou imprimir -->
            <button 
                onclick="window.print()" 
                class="btn btn-outline-secondary"
            >
                Exportar / Imprimir
            </button>
        </div>

        <!-- formulário de filtro por datas -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <!-- data inicial -->
                    <div class="col-md-4">

                        <label class="form-label">
                            Data inicial
                        </label>

                        <input 
                            type="date"
                            name="start"
                            class="form-control"
                            value="<?= htmlspecialchars($periodStart) ?>"
                        >

                    </div>

                    <!-- data final -->
                    <div class="col-md-4">

                        <label class="form-label">
                            Data final
                        </label>

                        <input 
                            type="date"
                            name="end"
                            class="form-control"
                            value="<?= htmlspecialchars($periodEnd) ?>"
                        >

                    </div>

                    <!-- botão gerar relatório -->
                    <div class="col-md-2 d-flex align-items-end">

                        <button class="btn btn-dark w-100">
                            Gerar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- cartões de indicadores -->
        <div class="row g-4 mb-4">
            <!-- consumo energético -->
            <div class="col-md-6">
                <div class="card shadow-sm p-4">

                    <span class="text-muted">
                        Consumo energético no período
                    </span>

                    <h2>
                        <?= number_format((float)$totalEnergy, 2, ",", ".") ?> kWh
                    </h2>
                </div>
            </div>

            <!-- alertas abertos -->
            <div class="col-md-6">
                <div class="card shadow-sm p-4">

                    <span class="text-muted">
                        Alertas abertos no período
                    </span>

                    <h2>
                        <?= (int)$openAlerts ?>
                    </h2>
                </div>
            </div>
        </div>

        <!-- cartão de sugestões -->
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">
                Sugestão de otimização
            </div>

            <div class="card-body">
                <?= htmlspecialchars($suggestion) ?>
            </div>
        </div>

        <!-- tabela de recursos -->
        <div class="card shadow-sm">

            <div class="card-header fw-bold">
                Recursos com maior consumo energético
            </div>

            <div class="card-body table-responsive">
                <table class="table table-hover">

                    <thead>

                        <tr>
                            <th>Recurso</th>
                            <th>Código</th>
                            <th>Consumo total</th>
                        </tr>

                    </thead>

                    <tbody>

                    <!-- verifica se existem dados -->
                    <?php if (empty($topResources)): ?>

                        <tr>

                            <td colspan="3" class="text-center text-muted">

                                Sem dados energéticos para este período.

                            </td>

                        </tr>

                    <?php endif; ?>

                    <!-- percorre os recursos encontrados -->
                    <?php foreach ($topResources as $resource): ?>

                        <tr>

                            <!-- nome do recurso -->
                            <td>
                                <?= htmlspecialchars($resource["resource_name"]) ?>
                            </td>

                            <!-- código do recurso -->
                            <td>
                                <?= htmlspecialchars($resource["resource_code"]) ?>
                            </td>

                            <!-- consumo energético -->
                            <td>
                                <?= number_format((float)$resource["total_energy"], 2, ",", ".") ?> kWh
                            </td>

                        </tr>

                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- inclui o rodapé -->
<?php include __DIR__ . "/../includes/footer.php"; ?>
