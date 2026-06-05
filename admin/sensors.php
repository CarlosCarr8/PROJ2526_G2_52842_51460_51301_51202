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
$pageTitle = "Gestão de Sensores - PCU";

// define o caminho base do projeto
$basePath = "../";

// consulta todos os sensores registados
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

        -- última leitura do sensor
        (
            SELECT sr.value
            FROM sensor_readings sr
            WHERE sr.sensor_id = s.sensor_id
            ORDER BY sr.reading_time DESC
            LIMIT 1
        ) AS latest_value,

        -- estado da última leitura
        (
            SELECT sr.status
            FROM sensor_readings sr
            WHERE sr.sensor_id = s.sensor_id
            ORDER BY sr.reading_time DESC
            LIMIT 1
        ) AS latest_status,

        -- data e hora da última leitura
        (
            SELECT sr.reading_time
            FROM sensor_readings sr
            WHERE sr.sensor_id = s.sensor_id
            ORDER BY sr.reading_time DESC
            LIMIT 1
        ) AS latest_time

    FROM sensors s

    INNER JOIN sensor_types st 
        ON s.sensor_type_id = st.sensor_type_id

    INNER JOIN resources r 
        ON s.resource_id = r.resource_id

    ORDER BY s.created_at DESC
";

// executa a consulta
$sensors = $pdo->query($sql)->fetchAll();

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

                <h2>Gestão de Sensores</h2>

                <p class="text-muted">
                    Registar sensores, simular leituras e gerar alertas.
                </p>

            </div>

            <!-- botão para criar novo sensor -->
            <a 
                href="sensor_form.php"
                class="btn btn-primary"
            >
                Novo Sensor
            </a>

        </div>

        <!-- mensagem de sucesso -->
        <?php if (isset($_GET["success"])): ?>

            <div class="alert alert-success">

                Operação realizada com sucesso.

            </div>

        <?php endif; ?>

        <!-- mensagem de erro -->
        <?php if (isset($_GET["error"])): ?>

            <div class="alert alert-danger">

                Ocorreu um erro na operação.

            </div>

        <?php endif; ?>

        <!-- tabela principal -->
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
                            <th style="min-width:260px;">
                                Simular leitura
                            </th>
                            <th class="text-end">
                                Ações
                            </th>
                        </tr>

                    </thead>

                    <tbody>

                    <!-- verifica se existem sensores -->
                    <?php if (empty($sensors)): ?>

                        <tr>

                            <td colspan="9" class="text-center text-muted">

                                Nenhum sensor registado.

                            </td>

                        </tr>

                    <?php endif; ?>

                    <!-- percorre todos os sensores -->
                    <?php foreach ($sensors as $sensor): ?>

                        <tr>

                            <!-- código do sensor -->
                            <td>

                                <?= htmlspecialchars($sensor["code"]) ?>

                            </td>

                            <!-- nome do sensor -->
                            <td>

                                <?= htmlspecialchars($sensor["name"]) ?>

                            </td>

                            <!-- tipo de sensor -->
                            <td>

                                <?= htmlspecialchars($sensor["type_name"]) ?>

                                <?php if ($sensor["unit"]): ?>

                                    <span class="text-muted">

                                        (<?= htmlspecialchars($sensor["unit"]) ?>)

                                    </span>

                                <?php endif; ?>

                            </td>

                            <!-- recurso associado -->
                            <td>

                                <?= htmlspecialchars($sensor["resource_name"]) ?>

                                <br>

                                <small class="text-muted">

                                    <?= htmlspecialchars($sensor["resource_code"]) ?>

                                </small>

                            </td>

                            <!-- última leitura -->
                            <td>

                                <?php if ($sensor["latest_value"] !== null): ?>

                                    <strong>

                                        <?= htmlspecialchars($sensor["latest_value"]) ?>

                                    </strong>

                                    <?= htmlspecialchars($sensor["unit"] ?? "") ?>

                                    <br>

                                    <?php
                                        // define a cor da badge
                                        $badge = "bg-success";

                                        if ($sensor["latest_status"] === "warning") 
                                        {
                                            $badge = "bg-warning text-dark";
                                        }

                                        if ($sensor["latest_status"] === "critical") 
                                        {
                                            $badge = "bg-danger";
                                        }
                                    ?>

                                    <!-- estado da leitura -->
                                    <span class="badge <?= $badge ?>">

                                        <?= htmlspecialchars($sensor["latest_status"]) ?>

                                    </span>

                                    <br>

                                    <!-- data da leitura -->
                                    <small class="text-muted">

                                        <?= htmlspecialchars($sensor["latest_time"]) ?>

                                    </small>

                                <?php else: ?>

                                    <span class="text-muted">

                                        Sem leituras

                                    </span>

                                <?php endif; ?>

                            </td>

                            <!-- limites do sensor -->
                            <td>

                                Min:
                                <?= htmlspecialchars($sensor["alert_limit_min"] ?? "-") ?>

                                <br>

                                Max:
                                <?= htmlspecialchars($sensor["alert_limit_max"] ?? "-") ?>

                            </td>

                            <!-- estado do sensor -->
                            <td>

                                <?php if ($sensor["status"] === "active"): ?>

                                    <span class="badge bg-success">

                                        Ativo

                                    </span>

                                <?php elseif ($sensor["status"] === "maintenance"): ?>

                                    <span class="badge bg-warning text-dark">

                                        Manutenção

                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">

                                        Inativo

                                    </span>

                                <?php endif; ?>

                            </td>

                            <!-- formulário para simular leitura -->
                            <td>

                                <form 
                                    action="../actions/save_sensor_action.php"
                                    method="POST"
                                    class="d-flex gap-2"
                                >

                                    <!-- modo da ação -->
                                    <input 
                                        type="hidden"
                                        name="mode"
                                        value="add_reading"
                                    >

                                    <!-- ID do sensor -->
                                    <input 
                                        type="hidden"
                                        name="sensor_id"
                                        value="<?= $sensor["sensor_id"] ?>"
                                    >

                                    <!-- valor da leitura -->
                                    <input 
                                        type="number"
                                        step="0.01"
                                        name="value"
                                        class="form-control form-control-sm"
                                        placeholder="Valor"
                                        required
                                    >

                                    <!-- botão guardar -->
                                    <button 
                                        type="submit"
                                        class="btn btn-sm btn-dark"
                                    >
                                        Guardar
                                    </button>

                                </form>

                            </td>

                            <!-- ações -->
                            <td class="text-end">

                                <!-- botão editar -->
                                <a 
                                    href="sensor_form.php?id=<?= $sensor["sensor_id"] ?>"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Editar
                                </a>

                                <!-- botão desativar -->
                                <?php if ($sensor["status"] !== "inactive"): ?>

                                    <form 
                                        action="../actions/save_sensor_action.php"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirmAction('Desativar este sensor?');"
                                    >

                                        <!-- modo da ação -->
                                        <input 
                                            type="hidden"
                                            name="mode"
                                            value="deactivate"
                                        >

                                        <!-- ID do sensor -->
                                        <input 
                                            type="hidden"
                                            name="sensor_id"
                                            value="<?= $sensor["sensor_id"] ?>"
                                        >

                                        <!-- botão desativar -->
                                        <button 
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                        >
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

<!-- inclui o rodapé -->
<?php include __DIR__ . "/../includes/footer.php"; ?>
