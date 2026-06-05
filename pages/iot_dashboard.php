<?php
include __DIR__ . "/../includes/auth_check.php";
include __DIR__ . "/../includes/role_check.php";
requireRole(["administrator", "funcionario", "professor"]);

require_once __DIR__ . "/../config/db.php";

$pageTitle = "Monitorização IoT - PCU";
$basePath = "../";

$stats = [
    "total_sensors" => $pdo->query("SELECT COUNT(*) FROM sensors")->fetchColumn(),
    "active_sensors" => $pdo->query("SELECT COUNT(*) FROM sensors WHERE status = 'active'")->fetchColumn(),
    "open_alerts" => $pdo->query("SELECT COUNT(*) FROM alerts WHERE status = 'open'")->fetchColumn(),
    "critical_readings" => $pdo->query("SELECT COUNT(*) FROM sensor_readings WHERE status = 'critical'")->fetchColumn()
];

$sql = "
    SELECT
        s.sensor_id,
        s.code,
        s.name AS sensor_name,
        s.status AS sensor_status,
        st.type_name,
        st.unit,
        r.name AS resource_name,
        r.code AS resource_code,
        (
            SELECT sr.value
            FROM sensor_readings sr
            WHERE sr.sensor_id = s.sensor_id
            ORDER BY sr.reading_time DESC
            LIMIT 1
        ) AS latest_value,
        (
            SELECT sr.status
            FROM sensor_readings sr
            WHERE sr.sensor_id = s.sensor_id
            ORDER BY sr.reading_time DESC
            LIMIT 1
        ) AS latest_status,
        (
            SELECT sr.reading_time
            FROM sensor_readings sr
            WHERE sr.sensor_id = s.sensor_id
            ORDER BY sr.reading_time DESC
            LIMIT 1
        ) AS latest_time
    FROM sensors s
    INNER JOIN sensor_types st ON s.sensor_type_id = st.sensor_type_id
    INNER JOIN resources r ON s.resource_id = r.resource_id
    ORDER BY st.type_name, r.name
";

$readings = $pdo->query($sql)->fetchAll();

include __DIR__ . "/../includes/header.php";
?>

<div class="app-layout">
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Monitorização IoT</h2>
                <p class="text-muted">Leituras simuladas de temperatura, energia, qualidade do ar e ocupação.</p>
            </div>

            <?php if ($_SESSION["role"] === "administrator"): ?>
                <a href="../admin/sensors.php" class="btn btn-primary">Gerir Sensores</a>
            <?php endif; ?>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm p-3">
                    <span class="text-muted">Sensores</span>
                    <h3><?= $stats["total_sensors"] ?></h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm p-3">
                    <span class="text-muted">Sensores ativos</span>
                    <h3><?= $stats["active_sensors"] ?></h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm p-3">
                    <span class="text-muted">Alertas abertos</span>
                    <h3><?= $stats["open_alerts"] ?></h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm p-3">
                    <span class="text-muted">Leituras críticas</span>
                    <h3><?= $stats["critical_readings"] ?></h3>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header fw-bold">Últimas leituras</div>

            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Recurso</th>
                            <th>Sensor</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Estado da leitura</th>
                            <th>Hora</th>
                            <th>Sensor</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($readings)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Sem sensores registados.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($readings as $row): ?>
                        <?php
                            $badge = "bg-success";
                            if ($row["latest_status"] === "warning") $badge = "bg-warning text-dark";
                            if ($row["latest_status"] === "critical") $badge = "bg-danger";
                        ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($row["resource_name"]) ?>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($row["resource_code"]) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($row["sensor_name"]) ?>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($row["code"]) ?></small>
                            </td>
                            <td><?= htmlspecialchars($row["type_name"]) ?></td>
                            <td>
                                <?php if ($row["latest_value"] !== null): ?>
                                    <strong><?= htmlspecialchars($row["latest_value"]) ?></strong>
                                    <?= htmlspecialchars($row["unit"] ?? "") ?>
                                <?php else: ?>
                                    <span class="text-muted">Sem leitura</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row["latest_status"]): ?>
                                    <span class="badge <?= $badge ?>"><?= htmlspecialchars($row["latest_status"]) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">sem dados</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row["latest_time"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($row["sensor_status"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
