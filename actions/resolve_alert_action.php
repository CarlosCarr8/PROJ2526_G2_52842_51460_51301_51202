<?php 

// verifica se o utilizador está autenticado
include __DIR__ . "/../includes/auth_check.php";

// verifica se o utilizador tem permissões válidas
include __DIR__ . "/../includes/role_check.php";

// permite apenas administradores e funcionários
requireRole(["administrator", "funcionario"]);

// ligação à base de dados
require_once __DIR__ . "/../config/db.php";

// verifica se o pedido foi enviado através do método POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") 
{
    // redireciona para a página de alertas
    header("Location: ../pages/alerts.php");
    exit;
}

// obtém o ID do alerta enviado pelo formulário
$alertId = $_POST["alert_id"] ?? null;

// verifica se foi recebido um ID válido
if (!$alertId) 
{
    // redireciona com mensagem de erro
    header("Location: ../pages/alerts.php?error=1");
    exit;
}

// consulta para marcar o alerta como resolvido
$stmt = $pdo->prepare("
    UPDATE alerts
    SET
        status = 'resolved',
        resolved_at = NOW(),
        resolved_by = ?
    WHERE alert_id = ?
");

// executa a atualização:
// 1. guarda o utilizador que resolveu o alerta
// 2. identifica qual alerta deve ser atualizado
$stmt->execute
([
    $_SESSION["user_id"],
    $alertId
]);

// redireciona com mensagem de sucesso
header("Location: ../pages/alerts.php?success=1");
exit;

?>