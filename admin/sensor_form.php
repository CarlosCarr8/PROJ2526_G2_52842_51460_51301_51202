<?php
include __DIR__ . "/../includes/auth_check.php";
include __DIR__ . "/../includes/role_check.php";
requireRole(["administrator"]);

require_once __DIR__ . "/../config/db.php";

$pageTitle = "Sensor - PCU";
$basePath = "../";

function e($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

$sensorId = $_GET["id"] ?? null;
$isEdit = !empty($sensorId);
$sensor = null;

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM sensors WHERE sensor_id = ?");
    $stmt->execute([$sensorId]);
    $sensor = $stmt->fetch();

    if (!$sensor) {
        header("Location: sensors.php?error=1");
        exit;
    }
}

$sensorTypes = $pdo->query("SELECT * FROM sensor_types ORDER BY type_name")->fetchAll();

$resources = $pdo->query("
    SELECT r.resource_id, r.code, r.name, rt.type_name
    FROM resources r
    INNER JOIN resource_types rt ON r.resource_type_id = rt.resource_type_id
    WHERE r.status <> 'inactive'
    ORDER BY rt.type_name, r.name
")->fetchAll();

include __DIR__ . "/../includes/header.php";
?>

<div class="app-layout">
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><?= $isEdit ? "Editar Sensor" : "Novo Sensor" ?></h2>
                <p class="text-muted">Associe o sensor a um recurso e defina limites de alerta.</p>
            </div>

            <a href="sensors.php" class="btn btn-outline-secondary">Voltar</a>
        </div>

        <form action="../actions/save_sensor_action.php" method="POST">
            <input type="hidden" name="mode" value="save_sensor">
            <input type="hidden" name="sensor_id" value="<?= e($sensor["sensor_id"] ?? "") ?>">

            <div class="card shadow-sm">
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Código</label>
                        <input type="text" name="code" class="form-control" required value="<?= e($sensor["code"] ?? "") ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" required value="<?= e($sensor["name"] ?? "") ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tipo de sensor</label>
                        <select name="sensor_type_id" class="form-select" required>
                            <option value="">Selecionar...</option>
                            <?php foreach ($sensorTypes as $type): ?>
                                <option value="<?= $type["sensor_type_id"] ?>" <?= (($sensor["sensor_type_id"] ?? "") == $type["sensor_type_id"]) ? "selected" : "" ?>>
                                    <?= e($type["type_name"]) ?> <?= $type["unit"] ? "(" . e($type["unit"]) . ")" : "" ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Recurso associado</label>
                        <select name="resource_id" class="form-select" required>
                            <option value="">Selecionar...</option>
                            <?php foreach ($resources as $resource): ?>
                                <option value="<?= $resource["resource_id"] ?>" <?= (($sensor["resource_id"] ?? "") == $resource["resource_id"]) ? "selected" : "" ?>>
                                    <?= e($resource["type_name"]) ?> - <?= e($resource["code"]) ?> - <?= e($resource["name"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select" required>
                            <option value="active" <?= (($sensor["status"] ?? "active") === "active") ? "selected" : "" ?>>Ativo</option>
                            <option value="inactive" <?= (($sensor["status"] ?? "") === "inactive") ? "selected" : "" ?>>Inativo</option>
                            <option value="maintenance" <?= (($sensor["status"] ?? "") === "maintenance") ? "selected" : "" ?>>Manutenção</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Limite mínimo</label>
                        <input type="number" step="0.01" name="alert_limit_min" class="form-control" value="<?= e($sensor["alert_limit_min"] ?? "") ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Limite máximo</label>
                        <input type="number" step="0.01" name="alert_limit_max" class="form-control" value="<?= e($sensor["alert_limit_max"] ?? "") ?>">
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="sensors.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </main>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
