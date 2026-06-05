<?php
include __DIR__ . "/../includes/auth_check.php";
include __DIR__ . "/../includes/role_check.php";
requireRole(["administrator"]);

require_once __DIR__ . "/../config/db.php";

$pageTitle = "Gestão de Sensores - PCU";
$basePath = "../";

$sql = "
    SELECT
        s.sensor_id,
        s.code,
        s.name,
        s.status,
        s.alert_limit_min,
        s.alert_limit_max,
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
    ORDER BY s.created_at DESC
";

$sensors = $pdo->query($sql)->fetchAll();

include __DIR__ . "/../includes/header.php";
?>

<div class="app-layout">
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Gestão de Sensores</h2>
                <p class="text-muted">Registar sensores, simular leituras e gerar alertas.</p>
            </div>

            <a href="sensor_form.php" class="btn btn-primary">
                Novo Sensor
            </a>
        </div>

        <?php if (isset($_GET["success"])): ?>
            <div class="alert alert-success">Operação realizada com sucesso.</div>
        <?php endif; ?>

        <?php if (isset($_GET["error"])): ?>
            <div class="alert alert-danger">Ocorreu um erro na operação.</div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Sensor</th>
                            <th>Tipo</th>
                            <th>Recurso</th>
                            <th>Última leitura</th>
                            <th>Limites</th>
                            <th>Estado</th>
                            <th style="min-width:260px;">Simular leitura</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($sensors)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                Nenhum sensor registado.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($sensors as $sensor): ?>
                        <tr>
                            <td><?= htmlspecialchars($sensor["code"]) ?></td>
                            <td><?= htmlspecialchars($sensor["name"]) ?></td>
                            <td>
                                <?= htmlspecialchars($sensor["type_name"]) ?>
                                <?php if ($sensor["unit"]): ?>
                                    <span class="text-muted">(<?= htmlspecialchars($sensor["unit"]) ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($sensor["resource_name"]) ?>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($sensor["resource_code"]) ?></small>
                            </td>
                            <td>
                                <?php if ($sensor["latest_value"] !== null): ?>
                                    <strong><?= htmlspecialchars($sensor["latest_value"]) ?></strong>
                                    <?= htmlspecialchars($sensor["unit"] ?? "") ?>
                                    <br>
                                    <?php
                                        $badge = "bg-success";
                                        if ($sensor["latest_status"] === "warning") $badge = "bg-warning text-dark";
                                        if ($sensor["latest_status"] === "critical") $badge = "bg-danger";
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= htmlspecialchars($sensor["latest_status"]) ?></span>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($sensor["latest_time"]) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Sem leituras</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                Min: <?= htmlspecialchars($sensor["alert_limit_min"] ?? "-") ?>
                                <br>
                                Max: <?= htmlspecialchars($sensor["alert_limit_max"] ?? "-") ?>
                            </td>
                            <td>
                                <?php if ($sensor["status"] === "active"): ?>
                                    <span class="badge bg-success">Ativo</span>
                                <?php elseif ($sensor["status"] === "maintenance"): ?>
                                    <span class="badge bg-warning text-dark">Manutenção</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="../actions/save_sensor_action.php" method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="mode" value="add_reading">
                                    <input type="hidden" name="sensor_id" value="<?= $sensor["sensor_id"] ?>">
                                    <input type="number" step="0.01" name="value" class="form-control form-control-sm" placeholder="Valor" required>
                                    <button type="submit" class="btn btn-sm btn-dark">Guardar</button>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="sensor_form.php?id=<?= $sensor["sensor_id"] ?>" class="btn btn-sm btn-outline-primary">
                                    Editar
                                </a>

                                <?php if ($sensor["status"] !== "inactive"): ?>
                                    <form action="../actions/save_sensor_action.php" method="POST" class="d-inline" onsubmit="return confirmAction('Desativar este sensor?');">
                                        <input type="hidden" name="mode" value="deactivate">
                                        <input type="hidden" name="sensor_id" value="<?= $sensor["sensor_id"] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Desativar
                                        </button>
                                    </form>
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
