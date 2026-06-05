<?php

session_start();

ini_set('display_errors', 1); //ativa os erros
error_reporting(E_ALL);

require_once '../config/db.php'; //conexao à bd


if (!isset($_SESSION['user_id'])) 
{
    die("Utilizador não autenticado");
}

//se na URL tem um ID válido
if (!isset($_GET['id']) || empty($_GET['id']))  
{
    die("ID de reserva inválido");
}

$userId = $_SESSION['user_id']; //id do ut
$reservationId = intval($_GET['id']); //ID para segurança

try {

    $checkSql = "
        SELECT
            reservation_id, 
            status_id
        FROM reservations
        WHERE reservation_id = :reservation_id //se a reserva existe
        AND user_id = :user_id //e se pertence ao ut
    ";

    $checkStmt = $pdo->prepare($checkSql); //consulta a BD
    $checkStmt->bindParam //associação id da reserva à consulta
    (
        ':reservation_id',
        $reservationId,
        PDO::PARAM_INT
    );

    //associação id da utilizador à consulta
    $checkStmt->bindParam 
    (
        ':user_id',
        $userId,
        PDO::PARAM_INT
    );

    $checkStmt->execute(); //executa a consulta
    $reservation = $checkStmt->fetch(); //e obtem os dados

    //verifica se foi encontrada
    if (!$reservation)  
    {
        die("Reservas não encontradas.");
    }

    //verifica se foi cancelada
    if ($reservation['status_id'] == 2) 
    {
        die("Esta reserva já foi cancelada.");
    }

    $updateSql = " //e aqui atualiza o estado para cancelada
        UPDATE reservations
        SET
            status_id = 2,
            cancelled_at = NOW(),
            cancelled_by = :cancelled_by
        WHERE reservation_id = :reservation_id
    ";

    $updateStmt = $pdo->prepare($updateSql); //prepara a atualização
    $updateStmt->bindParam //regista quem cancelou
    (
        ':cancelado por:',
        $userId,
        PDO::PARAM_INT
    );

    //diz qual reserva tem de atualizar
    $updateStmt->bindParam //diz qual reserva tem de atualizar
    (
        ':reservation_id',
        $reservationId,
        PDO::PARAM_INT
    );

    $updateStmt->execute(); //faz a atualização

    header("Location: ../pages/my_reservations.php"); //leva o ut para a página das reservas
    exit;
} 

catch (PDOException $e) //procura erros sobre a BD
{
    die("Erro: " . $e->getMessage());
}
?>
