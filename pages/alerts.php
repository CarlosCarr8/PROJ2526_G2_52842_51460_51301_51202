<?php
include __DIR__ . "/../includes/auth_check.php";
include __DIR__ . "/../includes/role_check.php";
requireRole(["administrator", "funcionario"]);

require_once __DIR__ . "/../config/db.php";

$pageTitle = "Alertas - PCU";
$basePath = "../";

$statusFilter = $_GET["status"] ?? "";

$params = [];
$where = "";

if ($statusFilter !== "") {
    $where = "WHERE a.status = ?";
    $params[] = $statusFilter;
}

$sql = "
    SELECT
        a.alert_id,
        a.alert_type,
        a.message,
        a.severity,
        a.status,
        a.created_at,
        a.resolved_at,
        s.code AS sensor_code,
        st.type_name AS sensor_type,
        r.name AS resource_name,
        r.code AS resource_code,
        u.name AS resolved_by_name
    FROM alerts a
    INNER JOIN sensors s ON a.sensor_id = s.sensor_id
    INNER JOIN sensor_types st ON s.sensor_type_id = st.sensor_type_id
    INNER JOIN resources r ON a.resource_id = r.resource_id
    LEFT JOIN users u ON a.resolved_by = u.user_id
    $where
    ORDER BY a.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$alerts = $stmt->fetchAll();

include __DIR__ . "/../includes/header.php";
?>

<div class="app-layout">
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Alertas</h2>
                <p class="text-muted">Alertas gerados por leituras críticas de sensores.</p>
            </div>

            <a href="iot_dashboard.php" class="btn btn-outline-secondary">Monitorização IoT</a>
        </div>

        <?php if (isset($_GET["success"])): ?>
            <div class="alert alert-success">Alerta resolvido com sucesso.</div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="open" <?= $statusFilter === "open" ? "selected" : "" ?>>Abertos</option>
                            <option value="resolved" <?= $statusFilter === "resolved" ? "selected" : "" ?>>Resolvidos</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-dark w-100">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Recurso</th>
                            <th>Sensor</th>
                            <th>Mensagem</th>
                            <th>Severidade</th>
                            <th>Estado</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($alerts)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Nenhum alerta encontrado.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($alerts as $alert): ?>
                        <?php
                            $severityClass = "bg-secondary";
                            if ($alert["severity"] === "low") $severityClass = "bg-info text-dark";
                            if ($alert["severity"] === "medium") $severityClass = "bg-warning text-dark";
                            if ($alert["severity"] === "high") $severityClass = "bg-danger";
                            if ($alert["severity"] === "critical") $severityClass = "bg-danger";
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($alert["created_at"]) ?></td>
                            <td>
                                <?= htmlspecialchars($alert["resource_name"]) ?>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($alert["resource_code"]) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($alert["sensor_type"]) ?>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($alert["sensor_code"]) ?></small>
                            </td>
                            <td><?= htmlspecialchars($alert["message"]) ?></td>
                            <td><span class="badge <?= $severityClass ?>"><?= htmlspecialchars($alert["severity"]) ?></span></td>
                            <td>
                                <?php if ($alert["status"] === "open"): ?>
                                    <span class="badge bg-danger">Aberto</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Resolvido</span>
                                    <br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($alert["resolved_at"] ?? "") ?>
                                        <?= $alert["resolved_by_name"] ? " por " . htmlspecialchars($alert["resolved_by_name"]) : "" ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($alert["status"] === "open"): ?>
                                    <form action="../actions/resolve_alert_action.php" method="POST" onsubmit="return confirmAction('Marcar este alerta como resolvido?');">
                                        <input type="hidden" name="alert_id" value="<?= $alert["alert_id"] ?>">
                                        <button class="btn btn-sm btn-outline-success">Resolver</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
