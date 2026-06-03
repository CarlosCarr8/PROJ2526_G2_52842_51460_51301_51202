<?php

session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) 
{
    die("User not authenticated.");
}

if (!isset($_GET['id']) || empty($_GET['id'])) 
{
    die("Invalid reservation ID.");
}

$userId = $_SESSION['user_id'];
$reservationId = intval($_GET['id']);

try {

    $checkSql = "
        SELECT
            reservation_id,
            status_id
        FROM reservations
        WHERE reservation_id = :reservation_id
        AND user_id = :user_id
    ";

    $checkStmt = $pdo->prepare($checkSql);

    $checkStmt->bindParam
    (
        ':reservation_id',
        $reservationId,
        PDO::PARAM_INT
    );

    $checkStmt->bindParam
    (
        ':user_id',
        $userId,
        PDO::PARAM_INT
    );

    $checkStmt->execute();

    $reservation = $checkStmt->fetch();

    if (!$reservation) 
    {
        die("Reservation not found.");
    }

    if ($reservation['status_id'] == 2) 
    {
        die("Reservation already cancelled.");
    }

    $updateSql = "
        UPDATE reservations
        SET
            status_id = 2,
            cancelled_at = NOW(),
            cancelled_by = :cancelled_by
        WHERE reservation_id = :reservation_id
    ";

    $updateStmt = $pdo->prepare($updateSql);

    $updateStmt->bindParam
    (
        ':cancelled_by',
        $userId,
        PDO::PARAM_INT
    );

    $updateStmt->bindParam
    (
        ':reservation_id',
        $reservationId,
        PDO::PARAM_INT
    );

    $updateStmt->execute();

    header("Location: ../pages/my_reservations.php");
    exit;
} 

catch (PDOException $e) 
{
    die("Erro: " . $e->getMessage());
}
?>
