<?php

ini_set('display_errors', 1); //ativa os erros
error_reporting(E_ALL);

require_once '../config/db.php'; //conexão à BD
require_once '../includes/auth_check.php'; //verifica autenticação
require_once '../includes/role_check.php'; //verifica permissões

requireRole(['administrator']); //apenas administradores

try {
    $sql = "
        SELECT
            r.resource_id,
            r.code,
            r.name,
            r.location,
            r.capacity,
            r.status,
            rt.type_name
        FROM resources r
        INNER JOIN resource_types rt
            ON r.resource_type_id = rt.resource_type_id
        ORDER BY r.resource_id DESC
    ";

    $stmt = $pdo->prepare($sql); //prepara a consulta
    $stmt->execute(); //executa a consulta
    $resources = $stmt->fetchAll(); //obtém todos os recursos

} 
catch (PDOException $e) //procura erros relacionados com a bd
{
    die("Erro ao carregar os recursos: " . $e->getMessage());
}

include '../includes/header.php'; //inclui o cabeçalho da página
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <span class="fs-3 fw-bold">
                Gerir recursos
            </span>
        </div>

        <a href="resource_form.php"
           class="btn btn-primary">
            Adicionar recurso
        </a>

        <a href="../dashboard.php"
           class="btn btn-secondary me-2">
            Voltar
        </a>

    </div>

    <?php if (count($resources) > 0): //se existirem recursos ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Localização</th>
                        <th>Capacidade</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($resources as $resource): //percorre todos os recursos 
                    ?>
                        <tr>

                            <td>
                                <?= $resource['resource_id'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($resource['code']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($resource['name']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($resource['type_name']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($resource['location']) ?>
                            </td>

                            <td>
                                <?php if ($resource['capacity'] !== null && $resource['capacity'] !== ''): ?>
                                    <?= htmlspecialchars((string) $resource['capacity']) ?>
                                <?php else: ?>
                                    <span class="text-muted">
                                        N/A
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($resource['status']) ?>
                            </td>
                            
                            <td>

                                <a href="resource_form.php?id=<?= $resource['resource_id'] ?>"
                                   class="btn btn-sm btn-warning">
                                    Editar
                                </a>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: //caso não existam recursos ?>

        <div class="alert alert-warning">
            Não foram encontrados recursos.
        </div>

    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; //inclui o rodapé ?>