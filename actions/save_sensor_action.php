<?php 

// verifica se o utilizador está autenticado
include __DIR__ . "/../includes/auth_check.php";

// verifica se o utilizador tem permissões válidas
include __DIR__ . "/../includes/role_check.php";

// permite apenas administradores
requireRole(["administrator"]);

// ligação à base de dados
require_once __DIR__ . "/../config/db.php";

// função que transforma valores vazios em NULL
function nullIfEmpty($value) 
{
    $value = trim($value ?? "");

    return $value === "" ? null : $value;
}

// função que calcula o estado da leitura do sensor
function calculateReadingStatus($value, $min, $max) 
{
    // valor abaixo do mínimo permitido
    if ($min !== null && $value < $min) 
    {
        return "critical";
    }

    // valor acima do máximo permitido
    if ($max !== null && $value > $max) 
    {
        return "critical";
    }

    // valor próximo do limite mínimo
    if ($min !== null && $min != 0 && $value <= ($min * 1.10)) 
    {
        return "warning";
    }

    // valor próximo do limite máximo
    if ($max !== null && $value >= ($max * 0.90)) 
    {
        return "warning";
    }

    // valor dentro dos limites normais
    return "normal";
}

// função que gera a mensagem de alerta
function alertMessage($sensorType, $resourceName, $value, $unit, $min, $max) 
{
    // valor acima do máximo
    if ($max !== null && $value > $max) 
    {
        return "Valor crítico em {$resourceName}: {$sensorType} atingiu {$value}{$unit}, acima do limite máximo {$max}{$unit}.";
    }

    // valor abaixo do mínimo
    if ($min !== null && $value < $min) 
    {
        return "Valor crítico em {$resourceName}: {$sensorType} atingiu {$value}{$unit}, abaixo do limite mínimo {$min}{$unit}.";
    }

    // mensagem genérica
    return "Valor anómalo detetado em {$resourceName}: {$sensorType} = {$value}{$unit}.";
}

// verifica se o pedido foi enviado através do método POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") 
{
    header("Location: ../admin/sensors.php");
    exit;
}

// obtém o modo de operação enviado pelo formulário
$mode = $_POST["mode"] ?? "";

