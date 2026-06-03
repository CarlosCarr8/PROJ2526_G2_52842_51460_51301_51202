<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';
require_once '../includes/auth_check.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do espaço inválido");
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

    $stmt->bindParam(':resource_id', $resourceId, PDO::PARAM_INT);

    $stmt->execute();

    $space = $stmt->fetch();

    if (!$space) {
        die("Não foi encontrado nenhum espaço.");
    }

} catch (PDOException $e) {
    die("Erro da base de dados " . $e->getMessage());
}

include '../includes/header.php';
?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="mb-3">
                <?= htmlspecialchars($space['name']) ?>
            </h2>

            <span class="badge bg-primary mb-3">
                <?= htmlspecialchars($space['type_name']) ?>
            </span>

            <p>
                <strong>Código:</strong>
                <?= htmlspecialchars($space['code']) ?>
            </p>

            <p>
                <strong>Descrição:</strong>
                <?= htmlspecialchars($space['description']) ?>
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
                <strong>Quantidade total:</strong>
                <?= htmlspecialchars($space['quantity_total']) ?>
            </p>

            <p>
                <strong>Quantidade disponivel:</strong>
                <?= htmlspecialchars($space['quantity_available']) ?>
            </p>

            <p>
                <strong>Status:</strong>
                <?= htmlspecialchars($space['status']) ?>
            </p>

            <div class="mt-4">

                <a href="reserve.php?id=<?= $space['resource_id'] ?>"
                   class="btn btn-success">
                    Reservar
                </a>

                <a href="spaces.php"
                   class="btn btn-secondary">
                    Voltar
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
