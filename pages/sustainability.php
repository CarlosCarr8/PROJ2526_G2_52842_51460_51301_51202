<?php
include __DIR__ . "/../includes/auth_check.php";
include __DIR__ . "/../includes/role_check.php";
requireRole(["administrator", "funcionario"]);

require_once __DIR__ . "/../config/db.php";

$pageTitle = "Sustentabilidade - PCU";
$basePath = "../";

$periodStart = $_GET["start"] ?? date("Y-m-01");
$periodEnd = $_GET["end"] ?? date("Y-m-t");

$totalStmt = $pdo->prepare("
    SELECT COALESCE(SUM(sr.value), 0)
    FROM sensor_readings sr
    INNER JOIN sensors s ON sr.sensor_id = s.sensor_id
    INNER JOIN sensor_types st ON s.sensor_type_id = st.sensor_type_id
    WHERE st.type_name = 'energy'
      AND DATE(sr.reading_time) BETWEEN ? AND ?
");
$totalStmt->execute([$periodStart, $periodEnd]);
$totalEnergy = $totalStmt->fetchColumn();

$topStmt = $pdo->prepare("
    SELECT
        r.name AS resource_name,
        r.code AS resource_code,
        COALESCE(SUM(sr.value), 0) AS total_energy
    FROM sensor_readings sr
    INNER JOIN sensors s ON sr.sensor_id = s.sensor_id
    INNER JOIN sensor_types st ON s.sensor_type_id = st.sensor_type_id
    INNER JOIN resources r ON s.resource_id = r.resource_id
    WHERE st.type_name = 'energy'
      AND DATE(sr.reading_time) BETWEEN ? AND ?
    GROUP BY r.resource_id, r.name, r.code
    ORDER BY total_energy DESC
    LIMIT 5
");
$topStmt->execute([$periodStart, $periodEnd]);
$topResources = $topStmt->fetchAll();

$alertStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM alerts
    WHERE DATE(created_at) BETWEEN ? AND ?
      AND status = 'open'
");
$alertStmt->execute([$periodStart, $periodEnd]);
$openAlerts = $alertStmt->fetchColumn();

$suggestion = "Consumo dentro dos valores esperados. Continuar a acompanhar os indicadores.";
if ($totalEnergy > 500) {
    $suggestion = "Consumo energético elevado. Recomenda-se verificar iluminação, climatização e ocupação dos espaços com maior consumo.";
}
if ($openAlerts > 0) {
    $suggestion .= " Existem alertas abertos que devem ser analisados pela equipa operacional.";
}

include __DIR__ . "/../includes/header.php";
?>

<div class="app-layout">
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Sustentabilidade</h2>
                <p class="text-muted">Indicadores energéticos e sugestões de otimização.</p>
            </div>

            <button onclick="window.print()" class="btn btn-outline-secondary">Exportar / Imprimir</button>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Data inicial</label>
                        <input type="date" name="start" class="form-control" value="<?= htmlspecialchars($periodStart) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Data final</label>
                        <input type="date" name="end" class="form-control" value="<?= htmlspecialchars($periodEnd) ?>">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-dark w-100">Gerar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <span class="text-muted">Consumo energético no período</span>
                    <h2><?= number_format((float)$totalEnergy, 2, ",", ".") ?> kWh</h2>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <span class="text-muted">Alertas abertos no período</span>
                    <h2><?= (int)$openAlerts ?></h2>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">Sugestão de otimização</div>
            <div class="card-body">
                <?= htmlspecialchars($suggestion) ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header fw-bold">Recursos com maior consumo energético</div>

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
                    <?php if (empty($topResources)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                Sem dados energéticos para este período.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($topResources as $resource): ?>
                        <tr>
                            <td><?= htmlspecialchars($resource["resource_name"]) ?></td>
                            <td><?= htmlspecialchars($resource["resource_code"]) ?></td>
                            <td><?= number_format((float)$resource["total_energy"], 2, ",", ".") ?> kWh</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
