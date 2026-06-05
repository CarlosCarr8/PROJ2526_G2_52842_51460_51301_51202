<?php

ini_set('display_errors', 1); // ativa a exibição de erros
error_reporting(E_ALL);

require_once '../config/db.php'; // ligação à BD
require_once '../includes/auth_check.php'; // verifica autenticação
require_once '../includes/role_check.php'; // verifica permissões

requireRole(['administrator']); // apenas administradores

// verifica se o pedido foi feito por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Pedido inválido.");
}

try {

    $resourceId = $_POST['resource_id'] ?? null;

    // dados do formulário
    $resourceTypeId = $_POST['resource_type_id'] ?? null;
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');

    $floor = ($_POST['floor'] !== '') ? intval($_POST['floor']) : null;
    $capacity = ($_POST['capacity'] !== '') ? intval($_POST['capacity']) : null;

    $quantityTotal = ($_POST['quantity_total'] !== '')
        ? intval($_POST['quantity_total'])
        : 1;

    $quantityAvailable = ($_POST['quantity_available'] !== '')
        ? intval($_POST['quantity_available'])
        : 1;

    $status = $_POST['status'] ?? 'available';

    // estados permitidos
    $validStatuses = [
        'available',
        'unavailable',
        'maintenance',
        'inactive'
    ];

    if (!in_array($status, $validStatuses)) {
        die("Estado do recurso inválido.");
    }

    if ($quantityAvailable > $quantityTotal) {
        die("A quantidade disponível não pode ser superior à quantidade total.");
    }

    if ($quantityTotal < 0 || $quantityAvailable < 0) {
        die("As quantidades não podem ser negativas.");
    }

    // valida campos obrigatórios
    if (
        empty($resourceTypeId) ||
        empty($code) ||
        empty($name) ||
        empty($status)
    ) {
        die("Por favor, preencha todos os campos obrigatórios.");
    }

    //se existir ID → atualiza
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

    //se não existir ID → cria novo registo
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

    //associa os parâmetros à query
    $stmt->bindParam(':resource_type_id', $resourceTypeId, PDO::PARAM_INT);
    $stmt->bindParam(':code', $code);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':floor', $floor);
    $stmt->bindParam(':capacity', $capacity);
    $stmt->bindParam(':quantity_total', $quantityTotal);
    $stmt->bindParam(':quantity_available', $quantityAvailable);
    $stmt->bindParam(':status', $status);

    $stmt->execute(); //executa a query
    header("Location: ../admin/resources.php"); //volta para a página de recursos
    exit;
} 
catch (PDOException $e) {
    die("Erro na base de dados: " . $e->getMessage());
}
?>