try 
{
    // guardar ou atualizar sensor
    if ($mode === "save_sensor") 
    {
        // obtém os dados enviados pelo formulário
        $sensorId = nullIfEmpty($_POST["sensor_id"] ?? "");
        $sensorTypeId = $_POST["sensor_type_id"] ?? null;
        $resourceId = $_POST["resource_id"] ?? null;
        $code = trim($_POST["code"] ?? "");
        $name = trim($_POST["name"] ?? "");
        $status = $_POST["status"] ?? "active";
        $min = nullIfEmpty($_POST["alert_limit_min"] ?? "");
        $max = nullIfEmpty($_POST["alert_limit_max"] ?? "");

        // verifica se os campos obrigatórios foram preenchidos
        if (!$sensorTypeId || !$resourceId || $code === "" || $name === "") 
        {
            throw new Exception("Preencha todos os campos obrigatórios.");
        }

        // verifica se é uma atualização
        if ($sensorId) 
        {
            // atualiza o sensor existente
            $stmt = $pdo->prepare("
                UPDATE sensors
                SET
                    sensor_type_id = ?,
                    resource_id = ?,
                    code = ?,
                    name = ?,
                    status = ?,
                    alert_limit_min = ?,
                    alert_limit_max = ?,
                    updated_at = NOW()
                WHERE sensor_id = ?
            ");

            $stmt->execute
            ([
                $sensorTypeId,
                $resourceId,
                $code,
                $name,
                $status,
                $min,
                $max,
                $sensorId
            ]);
        } 
        
        // cria um novo sensor
        else 
        {
            $stmt = $pdo->prepare("
                INSERT INTO sensors
                (
                    sensor_type_id,
                    resource_id,
                    code,
                    name,
                    status,
                    alert_limit_min,
                    alert_limit_max
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute
            ([
                $sensorTypeId,
                $resourceId,
                $code,
                $name,
                $status,
                $min,
                $max
            ]);
        }

        // redireciona com sucesso
        header("Location: ../admin/sensors.php?success=1");
        exit;
    }

    // desativar sensor
    if ($mode === "deactivate") 
    {
        // obtém o ID do sensor
        $sensorId = $_POST["sensor_id"] ?? null;

        // verifica se o sensor existe
        if (!$sensorId) 
        {
            throw new Exception("Sensor inválido.");
        }

        // altera o estado para inativo
        $stmt = $pdo->prepare("
            UPDATE sensors
            SET
                status = 'inactive',
                updated_at = NOW()
            WHERE sensor_id = ?
        ");

        $stmt->execute([$sensorId]);

        // redireciona com sucesso
        header("Location: ../admin/sensors.php?success=1");
        exit;
    }

    // adicionar leitura ao sensor
    if ($mode === "add_reading") 
    {
        // obtém os dados enviados
        $sensorId = $_POST["sensor_id"] ?? null;
        $value = $_POST["value"] ?? null;

        // verifica se os dados são válidos
        if (!$sensorId || $value === null || $value === "") 
        {
            throw new Exception("Valor da leitura inválido.");
        }

        // procura informações do sensor
        $stmt = $pdo->prepare("
            SELECT
                s.sensor_id,
                s.status,
                s.alert_limit_min,
                s.alert_limit_max,
                st.type_name,
                st.unit,
                r.resource_id,
                r.name AS resource_name
            FROM sensors s
            INNER JOIN sensor_types st 
                ON s.sensor_type_id = st.sensor_type_id
            INNER JOIN resources r 
                ON s.resource_id = r.resource_id
            WHERE s.sensor_id = ?
        ");

        $stmt->execute([$sensorId]);

        // obtém os dados do sensor
        $sensor = $stmt->fetch();

        // verifica se o sensor existe
        if (!$sensor) 
        {
            throw new Exception("Sensor não encontrado.");
        }

        // verifica se o sensor está ativo
        if ($sensor["status"] !== "active") 
        {
            throw new Exception("Apenas sensores ativos podem enviar leituras.");
        }

        // converte os valores para decimal
        $value = (float)$value;

        $min = $sensor["alert_limit_min"] !== null
            ? (float)$sensor["alert_limit_min"]
            : null;

        $max = $sensor["alert_limit_max"] !== null
            ? (float)$sensor["alert_limit_max"]
            : null;

        // calcula o estado da leitura
        $readingStatus = calculateReadingStatus($value, $min, $max);

        // inicia transação
        $pdo->beginTransaction();

        // guarda a leitura do sensor
        $stmt = $pdo->prepare("
            INSERT INTO sensor_readings
            (
                sensor_id,
                value,
                status
            )
            VALUES (?, ?, ?)
        ");

        $stmt->execute
        ([
            $sensorId,
            $value,
            $readingStatus
        ]);

        // obtém o ID da leitura inserida
        $readingId = $pdo->lastInsertId();

        // verifica se a leitura é crítica
        if ($readingStatus === "critical") 
        {
            // gera a mensagem de alerta
            $message = alertMessage
            (
                $sensor["type_name"],
                $sensor["resource_name"],
                $value,
                $sensor["unit"] ?? "",
                $min,
                $max
            );

            // cria o alerta na base de dados
            $stmt = $pdo->prepare("
                INSERT INTO alerts
                (
                    sensor_id,
                    resource_id,
                    reading_id,
                    alert_type,
                    message,
                    severity,
                    status
                )
                VALUES (?, ?, ?, ?, ?, 'critical', 'open')
            ");

            $stmt->execute
            ([
                $sensorId,
                $sensor["resource_id"],
                $readingId,
                "critical_" . $sensor["type_name"],
                $message
            ]);
        }

        // confirma todas as operações
        $pdo->commit();

        // redireciona com sucesso
        header("Location: ../admin/sensors.php?success=1");
        exit;
    }

    // modo inválido
    throw new Exception("Modo inválido.");

} 

// captura erros do sistema
catch (Exception $e) 
{
    // desfaz alterações caso exista transação ativa
    if ($pdo->inTransaction()) 
    {
        $pdo->rollBack();
    }

    // apresenta mensagem de erro
    die("Erro no sistema IoT: " . $e->getMessage());
}

?>