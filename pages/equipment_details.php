<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';
require_once '../includes/auth_check.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do recurso inválido.");
}

$resourceId = intval($_GET['id']);

try {

    $sql = "
        SELECT
            r.resource_id,
            r.code,
            r.name,
            r.description,
            r.location,
            r.floor,
            r.capacity,
            r.quantity_total,
            r.quantity_available,
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
        die("Este recurso não foi encontrado.");
    }

} catch (PDOException $e) {

    die("Erro: " . $e->getMessage());

}

include '../includes/header.php';
?>

<div class="container mt-4">

    <div class="card shadow-sm">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h2>Detalhes dos Recurso</h2>

                <a href="javascript:history.back()"
                   class="btn btn-secondary">
                    Voltar
                </a>

            </div>

            <div class="row">

                <div class="col-md-6">

                    <p>
                        <strong>Nome:</strong>
                        <?= htmlspecialchars($resource['name']) ?>
                    </p>

                    <p>
                        <strong>Código:</strong>
                        <?= htmlspecialchars($resource['code']) ?>
                    </p>

                    <p>
                        <strong>Tipo:</strong>
                        <?= htmlspecialchars($resource['type_name']) ?>
                    </p>

                    <p>
                        <strong>Localização:</strong>
                        <?= htmlspecialchars($resource['location']) ?>
                    </p>

                    <p>
                        <strong>Andar:</strong>
                        <?= htmlspecialchars($resource['floor']) ?>
                    </p>

                </div>

                <div class="col-md-6">

                    <p>
                        <strong>Capacidade:</strong>
                        <?= htmlspecialchars($resource['capacity']) ?>
                    </p>

                    <p>
                        <strong>Quantidade total:</strong>
                        <?= htmlspecialchars($resource['quantity_total']) ?>
                    </p>

                    <p>
                        <strong>Quantidade disponivel:</strong>
                        <?= htmlspecialchars($resource['quantity_available']) ?>
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <?= htmlspecialchars($resource['status']) ?>
                    </p>
                </div>
            </div>

            <hr>
            <p>
                <strong>Descrição:</strong>
            </p>

            <p>
                <?= nl2br(htmlspecialchars($resource['description'])) ?>
            </p>

            <a href="reserve.php?id=<?= $resource['resource_id'] ?>"
               class="btn btn-success">
                Reservar
            </a>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
