<?php
session_start(); //inicia a sessão

require_once "../config/db.php"; //conexão à BD

//verifica se o formulário foi enviado por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") 
{
    header("Location: ../login.php");
    exit;
}

//obtém os dados introduzidos pelo ut
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

//verifica se os campos foram preenchidos
if (empty($email) || empty($password)) 
{
    header("Location: ../login.php?error=1");
    exit;
}
//procura o utilizador pelo email e obtém os seus dados e função
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

//procura o utilizador pelo email
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch();

//verifica se o utilizador existe
if (!$user) 
{
    header("Location: ../login.php?error=1");
    exit;
}

//verifica se a conta está ativa
if ($user["status"] !== "active") 
{
    header("Location: ../login.php?error=1");
    exit;
}

//verifica se a passe está correta
if (!password_verify($password, $user["password_hash"])) 
{
    header("Location: ../login.php?error=1");
    exit;
}

//guarda os dados do utilizador na sessão
$_SESSION["user_id"] = $user["user_id"];
$_SESSION["name"] = $user["name"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role_name"];

//manda para o dashboard
header("Location: ../dashboard.php");
exit;
?>