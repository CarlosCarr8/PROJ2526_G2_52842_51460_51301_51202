<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

try {
    $resourceId = $_POST['resource_id'] ?? null;

    $resourceTypeId = $_POST['resource_type_id'] ?? null;
    $code = trim($_POST['code']) ?? null;
    $name = trim($_POST['name']) ?? null;
    $description = trim($_POST['description']) ?? null;
    $location = trim($_POST['location']) ?? null;

    $floor = ($_POST['floor'] == '' ) ? intval($_POST['floor']) : null;
    $capacity = ($_POST['capacity'] !== '') ? intval($_POST['capacity']) : null;

    $quantityTotal = ($_POST['quantity_total'] !== '') ? intval($_POST['quantity_total']) : 1;
    $quantityAvailable = ($_POST['quantity_available'] !== '') ? intval($_POST['quantity_available']) : 1;
    $status = $_POST['status'] ?? 'available';

    $validStatuses = ['available', 'unavailable', 'maintenance', 'inactive'];

    if (!in_array($status, $validStatuses)) {
        die("Estado do recurso inválido.");
    }
    
    if ($quantityAvailable > $quantityTotal) {
        die("A quantidade disponível não pode ser maior que a quantidade total.");
    }

    if ($quantityTotal < 0 || $quantityAvailable < 0) {
        die("As quantidades não podem ser negativas.");
    }

    if (
        empty($resourceTypeId) ||
        empty($code) ||
        empty($name) ||
        empty($status)
    ) {
        die("Please fill all required fields.");
    }

    if (!empty($resourceId)) {

        $sql = "
            UPDATE resources
            SET
                resource_type_id = :resource_type_id,
                code = :code,
                name = :name,
                description = :description,
                location = :location,
                floor = :floor,
                capacity = :capacity,
                quantity_total = :quantity_total,
                quantity_available = :quantity_available,
                status = :status,
                updated_at = NOW()
            WHERE resource_id = :resource_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':resource_id', $resourceId, PDO::PARAM_INT);

    }

    else {

        $sql = "
            INSERT INTO resources (
                resource_type_id,
                code,
                name,
                description,
                location,
                floor,
                capacity,
                quantity_total,
                quantity_available,
                status,
                created_at
            )
            VALUES (
                :resource_type_id,
                :code,
                :name,
                :description,
                :location,
                :floor,
                :capacity,
                :quantity_total,
                :quantity_available,
                :status,
                NOW()
            )
        ";

        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindParam(
        ':resource_type_id',
        $resourceTypeId,
        PDO::PARAM_INT
    );

    $stmt->bindParam(':code', $code);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':floor', $floor);
    $stmt->bindParam(':capacity', $capacity);
    $stmt->bindParam(':quantity_total', $quantityTotal);
    $stmt->bindParam(':quantity_available', $quantityAvailable);
    $stmt->bindParam(':status', $status);

    $stmt->execute();

    header("Location: ../admin/resources.php");
    exit;

} catch (PDOException $e) {

    die("Database error: " . $e->getMessage());

}