<?php
$host = "localhost";
$dbname = "pcu_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO( //cria a ligação à base de dados
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //ativa exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); //resultados em formato associativo

} 
catch (PDOException $e) { //procura erros na ligação à base de dados
    echo $e;
    die("Erro ao ligar à base de dados.");
}
?>