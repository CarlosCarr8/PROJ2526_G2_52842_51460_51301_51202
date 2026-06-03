<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';
require_once '../includes/auth_check.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid resource ID.");
}

$resourceId = intval($_GET['id']);

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

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(
        ':resource_id',
        $resourceId,
        PDO::PARAM_INT
    );

    $stmt->execute();

    $resource = $stmt->fetch();

    if (!$resource) {
        die("Não foi encontrado nenhum recurso");
    }

} catch (PDOException $e) {

    die("Err: " . $e->getMessage());

}

include '../includes/header.php';
?>

<div class="container mt-4">

    <div class="card shadow-sm">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h2>Reservar recursos</h2>

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
                    <strong>Codigo:</strong>
                    <?= htmlspecialchars($resource['code']) ?>
                </p>

                <p>
                    <strong>Localizaça:</strong>
                    <?= htmlspecialchars($resource['location']) ?>
                </p>

                <p>
                    <strong>Status:</strong>
                    <?= htmlspecialchars($resource['status']) ?>
                </p>

            </div>

            <form action="../actions/reserve_action.php"
                  method="POST">

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
                            Tempo de inicio
                        </label>

                        <input type="time"
                               name="start_time"
                               class="form-control"
                               required>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Tempo final
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
<?php include '../includes/footer.php'; ?>
