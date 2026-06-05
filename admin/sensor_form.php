<?php 

// verifica se o utilizador está autenticado
include __DIR__ . "/../includes/auth_check.php";

// verifica se o utilizador tem permissões válidas
include __DIR__ . "/../includes/role_check.php";

// permite acesso apenas a administradores
requireRole(["administrator"]);

// ligação à base de dados
require_once __DIR__ . "/../config/db.php";

// define o título da página
$pageTitle = "Sensor - PCU";

// define o caminho base do projeto
$basePath = "../";

// função para proteger dados apresentados no HTML
function e($value) 
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

// obtém o ID do sensor enviado pela URL
$sensorId = $_GET["id"] ?? null;

// verifica se é modo de edição
$isEdit = !empty($sensorId);

// variável que irá guardar os dados do sensor
$sensor = null;

// verifica se está a editar um sensor
if ($isEdit) 
{
    // procura o sensor na base de dados
    $stmt = $pdo->prepare("
        SELECT *
        FROM sensors
        WHERE sensor_id = ?
    ");

    $stmt->execute([$sensorId]);

    // obtém os dados do sensor
    $sensor = $stmt->fetch();

    // verifica se o sensor existe
    if (!$sensor) 
    {
        header("Location: sensors.php?error=1");
        exit;
    }
}

// obtém todos os tipos de sensores
$sensorTypes = $pdo
    ->query("
        SELECT *
        FROM sensor_types
        ORDER BY type_name
    ")
    ->fetchAll();

// obtém todos os recursos ativos
$resources = $pdo->query("
    SELECT
        r.resource_id,
        r.code,
        r.name,
        rt.type_name
    FROM resources r

    INNER JOIN resource_types rt 
        ON r.resource_type_id = rt.resource_type_id

    WHERE r.status <> 'inactive'

    ORDER BY rt.type_name, r.name
")->fetchAll();

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
                <!-- título dinâmico -->
                <h2>
                    <?= $isEdit ? "Editar Sensor" : "Novo Sensor" ?>
                </h2>

                <p class="text-muted">
                    Associe o sensor a um recurso
                    e defina limites de alerta.
                </p>
            </div>

            <!-- botão voltar -->
            <a 
                href="sensors.php"
                class="btn btn-outline-secondary"
            >
                Voltar
            </a>
        </div>

        <!-- formulário principal -->
        <form 
            action="../actions/save_sensor_action.php"
            method="POST"
        >

            <!-- modo da ação -->
            <input 
                type="hidden"
                name="mode"
                value="save_sensor"
            >

            <!-- ID do sensor -->
            <input 
                type="hidden"
                name="sensor_id"
                value="<?= e($sensor["sensor_id"] ?? "") ?>"
            >

            <!-- cartão principal -->
            <div class="card shadow-sm">
                <div class="card-body row g-3">
                    <!-- código do sensor -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Código
                        </label>
                        <input 
                            type="text"
                            name="code"
                            class="form-control"
                            required
                            value="<?= e($sensor["code"] ?? "") ?>"
                        >
                    </div>

                    <!-- nome do sensor -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Nome
                        </label>

                        <input 
                            type="text"
                            name="name"
                            class="form-control"
                            required
                            value="<?= e($sensor["name"] ?? "") ?>"
                        >
                    </div>

                    <!-- tipo de sensor -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Tipo de sensor
                        </label>

                        <select 
                            name="sensor_type_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Selecionar...
                            </option>
                            <?php foreach ($sensorTypes as $type): ?>

                                <option 
                                    value="<?= $type["sensor_type_id"] ?>"
                                    <?= (($sensor["sensor_type_id"] ?? "") == $type["sensor_type_id"]) ? "selected" : "" ?>
                                >

                                    <?= e($type["type_name"]) ?>

                                    <?= $type["unit"] ? "(" . e($type["unit"]) . ")" : "" ?>

                                </option>

                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- recurso associado -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Recurso associado
                        </label>

                        <select 
                            name="resource_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Selecionar...
                            </option>

                            <?php foreach ($resources as $resource): ?>

                                <option 
                                    value="<?= $resource["resource_id"] ?>"
                                    <?= (($sensor["resource_id"] ?? "") == $resource["resource_id"]) ? "selected" : "" ?>
                                >

                                    <?= e($resource["type_name"]) ?>
                                    -
                                    <?= e($resource["code"]) ?>
                                    -
                                    <?= e($resource["name"]) ?>

                                </option>

                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- estado do sensor -->
                    <div class="col-md-4">

                        <label class="form-label">
                            Estado
                        </label>

                        <select 
                            name="status"
                            class="form-select"
                            required
                        >

                            <option 
                                value="active"
                                <?= (($sensor["status"] ?? "active") === "active") ? "selected" : "" ?>
                            >
                                Ativo
                            </option>

                            <option 
                                value="inactive"
                                <?= (($sensor["status"] ?? "") === "inactive") ? "selected" : "" ?>
                            >
                                Inativo
                            </option>

                            <option 
                                value="maintenance"
                                <?= (($sensor["status"] ?? "") === "maintenance") ? "selected" : "" ?>
                            >
                                Manutenção
                            </option>
                        </select>
                    </div>

                    <!-- limite mínimo -->
                    <div class="col-md-4">
                        <label class="form-label">
                            Limite mínimo
                        </label>

                        <input 
                            type="number"
                            step="0.01"
                            name="alert_limit_min"
                            class="form-control"
                            value="<?= e($sensor["alert_limit_min"] ?? "") ?>"
                        >
                    </div>

                    <!-- limite máximo -->
                    <div class="col-md-4">

                        <label class="form-label">
                            Limite máximo
                        </label>

                        <input 
                            type="number"
                            step="0.01"
                            name="alert_limit_max"
                            class="form-control"
                            value="<?= e($sensor["alert_limit_max"] ?? "") ?>"
                        >
                    </div>
                </div>
            </div>

            <!-- botões finais -->
            <div class="mt-4">

                <!-- botão guardar -->
                <button 
                    type="submit"
                    class="btn btn-primary"
                >
                    Guardar
                </button>

                <!-- botão cancelar -->
                <a 
                    href="sensors.php"
                    class="btn btn-outline-secondary"
                >
                    Cancelar
                </a>
            </div>
        </form>
    </main>
</div>

<!-- inclui o rodapé -->
<?php include __DIR__ . "/../includes/footer.php"; ?>