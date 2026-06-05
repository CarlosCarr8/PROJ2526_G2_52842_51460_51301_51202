<?php

ini_set('display_errors', 1); //ativa a exibição de erros
error_reporting(E_ALL); //mostra todos os erros

require_once '../config/db.php'; //conexão à bd
require_once '../includes/auth_check.php'; //verifica se o utilizador está autenticado

$pageTitle = "Equipamentos"; //título da página

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
        WHERE rt.type_name = 'Equipment'
        ORDER BY r.name ASC
    ";

    $stmt = $pdo->prepare($sql); //prepara a consulta
    $stmt->execute(); //executa a consulta

    $equipment = $stmt->fetchAll(); //obtém todos os equipamentos

} catch (PDOException $e) { //procura erros relacionados com a bd

    die("Erro ao carregar equipamentos: " . $e->getMessage());

}

include '../includes/header.php'; //inclui o cabeçalho
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Equipamentos</h2>

        <a href="../dashboard.php"
           class="btn btn-secondary">
            Voltar
        </a>

    </div>

    <?php if (count($equipment) > 0): //verifica se existem equipamentos ?>

        <div class="row">
            <?php foreach ($equipment as $item): //percorre todos os equipamentos ?>
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
                                <strong>Código:</strong>
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
                                <strong>Estado:</strong>
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

    <?php else: //caso não existam equipamentos ?>

        <div class="alert alert-warning">
            Equipamentos não encontrados.
        </div>

    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; //inclui o rodapé ?>
