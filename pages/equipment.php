<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';
require_once '../includes/auth_check.php';

$pageTitle = "Equipment";

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
        WHERE rt.type_name = 'equipment'
        ORDER BY r.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $equipment = $stmt->fetchAll();

} catch (PDOException $e) {

    die("Error loading equipment: " . $e->getMessage());

}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Equipamento</h2>

        <a href="../dashboard.php"
           class="btn btn-secondary">
            Voltar
        </a>
    </div>

    <?php if (count($equipment) > 0): ?>

        <div class="row">
            <?php foreach ($equipment as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">

                            <h5 class="card-title">
                                <?= htmlspecialchars($item['name']) ?>
                            </h5>

                            <span class="badge bg-primary mb-3">
                                <?= htmlspecialchars($item['type_name']) ?>
                            </span>

                            <p>
                                <strong>Codigo:</strong>
                                <?= htmlspecialchars($item['code']) ?>
                            </p>

                            <p>
                                <strong>Localização:</strong>
                                <?= htmlspecialchars($item['location']) ?>
                            </p>

                            <p>
                                <strong>Disponibilidade:</strong>
                                <?= htmlspecialchars($item['quantity_available']) ?>
                            </p>

                            <p>
                                <strong>Status:</strong>
                                <?= htmlspecialchars($item['status']) ?>
                            </p>

                            <p>
                                <?= htmlspecialchars($item['description']) ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="equipment_details.php?id=<?= $item['resource_id'] ?>"
                               class="btn btn-primary w-100">
                                Ver detalhes
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            Equipamento não encontrado
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
