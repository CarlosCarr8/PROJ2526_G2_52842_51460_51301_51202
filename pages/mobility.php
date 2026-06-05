<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';
require_once '../includes/auth_check.php';

$pageTitle = "Mobilidade";

try {

    $sql = "
        SELECT
            r.resource_id,
            r.code,
            r.name,
            r.description,
            r.location,
            r.quantity_available,
            r.status,
            rt.type_name
        FROM resources r
        INNER JOIN resource_types rt
            ON r.resource_type_id = rt.resource_type_id
        WHERE rt.type_name IN ('bicycle', 'scooter')
        ORDER BY r.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $mobilityResources = $stmt->fetchAll();

} catch (PDOException $e) {

    die("Erro: " . $e->getMessage());

}

include '../includes/header.php';
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Recurso de mobilidade</h2>

        <a href="../dashboard.php"
           class="btn btn-secondary">
            Voltar
        </a>

    </div>

    <?php if (count($mobilityResources) > 0): ?>

        <div class="row">
            <?php foreach ($mobilityResources as $resource): ?>

                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= htmlspecialchars($resource['name']) ?>
                            </h5>

                            <span class="badge bg-success mb-3">
                                <?= htmlspecialchars($resource['type_name']) ?>
                            </span>

                            <p>
                                <strong>Codigo:</strong>
                                <?= htmlspecialchars($resource['code']) ?>
                            </p>

                            <p>
                                <strong>Localização:</strong>
                                <?= htmlspecialchars($resource['location']) ?>
                            </p>

                            <p>
                                <strong>Disponibilidade:</strong>
                                <?= htmlspecialchars($resource['quantity_available']) ?>
                            </p>

                            <p>
                                <strong>Status:</strong>
                                <?= htmlspecialchars($resource['status']) ?>
                            </p>

                            <p>
                                <?= htmlspecialchars($resource['description']) ?>
                            </p>

                        </div>

                        <div class="card-footer bg-white border-0">

                            <a href="equipment_details.php?id=<?= $resource['resource_id'] ?>"
                               class="btn btn-success w-100">
                                Ver detalhes
                            </a>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            Não foram encontrados nenhuns meios de mobilidade.
        </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
