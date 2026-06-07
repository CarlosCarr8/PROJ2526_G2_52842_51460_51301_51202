<?php

session_start(); //inicia a sessão

if (isset($_SESSION["user_id"])) //se o utilizador já estiver autenticado
{
    header("Location: dashboard.php"); //redireciona para o painel principal
    exit;
}

$error = $_GET["error"] ?? null; //obtém o parâmetro de erro caso exista ?>

<!DOCTYPE html>

<html lang="pt">
<head>

    <meta charset="UTF-8">

    <title>
        Iniciar Sessão - PCU
    </title>

    <!-- bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- css próprio -->
    <link
        href="assets/css/style.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow p-4" style="width: 400px;">
            <h3 class="text-center mb-4">
                PCU - Iniciar Sessão
            </h3>

            <?php if ($error): //mensagem de erro ?>

                <div class="alert alert-danger">
                    Email ou palavra-passe inválidos.
                </div>

            <?php endif; ?>

            <form
                action="actions/login_action.php"
                method="POST"
            >
                <div class="mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Palavra-passe
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100"
                >
                    Entrar
                </button>
            </form>
        </div>
    </div>
</body>
</html>