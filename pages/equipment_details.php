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

    $stmt = $pdo->prepare($sql); //prepara a consulta
    $stmt->bindParam( //associa o id do recurso à consulta
        ':resource_id',
        $resourceId,
        PDO::PARAM_INT
    );

    $stmt->execute(); //executa a consulta

    $resource = $stmt->fetch(); //obtém os dados do recurso

    if (!$resource) { //verifica se o recurso existe
        die("Este recurso não foi encontrado.");
    }

} catch (PDOException $e) { //procura erros relacionados com a bd

    die("Erro: " . $e->getMessage());

}

include '../includes/header.php'; //inclui o cabeçalho
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">

                <h2>Detalhes do Recurso</h2>

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

                <p>
                    <strong>Andar:</strong>

                    <?php if ($resource['floor'] !== null && $resource['floor'] !== ''): ?>

                        <?= htmlspecialchars((string) $resource['floor']) ?>

                    <?php else: ?>

                        <span class="text-muted">N/A</span>

                    <?php endif; ?>
                </p>

                </div>

                <div class="col-md-6">

                    <p>
                        <strong>Capacidade:</strong>

                        <?php if ($resource['capacity'] !== null && $resource['capacity'] !== ''): ?>

                            <?= htmlspecialchars((string) $resource['capacity']) ?>

                        <?php else: ?>

                            <span class="text-muted">N/A</span>

                        <?php endif; ?>
                    </p>

                    <p>
                        <strong>Quantidade total:</strong>
                        <?= htmlspecialchars($resource['quantity_total']) ?>
                    </p>

                    <p>
                        <strong>Quantidade disponível:</strong>
                        <?= htmlspecialchars($resource['quantity_available']) ?>
                    </p>

                    <p>
                        <strong>Estado:</strong>
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

<?php include '../includes/footer.php'; //inclui o rodapé ?>
