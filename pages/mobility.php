<?php

ini_set('display_errors', 1); //ativa a exibição de erros
error_reporting(E_ALL); //mostra todos os erros

require_once '../config/db.php'; //conexão à bd
require_once '../includes/auth_check.php'; //verifica se o utilizador está autenticado
require_once '../includes/role_check.php'; //verifica se o utilizador tem permissão para aceder a esta página
requireRole(['student']); //apenas estudantes podem aceder a esta página

$pageTitle = "Mobilidade"; //título da página

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

    $stmt = $pdo->prepare($sql); //prepara a consulta
    $stmt->execute(); //executa a consulta
    $mobilityResources = $stmt->fetchAll(); //obtém os recursos de mobilidade

} catch (PDOException $e) { //procura erros relacionados com a bd

    die("Erro: " . $e->getMessage());

}

include '../includes/header.php'; //inclui o cabeçalho
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Recursos de Mobilidade</h2>

        <a href="../dashboard.php"
           class="btn btn-secondary">
            Voltar
        </a>
    </div>

    <?php if (count($mobilityResources) > 0): //verifica se existem recursos ?>
        <div class="row">
            <?php foreach ($mobilityResources as $resource): //percorre todos os recursos ?>
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
                                <strong>Código:</strong>
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
                                <strong>Estado:</strong>
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

    <?php else: //caso não existam recursos ?>
        <div class="alert alert-warning">
            Não foram encontrados meios de mobilidade.
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; //inclui o rodapé ?>
