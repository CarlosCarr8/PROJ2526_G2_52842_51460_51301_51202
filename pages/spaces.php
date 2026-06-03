<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$pageTitle = "Spaces";

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
            r.status,
            rt.type_name
        FROM resources r
        INNER JOIN resource_types rt 
            ON r.resource_type_id = rt.resource_type_id
        WHERE rt.type_name IN ('room', 'laboratory')
        ORDER BY r.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $spaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error loading spaces: " . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="container mt-4">

    <h2 class="mb-4">Spaces and Laboratories</h2>

    <?php if (count($spaces) > 0): ?>

        <div class="row">
            <?php foreach ($spaces as $space): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">

                            <h5 class="card-title">
                                <?= htmlspecialchars($space['name']) ?>
                            </h5>

                            <p class="text-muted mb-2">
                                <?= htmlspecialchars($space['type_name']) ?>
                            </p>

                            <p>
                                <strong>Código:</strong>
                                <?= htmlspecialchars($space['code']) ?>
                            </p>

                            <p>
                                <strong>Localização:</strong>
                                <?= htmlspecialchars($space['location']) ?>
                            </p>

                            <p>
                                <strong>Andar:</strong>
                                <?= htmlspecialchars($space['floor']) ?>
                            </p>

                            <p>
                                <strong>Capacidade:</strong>
                                <?= htmlspecialchars($space['capacity']) ?>
                            </p>

                            <p>
                                <strong>Status:</strong>
                                <?= htmlspecialchars($space['status']) ?>
                            </p>

                            <p>
                                <?= htmlspecialchars($space['description']) ?>
                            </p>

                        </div>

                        <div class="card-footer bg-white border-0">

                            <a 
                                href="space_details.php?id=<?= $space['resource_id'] ?>" 
                                class="btn btn-primary w-100"
                            >
                                Ver detalhes
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>

    <?php else: ?>
        <div class="alert alert-warning">
            Não foram encontrados espaços.
        </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>

