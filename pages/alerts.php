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
<?php 

// verifica se o utilizador está autenticado
include __DIR__ . "/../includes/auth_check.php";

// verifica se o utilizador tem permissões válidas
include __DIR__ . "/../includes/role_check.php";

// permite acesso a administradores e funcionários
requireRole(["administrator", "funcionario"]);

// ligação à base de dados
require_once __DIR__ . "/../config/db.php";

// define o título da página
$pageTitle = "Alertas - PCU";

// define o caminho base do projeto
$basePath = "../";

// obtém o filtro de estado enviado pela URL
$statusFilter = $_GET["status"] ?? "";

// array para guardar parâmetros da consulta
$params = [];

// variável para guardar condições SQL
$where = "";

// verifica se foi selecionado um estado
if ($statusFilter !== "") 
{
    // adiciona condição à consulta
    $where = "WHERE a.status = ?";

    // adiciona o valor do filtro
    $params[] = $statusFilter;
}

// consulta todos os alertas
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

    INNER JOIN sensors s 
        ON a.sensor_id = s.sensor_id

    INNER JOIN sensor_types st 
        ON s.sensor_type_id = st.sensor_type_id

    INNER JOIN resources r 
        ON a.resource_id = r.resource_id

    LEFT JOIN users u 
        ON a.resolved_by = u.user_id

    $where

    ORDER BY a.created_at DESC
";

// prepara a consulta
$stmt = $pdo->prepare($sql);

// executa a consulta
$stmt->execute($params);

// obtém todos os alertas encontrados
$alerts = $stmt->fetchAll();

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

                <h2>Alertas</h2>

                <p class="text-muted">
                    Alertas gerados por leituras críticas de sensores.
                </p>

            </div>

            <!-- botão para abrir monitorização IoT -->
            <a 
                href="iot_dashboard.php"
                class="btn btn-outline-secondary"
            >
                Monitorização IoT
            </a>

        </div>

        <!-- mensagem de sucesso -->
        <?php if (isset($_GET["success"])): ?>

            <div class="alert alert-success">

                Alerta resolvido com sucesso.

            </div>

        <?php endif; ?>

        <!-- cartão de filtros -->
        <div class="card shadow-sm mb-4">

            <div class="card-body">

                <form method="GET" class="row g-3">

                    <!-- filtro de estado -->
                    <div class="col-md-4">

                        <label class="form-label">
                            Estado
                        </label>

                        <select 
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                Todos
                            </option>

                            <option 
                                value="open"
                                <?= $statusFilter === "open" ? "selected" : "" ?>
                            >
                                Abertos
                            </option>

                            <option 
                                value="resolved"
                                <?= $statusFilter === "resolved" ? "selected" : "" ?>
                            >
                                Resolvidos
                            </option>

                        </select>

                    </div>

                    <!-- botão filtrar -->
                    <div class="col-md-2 d-flex align-items-end">

                        <button class="btn btn-dark w-100">

                            Filtrar

                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- tabela principal -->
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

                    <!-- verifica se existem alertas -->
                    <?php if (empty($alerts)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                Nenhum alerta encontrado.
                            </td>
                        </tr>

                    <?php endif; ?>

                    <!-- percorre todos os alertas -->
                    <?php foreach ($alerts as $alert): ?>

                        <?php
                            // define a cor da severidade
                            $severityClass = "bg-secondary";

                            if ($alert["severity"] === "low") 
                            {
                                $severityClass = "bg-info text-dark";
                            }

                            if ($alert["severity"] === "medium") 
                            {
                                $severityClass = "bg-warning text-dark";
                            }

                            if ($alert["severity"] === "high") 
                            {
                                $severityClass = "bg-danger";
                            }

                            if ($alert["severity"] === "critical") 
                            {
                                $severityClass = "bg-danger";
                            }
                        ?>

                        <tr>

                            <!-- data do alerta -->
                            <td>
                                <?= htmlspecialchars($alert["created_at"]) ?>
                            </td>

                            <!-- recurso associado -->
                            <td>
                                <?= htmlspecialchars($alert["resource_name"]) ?>
                                <br>
                                <small class="text-muted">
                                    <?= htmlspecialchars($alert["resource_code"]) ?>
                                </small>
                            </td>

                            <!-- sensor associado -->
                            <td>
                                <?= htmlspecialchars($alert["sensor_type"]) ?>
                                <br>
                                <small class="text-muted">
                                    <?= htmlspecialchars($alert["sensor_code"]) ?>
                                </small>
                            </td>

                            <!-- mensagem do alerta -->
                            <td>
                                <?= htmlspecialchars($alert["message"]) ?>
                            </td>

                            <!-- severidade -->
                            <td>
                                <span class="badge <?= $severityClass ?>">
                                    <?= htmlspecialchars($alert["severity"]) ?>
                                </span>
                            </td>

                            <!-- estado do alerta -->
                            <td>

                                <?php if ($alert["status"] === "open"): ?>

                                    <span class="badge bg-danger">
                                        Aberto
                                    </span>

                                <?php else: ?>
                                    <span class="badge bg-success">
                                        Resolvido
                                    </span>

                                    <br>
                                    <!-- informação da resolução -->
                                    <small class="text-muted">

                                        <?= htmlspecialchars($alert["resolved_at"] ?? "") ?>

                                        <?= $alert["resolved_by_name"] 
                                            ? " por " . htmlspecialchars($alert["resolved_by_name"]) 
                                            : "" ?>

                                    </small>

                                <?php endif; ?>

                            </td>

                            <!-- ações -->
                            <td class="text-end">

                                <?php if ($alert["status"] === "open"): ?>

                                    <!-- formulário para resolver alerta -->
                                    <form 
                                        action="../actions/resolve_alert_action.php"
                                        method="POST"
                                        onsubmit="return confirmAction('Marcar este alerta como resolvido?');"
                                    >

                                        <!-- ID do alerta -->
                                        <input 
                                            type="hidden"
                                            name="alert_id"
                                            value="<?= $alert["alert_id"] ?>"
                                        >

                                        <!-- botão resolver -->
                                        <button class="btn btn-sm btn-outline-success">

                                            Resolver

                                        </button>

                                    </form>

                                <?php else: ?>

                                    <span class="text-muted">

                                        -

                                    </span>

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