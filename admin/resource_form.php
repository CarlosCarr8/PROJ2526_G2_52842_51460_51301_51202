<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/role_check.php';

requireRole(['administrator']);

$isEdit = false;
$resource = null;

try {

    // LOAD RESOURCE TYPES

    $typesSql = "
        SELECT resource_type_id, type_name
        FROM resource_types
        ORDER BY type_name ASC
    ";

    $typesStmt = $pdo->prepare($typesSql);
    $typesStmt->execute();

    $resourceTypes = $typesStmt->fetchAll();

    // EDIT MODE

    if (isset($_GET['id']) && !empty($_GET['id'])) {

        $isEdit = true;

        $resourceId = intval($_GET['id']);

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

        if (!$resource) {
            die("Resource not found.");
        }
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            <?= $isEdit ? 'Edit Resource' : 'Add Resource' ?>
        </h2>

        <a href="resources.php"
           class="btn btn-secondary">
            Back
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
                    Resource Type
                </label>

                <select name="resource_type_id"
                        class="form-select"
                        required>

                    <option value="">
                        Select type
                    </option>

                    <?php foreach ($resourceTypes as $type): ?>

                        <option value="<?= $type['resource_type_id'] ?>"

                            <?php
                            if (
                                $isEdit &&
                                $resource['resource_type_id']
                                == $type['resource_type_id']
                            ) {
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
                    Code
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
                Name
            </label>

            <input type="text"
                   name="name"
                   class="form-control"
                   required
                   value="<?= $isEdit ? htmlspecialchars($resource['name']) : '' ?>">

        </div>

        <div class="mb-3">

            <label class="form-label">
                Description
            </label>

            <textarea name="description"
                      class="form-control"
                      rows="3"><?= $isEdit ? htmlspecialchars($resource['description']) : '' ?></textarea>

        </div>

        <div class="row">

            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Location
                </label>

                <input type="text"
                       name="location"
                       class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($resource['location']) : '' ?>">

            </div>

            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Floor
                </label>

                <input type="number"
                       name="floor"
                       class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($resource['floor']) : '' ?>">

            </div>

            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Capacity
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
                    Total Quantity
                </label>

                <input type="number"
                       name="quantity_total"
                       class="form-control"
                       required
                       value="<?= $isEdit ? htmlspecialchars($resource['quantity_total']) : 1 ?>">

            </div>

            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Available Quantity
                </label>

                <input type="number"
                       name="quantity_available"
                       class="form-control"
                       required
                       value="<?= $isEdit ? htmlspecialchars($resource['quantity_available']) : 1 ?>">

            </div>

            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Status
                </label>

                <select name="status"
                        class="form-select"
                        required>

                    <option value="available"
                        <?= ($isEdit && $resource['status'] == 'available') ? 'selected' : '' ?>>
                        Available
                    </option>

                    <option value="unavailable"
                        <?= ($isEdit && $resource['status'] == 'unavailable') ? 'selected' : '' ?>>
                        Unavailable
                    </option>

                    <option value="maintenance"
                        <?= ($isEdit && $resource['status'] == 'maintenance') ? 'selected' : '' ?>>
                        Maintenance

                    <option value="inactive"
                        <?= ($isEdit && $resource['status'] == 'inactive') ? 'selected' : '' ?>>
                        Inactive
                    </option>

                </select>

            </div>

        </div>

        <button type="submit"
                class="btn btn-success">
            Save Resource
        </button>

    </form>

</div>

<?php include '../includes/footer.php'; ?>

