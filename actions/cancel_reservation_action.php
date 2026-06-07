<?php

session_start();    

ini_set('display_errors', 1); 
error_reporting(E_ALL);

require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Utilizador não autenticado");
}

if (!isset($_POST['reservation_id']) || empty($_POST['reservation_id'])) {
    die("ID de reserva inválido");
}

$userId = $_SESSION['user_id'];
$reservationId = intval($_POST['reservation_id']);

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

    $checkStmt->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
    $checkStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

    $checkStmt->execute();

    $reservation = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        die("Reserva não encontrada.");
    }

    if ($reservation['status_id'] == 2) {
        die("Esta reserva já foi cancelada.");
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

    $updateStmt->bindParam(':cancelled_by', $userId, PDO::PARAM_INT);
    $updateStmt->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);

    $updateStmt->execute();

    header("Location: ../pages/my_reservations.php");
    exit;

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

?>
