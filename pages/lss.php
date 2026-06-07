<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

$resultado = "";
$comando = "";

function guardarComandoLSS($pdo, $userId, $comando, $resultado, $status) {
    try {
        $sql = "
            INSERT INTO lss_commands (
                user_id,
                command_text,
                result,
                status,
                executed_at
            )
            VALUES (
                :user_id,
                :command_text,
                :result,
                :status,
                NOW()
            )
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':command_text', $comando);
        $stmt->bindParam(':result', $resultado);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
    }
    catch (PDOException $e) {
        // Se a tabela lss_commands ainda não existir, a página continua a funcionar.
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $comando = trim($_POST["comando"] ?? "");

    if (!isset($_SESSION['user_id'])) {
        $resultado = "Utilizador não autenticado.";
    }
    elseif ($comando == "") {
        $resultado = "Erro: escreva um comando LSS.";
    }
    else {

        $python = "python";
        $script = __DIR__ . "/../lss/executer.py";

        $process = proc_open(
            $python . " " . escapeshellarg($script),
            [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ],
            $pipes
        );

        if (!is_resource($process)) {
            $resultado = "Erro: não foi possível iniciar o Python.";
        }
        else {

            fwrite($pipes[0], $comando);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            proc_close($process);

            if (!empty($errors)) {
                $resultado = $errors;
                guardarComandoLSS($pdo, $_SESSION['user_id'], $comando, $resultado, "erro");
            }
            else {

                $dados = json_decode($output, true);

                if (!$dados || !isset($dados['comando'])) {
                    $resultado = $output;
                    guardarComandoLSS($pdo, $_SESSION['user_id'], $comando, $resultado, "erro");
                }
                else {
                    try {

                        $userId = $_SESSION['user_id'];
                        $tipo = $dados['tipo'] ?? "";

                        $tipos = [
                            "sala" => "room",
                            "laboratorio" => "laboratory",
                            "equipamento" => "equipment",
                            "bicicleta" => "bicycle",
                            "trotinete" => "scooter"
                        ];

                        $tipoBD = $tipos[$tipo] ?? $tipo;

                        if ($dados['comando'] == "reservar") {

                            $espaco = $dados['espaco'];
                            $data = $dados['data'];
                            $horaInicio = $dados['hora_inicio'];
                            $horaFim = $dados['hora_fim'];

                            $startDatetime = $data . " " . $horaInicio . ":00";
                            $endDatetime = $data . " " . $horaFim . ":00";

                            $sql = "
                                SELECT r.resource_id
                                FROM resources r
                                INNER JOIN resource_types rt
                                    ON r.resource_type_id = rt.resource_type_id
                                WHERE LOWER(r.name) = LOWER(:name)
                                AND LOWER(rt.type_name) = LOWER(:type_name)
                                AND r.status = 'available'
                                LIMIT 1
                            ";

                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':name', $espaco);
                            $stmt->bindParam(':type_name', $tipoBD);
                            $stmt->execute();
                            $resource = $stmt->fetch();

                            if (!$resource) {
                                $resultado = "Erro: recurso não encontrado ou indisponível.";
                                guardarComandoLSS($pdo, $userId, $comando, $resultado, "erro");
                            }
                            else {

                                $resourceId = $resource['resource_id'];

                                $overlapSql = "
                                    SELECT reservation_id
                                    FROM reservations
                                    WHERE resource_id = :resource_id
                                    AND status_id = 1
                                    AND start_datetime < :new_end
                                    AND end_datetime > :new_start
                                ";

                                $overlapStmt = $pdo->prepare($overlapSql);
                                $overlapStmt->bindParam(':resource_id', $resourceId, PDO::PARAM_INT);
                                $overlapStmt->bindParam(':new_start', $startDatetime);
                                $overlapStmt->bindParam(':new_end', $endDatetime);
                                $overlapStmt->execute();

                                if ($overlapStmt->fetch()) {
                                    $resultado = "Erro: este recurso já está reservado nesse horário.";
                                    guardarComandoLSS($pdo, $userId, $comando, $resultado, "erro");
                                }
                                else {

                                    $insertSql = "
                                        INSERT INTO reservations (
                                            user_id,
                                            resource_id,
                                            status_id,
                                            start_datetime,
                                            end_datetime,
                                            quantity,
                                            purpose,
                                            created_at
                                        )
                                        VALUES (
                                            :user_id,
                                            :resource_id,
                                            1,
                                            :start_datetime,
                                            :end_datetime,
                                            1,
                                            'Reserva efetuada através da linguagem LSS',
                                            NOW()
                                        )
                                    ";

                                    $insertStmt = $pdo->prepare($insertSql);
                                    $insertStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                                    $insertStmt->bindParam(':resource_id', $resourceId, PDO::PARAM_INT);
                                    $insertStmt->bindParam(':start_datetime', $startDatetime);
                                    $insertStmt->bindParam(':end_datetime', $endDatetime);
                                    $insertStmt->execute();

                                    $resultado = "Reserva criada com sucesso através do LSS.";
                                    guardarComandoLSS($pdo, $userId, $comando, $resultado, "sucesso");
                                }
                            }
                        }
                        elseif ($dados['comando'] == "cancelar") {

                            $reservationId = intval($dados['reserva_id']);

                            $sql = "
                                UPDATE reservations
                                SET
                                    status_id = 2,
                                    cancelled_at = NOW(),
                                    cancelled_by = :cancelled_by
                                WHERE reservation_id = :reservation_id
                                AND user_id = :user_id
                            ";

                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':cancelled_by', $userId, PDO::PARAM_INT);
                            $stmt->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
                            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                            $stmt->execute();

                            if ($stmt->rowCount() > 0) {
                                $resultado = "Reserva cancelada com sucesso através do LSS.";
                                guardarComandoLSS($pdo, $userId, $comando, $resultado, "sucesso");
                            }
                            else {
                                $resultado = "Erro: reserva não encontrada ou não pertence ao utilizador.";
                                guardarComandoLSS($pdo, $userId, $comando, $resultado, "erro");
                            }
                        }
                        elseif ($dados['comando'] == "disponibilidade") {

                            $data = $dados['data'];
                            $horaInicio = $dados['hora_inicio'];
                            $horaFim = $dados['hora_fim'];

                            $startDatetime = $data . " " . $horaInicio . ":00";
                            $endDatetime = $data . " " . $horaFim . ":00";

                            $sql = "
                                SELECT r.name
                                FROM resources r
                                INNER JOIN resource_types rt
                                    ON r.resource_type_id = rt.resource_type_id
                                WHERE LOWER(rt.type_name) = LOWER(:type_name)
                                AND r.status = 'available'
                                AND r.resource_id NOT IN (
                                    SELECT resource_id
                                    FROM reservations
                                    WHERE status_id = 1
                                    AND start_datetime < :new_end
                                    AND end_datetime > :new_start
                                )
                            ";

                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':type_name', $tipoBD);
                            $stmt->bindParam(':new_start', $startDatetime);
                            $stmt->bindParam(':new_end', $endDatetime);
                            $stmt->execute();
                            $resources = $stmt->fetchAll();

                            if (count($resources) == 0) {
                                $resultado = "Não existem recursos disponíveis para esse horário.";
                            }
                            else {
                                $resultado = "Recursos disponíveis:\n";
                                foreach ($resources as $resource) {
                                    $resultado .= "- " . $resource['name'] . "\n";
                                }
                            }

                            guardarComandoLSS($pdo, $userId, $comando, $resultado, "sucesso");
                        }
                        else {
                            $resultado = "Comando reconhecido pelo parser:\n" . $output;
                            guardarComandoLSS($pdo, $userId, $comando, $resultado, "sucesso");
                        }
                    }
                    catch (PDOException $e) {
                        $resultado = "Erro na base de dados: " . $e->getMessage();
                        guardarComandoLSS($pdo, $_SESSION['user_id'], $comando, $resultado, "erro");
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>PCU - LSS</title>

<style>

body{
    margin:0;
    font-family:Arial, sans-serif;

    background:linear-gradient(
        135deg,
        #eaf4fc,
        #d6ecff
    );

    min-height:100vh;
}

.container{
    width:900px;
    margin:50px auto;

    background:white;

    border-radius:20px;

    padding:30px;

    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

h1{
    text-align:center;
    color:#0b1f3a;
}

textarea{
    width:100%;
    height:300px;

    background:#0d1117;
    color:#58a6ff;

    border:none;
    border-radius:10px;

    padding:15px;

    font-family:Consolas, monospace;
    font-size:16px;

    resize:none;

    box-sizing:border-box;
}

button{
    margin-top:20px;

    width:100%;
    height:50px;

    border:none;
    border-radius:25px;

    background:linear-gradient(
        90deg,
        #2d98da,
        #5dade2
    );

    color:white;
    font-size:18px;
    font-weight:bold;

    cursor:pointer;
}

button:hover{
    opacity:0.9;
}

.resultado{

    margin-top:25px;

    background:#f5f8fb;

    border-radius:10px;

    padding:15px;

    white-space:pre-wrap;
}

.voltar{

    display:inline-block;

    margin-bottom:20px;

    text-decoration:none;

    color:#2d98da;

    font-weight:bold;
}

</style>

</head>
<body>

<div class="container">

        <a href="../dashboard.php" class="voltar">
        ← Voltar
        </a>

    <h1>PCU - Linguagem LSS</h1>

    <form method="POST">

        <textarea
            name="comando"
            placeholder="Escreva o comando LSS..."
        ><?= htmlspecialchars($comando) ?></textarea>

        <button type="submit">
            Executar
        </button>

    </form>

    <?php if(!empty($resultado)): ?>

        <div class="resultado">
            <?= htmlspecialchars($resultado) ?>
        </div>

    <?php endif; ?>

</div>

</body>
</html>