<?php

ini_set('display_errors', 1); //ativa a exibição de erros
error_reporting(E_ALL); //mostra todos os erros

require_once '../config/db.php'; //conexão à bd
require_once '../includes/auth_check.php'; //verifica se o utilizador está autenticado

if (!isset($_GET['id']) || empty($_GET['id'])) { //verifica se foi recebido um id válido
    die("ID do recurso inválido.");
}

$resourceId = intval($_GET['id']); //converte o id para inteiro

try {

    $sql = "
        SELECT
            r.resource_id,
            r.name,
            r.code,
            r.location,
            r.status,
            rt.type_name
        FROM resources r
        INNER JOIN resource_types rt
            ON r.resource_type_id = rt.resource_type_id
        WHERE r.resource_id = :resource_id
    ";

    $stmt = $pdo->prepare($sql); //prepara a consulta

    $stmt->bindParam( //associa o id do recurso à consulta
        ':resource_id',
        $resourceId,
        PDO::PARAM_INT
    );

    $stmt->execute(); //executa a consulta
    $resource = $stmt->fetch(); //obtém os dados do recurso

    if (!$resource) { //verifica se o recurso existe
        die("Não foi encontrado nenhum recurso.");
    }

} 
catch (PDOException $e) { //procura erros relacionados com a bd
    die("Erro: " . $e->getMessage());
}

include '../includes/header.php'; //inclui o cabeçalho
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">

                <h2>Reservar recurso</h2>

                <a href="javascript:history.back()"
                   class="btn btn-secondary">
                    Voltar
                </a>

            </div>

            <div class="mb-4">

                <h4>
                    <?= htmlspecialchars($resource['name']) ?>
                </h4>

                <p>
                    <strong>Tipo:</strong>
                    <?= htmlspecialchars($resource['type_name']) ?>
                </p>

                <p>
                    <strong>Código:</strong>
                    <?= htmlspecialchars($resource['code']) ?>
                </p>

                <p>
                    <strong>Localização:</strong>
                    <?= htmlspecialchars($resource['location']) ?>
                </p>

                <p>
                    <strong>Estado:</strong>
                    <?= htmlspecialchars($resource['status']) ?>
                </p>

            </div>

            <form action="../actions/reserve_action.php"
                  method="POST">

                <!-- id do recurso a reservar -->
                <input type="hidden"
                       name="resource_id"
                       value="<?= $resource['resource_id'] ?>">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Data da reserva
                        </label>

                        <input type="date"
                               name="reservation_date"
                               class="form-control"
                               required>
                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Hora de início
                        </label>

                        <input type="time"
                               name="start_time"
                               class="form-control"
                               required>
                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Hora de fim
                        </label>

                        <input type="time"
                               name="end_time"
                               class="form-control"
                               required>
                    </div>
                </div>

                <button type="submit"
                        class="btn btn-success">
                    Confirmar reserva
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; //inclui o rodapé ?>
