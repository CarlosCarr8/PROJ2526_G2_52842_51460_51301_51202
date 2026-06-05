<?php

ini_set('display_errors', 1); //ativa a exibição de erros
error_reporting(E_ALL);

require_once '../config/db.php'; //conexão à BD
require_once '../includes/auth_check.php'; //verifica autenticação

$isEdit = false; //define se está em modo de edição
$resource = null; //guarda os dados do recurso (caso edição)

try {

    //carrega os tipos de recurso
    $typesSql = "
        SELECT resource_type_id, type_name
        FROM resource_types
        ORDER BY type_name ASC
    ";

    $typesStmt = $pdo->prepare($typesSql);
    $typesStmt->execute();
    $resourceTypes = $typesStmt->fetchAll();

    //modo edição
    if (isset($_GET['id']) && !empty($_GET['id'])) {

        $isEdit = true;

        $resourceId = intval($_GET['id']); //ID do recurso

        //vai buscar o recurso selecionado
        $resourceSql = "
            SELECT *
            FROM resources
            WHERE resource_id = :resource_id
        ";

        $resourceStmt = $pdo->prepare($resourceSql);
        $resourceStmt->bindParam(
            ':resource_id',
            $resourceId,
            PDO::PARAM_INT
        );

        $resourceStmt->execute();

        $resource = $resourceStmt->fetch();

        //verifica se o recurso existe
        if (!$resource) {
            die("Recurso não encontrado.");
        }
    }

} catch (PDOException $e) {
    die("Erro na base de dados: " . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            <?= $isEdit ? 'Editar Recurso' : 'Adicionar Recurso' ?>
        </h2>

        <a href="resources.php"
           class="btn btn-secondary">
            Voltar
        </a>

    </div>

    <form action="../actions/save_resource_action.php"
          method="POST">

        <?php if ($isEdit): ?>

            <input type="hidden"
                   name="resource_id"
                   value="<?= $resource['resource_id'] ?>">

        <?php endif; ?>
        <div class="row">

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Tipo de Recurso
                </label>

                <select name="resource_type_id"
                        class="form-select"
                        required>

                    <option value="">
                        Selecionar tipo
                    </option>

                    <?php foreach ($resourceTypes as $type): ?>

                        <option value="<?= $type['resource_type_id'] ?>"

                            <?php
                            if ($isEdit && $resource['resource_type_id'] == $type['resource_type_id']) {
                                echo 'selected';
                            }
                            ?>
                        >
                            <?= htmlspecialchars($type['type_name']) ?>
                        </option>

                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Código
                </label>

                <input type="text"
                       name="code"
                       class="form-control"
                       required
                       value="<?= $isEdit ? htmlspecialchars($resource['code']) : '' ?>">
            </div>
        </div>

        <div class="mb-3">

            <label class="form-label">
                Nome
            </label>

            <input type="text"
                   name="name"
                   class="form-control"
                   required
                   value="<?= $isEdit ? htmlspecialchars($resource['name']) : '' ?>">
        </div>

        <div class="mb-3">

            <label class="form-label">
                Descrição
            </label>

            <textarea name="description"
                      class="form-control"
                      rows="3"><?= $isEdit ? htmlspecialchars($resource['description']) : '' ?></textarea>

        </div>
        <div class="row">

            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Localização
                </label>

                <input type="text"
                       name="location"
                       class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($resource['location']) : '' ?>">

            </div>

            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Piso
                </label>

                <input type="number"
                       name="floor"
                       class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($resource['floor']) : '' ?>">

            </div>

            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Capacidade
                </label>

                <input type="number"
                       name="capacity"
                       class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($resource['capacity']) : '' ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Quantidade Total
                </label>

                <input type="number"
                       name="quantity_total"
                       class="form-control"
                       required
                       value="<?= $isEdit ? htmlspecialchars($resource['quantity_total']) : 1 ?>">

            </div>
            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Quantidade Disponível
                </label>

                <input type="number"
                       name="quantity_available"
                       class="form-control"
                       required
                       value="<?= $isEdit ? htmlspecialchars($resource['quantity_available']) : 1 ?>">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">
                    Estado
                </label>

                <select name="status"
                        class="form-select"
                        required>

                    <option value="active"
                        <?= ($isEdit && $resource['status'] == 'active') ? 'selected' : '' ?>>
                        Ativo
                    </option>
                    <option value="inactive"
                        <?= ($isEdit && $resource['status'] == 'inactive') ? 'selected' : '' ?>>
                        Inativo
                    </option>
                </select>
            </div>
        </div>

        <button type="submit"
                class="btn btn-success">
            Guardar Recurso
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
