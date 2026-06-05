<?php
include __DIR__ . "/../includes/auth_check.php";
include __DIR__ . "/../includes/role_check.php";
requireRole(["administrator", "funcionario"]);

require_once __DIR__ . "/../config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/alerts.php");
    exit;
}

$alertId = $_POST["alert_id"] ?? null;

if (!$alertId) {
    header("Location: ../pages/alerts.php?error=1");
    exit;
}

$stmt = $pdo->prepare("
    UPDATE alerts
    SET status = 'resolved',
        resolved_at = NOW(),
        resolved_by = ?
    WHERE alert_id = ?
");

$stmt->execute([$_SESSION["user_id"], $alertId]);

header("Location: ../pages/alerts.php?success=1");
exit;
?>