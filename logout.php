<?php

session_start(); //inicia a sessão
$_SESSION = []; //remove todos os dados guardados na sessão
session_destroy(); //termina a sessão

header("Location: login.php"); //redireciona para a página de início de sessão
exit;
?>