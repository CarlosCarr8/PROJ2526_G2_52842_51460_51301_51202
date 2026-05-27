<?php
session_start();

require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if (empty($email) || empty($password)) {
    header("Location: ../login.php?error=1");
    exit;
}

$sql = "
    SELECT 
        u.user_id,
        u.name,
        u.email,
        u.password_hash,
        u.status,
        r.role_name
    FROM users u
    INNER JOIN roles r ON u.role_id = r.role_id
    WHERE u.email = ?
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: ../login.php?error=1");
    exit;
}

if ($user["status"] !== "active") {
    header("Location: ../login.php?error=1");
    exit;
}

if (!password_verify($password, $user["password_hash"])) {
    header("Location: ../login.php?error=1");
    exit;
}

$_SESSION["user_id"] = $user["user_id"];
$_SESSION["name"] = $user["name"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role_name"];

header("Location: ../dashboard.php");
exit;
?>