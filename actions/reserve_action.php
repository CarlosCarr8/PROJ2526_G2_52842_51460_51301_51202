<?php

session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') 
{
    die("Pedido invalido");
}

if (!isset($_SESSION['user_id'])) 
{
    die("Utilizador nao está autenticado");
}

$userId = $_SESSION['user_id'];
$resourceId = $_POST['resource_id'] ?? null;
$reservationDate = $_POST['reservation_date'] ?? null;
$startTime = $_POST['start_time'] ?? null;
$endTime = $_POST['end_time'] ?? null;

if (empty($resourceId) || empty($reservationDate) || empty($startTime) || empty($endTime)) 
{
    die("Por favor preencha todos os campos");
}

$startDatetime = $reservationDate . ' ' . $startTime . ':00';
$endDatetime = $reservationDate . ' ' . $endTime . ':00';

if ($startDatetime >= $endDatetime) 
{
    die("O tempo final deve ser depois do inicial");
}

try {
    $statusId = 1;

    $typeSql = "
        SELECT rt.type_name
        FROM resources r
        INNER JOIN resource_types rt
            ON r.resource_type_id = rt.resource_type_id
        WHERE r.resource_id = :resource_id
    ";

    $typeStmt = $pdo->prepare($typeSql);

    $typeStmt->bindParam(
        ':resource_id',
        $resourceId,
        PDO::PARAM_INT
    );

    $typeStmt->execute();
    $resourceData = $typeStmt->fetch();

    if (!$resourceData) {
        die("Recurso inválido");
    }

    $resourceType = strtolower($resourceData['type_name']);

    $countSql = "
        SELECT COUNT(*) AS total
        FROM reservations r
        INNER JOIN resources re
            ON r.resource_id = re.resource_id
        INNER JOIN resource_types rt
            ON re.resource_type_id = rt.resource_type_id
        WHERE r.user_id = :user_id
        AND r.status_id = 1
    ";

    if ($resourceType === 'room' || $resourceType === 'laboratory') {

        $countSql .= "
            AND rt.type_name IN ('Room', 'Laboratory')";

        $limit = 2;

    }

    elseif ($resourceType === 'bicycle' || $resourceType === 'scooter') {
        $countSql .= "
            AND rt.type_name IN ('Bicycle', 'Scooter')
        ";
        $limit = 1;
    }

    else {
        $limit = null;
    }

    if ($limit !== null) {
        $countStmt = $pdo->prepare($countSql);
        $countStmt->bindParam(
            ':user_id',
            $userId,
            PDO::PARAM_INT
        );

        $countStmt->execute();

        $activeReservations = $countStmt->fetch();

        if ($activeReservations['total'] >= $limit) {
            die("Atingiu o número de reservas máxima");
        }

    }

    $overlapSql = "
        SELECT reservation_id
        FROM reservations
        WHERE resource_id = :resource_id
        AND status_id = 1

        AND 
        (
            start_datetime < :new_end
            AND end_datetime > :new_start
        )
    ";

    $overlapStmt = $pdo->prepare($overlapSql);

    $overlapStmt->bindParam(
        ':resource_id',
        $resourceId,
        PDO::PARAM_INT
    );

    $overlapStmt->bindParam(
        ':new_start',
        $startDatetime
    );

    $overlapStmt->bindParam(
        ':new_end',
        $endDatetime
    );

    $overlapStmt->execute();

    $overlapReservation = $overlapStmt->fetch();

    if ($overlapReservation) 
    {
        die("Este recurso já está reservado para esta hora. Por favor tente outra.");
    }

    $sql = 
    "
     INSERT INTO reservations (
            user_id,
            resource_id,
            status_id,
            start_datetime,
            end_datetime,
            quantity,
            purpose,
            created_at
        )
        VALUES (
            :user_id,
            :resource_id,
            :status_id,
            :start_datetime,
            :end_datetime,
            :quantity,
            :purpose,
            NOW()
        )
    ";

    $stmt = $pdo->prepare($sql);
    $quantity = 1;
    $purpose = 'Reservation made via platform';
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':resource_id', $resourceId, PDO::PARAM_INT);
    $stmt->bindParam(':status_id', $statusId, PDO::PARAM_INT);
    $stmt->bindParam(':start_datetime', $startDatetime);
    $stmt->bindParam(':end_datetime', $endDatetime);
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':purpose', $purpose);
    $stmt->execute();
    header("Location: ../pages/my_reservations.php");
    exit;

} 
catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>