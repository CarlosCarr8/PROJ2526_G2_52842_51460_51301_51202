<?php

session_start(); //inicia a sessão

if (isset($_SESSION["user_id"])) //se o utilizador já estiver autenticado
{
    header("Location: dashboard.php"); //redireciona para o painel principal
    exit;
}

$pageTitle = "Início - PCU"; //título da página
$basePath = ""; //caminho base

include "includes/header.php"; //inclui o cabeçalho
?>

<main class="landing-page">
    <div class="container py-5">
        <div class="row align-items-center min-vh-75">
            <div class="col-md-7">

                <h1 class="display-5 fw-bold">
                    Plataforma Inteligente de Gestão de Campus Universitário
                </h1>

                <p class="lead mt-3">
                    Sistema integrado para gestão de espaços, equipamentos, mobilidade,
                    sensores IoT, alertas, sustentabilidade e reservas no campus universitário.
                </p>

                <div class="mt-4">

                    <a href="login.php" class="btn btn-primary btn-lg">
                        Entrar na Plataforma
                    </a>
                </div>
            </div>

            <div class="col-md-5 mt-5 mt-md-0">
                <div class="card shadow-lg border-0 p-4">
                    <h4 class="mb-3">
                        Funcionalidades principais
                    </h4>

                    <ul class="list-unstyled">

                        <li class="mb-2">
                            ✓ Autenticação de utilizadores
                        </li>

                        <li class="mb-2">
                            ✓ Gestão de salas e laboratórios
                        </li>

                        <li class="mb-2">
                            ✓ Reserva de equipamentos
                        </li>

                        <li class="mb-2">
                            ✓ Mobilidade: bicicletas e trotinetes
                        </li>

                        <li class="mb-2">
                            ✓ Monitorização IoT
                        </li>

                        <li class="mb-2">
                            ✓ Alertas e relatórios
                        </li>

                        <li class="mb-2">
                            ✓ Interface LSS
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include "includes/footer.php"; //inclui o rodapé ?>