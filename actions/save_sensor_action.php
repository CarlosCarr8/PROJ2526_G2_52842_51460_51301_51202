<?php
include __DIR__ . "/../includes/auth_check.php";
include __DIR__ . "/../includes/role_check.php";
requireRole(["administrator"]);

require_once __DIR__ . "/../config/db.php";

function nullIfEmpty($value) {
    $value = trim($value ?? "");
    return $value === "" ? null : $value;
}

function calculateReadingStatus($value, $min, $max) {
    if ($min !== null && $value < $min) {
        return "critical";
    }

    if ($max !== null && $value > $max) {
        return "critical";
    }

    if ($min !== null && $min != 0 && $value <= ($min * 1.10)) {
        return "warning";
    }

    if ($max !== null && $value >= ($max * 0.90)) {
        return "warning";
    }

    return "normal";
}

function alertMessage($sensorType, $resourceName, $value, $unit, $min, $max) {
    if ($max !== null && $value > $max) {
        return "Valor crítico em {$resourceName}: {$sensorType} atingiu {$value}{$unit}, acima do limite máximo {$max}{$unit}.";
    }

    if ($min !== null && $value < $min) {
        return "Valor crítico em {$resourceName}: {$sensorType} atingiu {$value}{$unit}, abaixo do limite mínimo {$min}{$unit}.";
    }

    return "Valor anómalo detetado em {$resourceName}: {$sensorType} = {$value}{$unit}.";
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../admin/sensors.php");
    exit;
}

$mode = $_POST["mode"] ?? "";

try {
    if ($mode === "save_sensor") {
        $sensorId = nullIfEmpty($_POST["sensor_id"] ?? "");
        $sensorTypeId = $_POST["sensor_type_id"] ?? null;
        $resourceId = $_POST["resource_id"] ?? null;
        $code = trim($_POST["code"] ?? "");
        $name = trim($_POST["name"] ?? "");
        $status = $_POST["status"] ?? "active";
        $min = nullIfEmpty($_POST["alert_limit_min"] ?? "");
        $max = nullIfEmpty($_POST["alert_limit_max"] ?? "");

        if (!$sensorTypeId || !$resourceId || $code === "" || $name === "") {
            throw new Exception("Preencha todos os campos obrigatórios.");
        }

        if ($sensorId) {
            $stmt = $pdo->prepare("
                UPDATE sensors
                SET sensor_type_id = ?, resource_id = ?, code = ?, name = ?, status = ?,
                    alert_limit_min = ?, alert_limit_max = ?, updated_at = NOW()
                WHERE sensor_id = ?
            ");

            $stmt->execute([$sensorTypeId, $resourceId, $code, $name, $status, $min, $max, $sensorId]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO sensors
                (sensor_type_id, resource_id, code, name, status, alert_limit_min, alert_limit_max)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([$sensorTypeId, $resourceId, $code, $name, $status, $min, $max]);
        }

        header("Location: ../admin/sensors.php?success=1");
        exit;
    }

    if ($mode === "deactivate") {
        $sensorId = $_POST["sensor_id"] ?? null;

        if (!$sensorId) {
            throw new Exception("Sensor inválido.");
        }

        $stmt = $pdo->prepare("UPDATE sensors SET status = 'inactive', updated_at = NOW() WHERE sensor_id = ?");
        $stmt->execute([$sensorId]);

        header("Location: ../admin/sensors.php?success=1");
        exit;
    }

    if ($mode === "add_reading") {
        $sensorId = $_POST["sensor_id"] ?? null;
        $value = $_POST["value"] ?? null;

        if (!$sensorId || $value === null || $value === "") {
            throw new Exception("Valor de leitura inválido.");
        }

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
            INNER JOIN sensor_types st ON s.sensor_type_id = st.sensor_type_id
            INNER JOIN resources r ON s.resource_id = r.resource_id
            WHERE s.sensor_id = ?
        ");
        $stmt->execute([$sensorId]);
        $sensor = $stmt->fetch();

        if (!$sensor) {
            throw new Exception("Sensor não encontrado.");
        }

        if ($sensor["status"] !== "active") {
            throw new Exception("Apenas sensores ativos podem enviar leituras.");
        }

        $value = (float)$value;
        $min = $sensor["alert_limit_min"] !== null ? (float)$sensor["alert_limit_min"] : null;
        $max = $sensor["alert_limit_max"] !== null ? (float)$sensor["alert_limit_max"] : null;

        $readingStatus = calculateReadingStatus($value, $min, $max);

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO sensor_readings (sensor_id, value, status)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$sensorId, $value, $readingStatus]);

        $readingId = $pdo->lastInsertId();

        if ($readingStatus === "critical") {
            $message = alertMessage(
                $sensor["type_name"],
                $sensor["resource_name"],
                $value,
                $sensor["unit"] ?? "",
                $min,
                $max
            );

            $stmt = $pdo->prepare("
                INSERT INTO alerts
                (sensor_id, resource_id, reading_id, alert_type, message, severity, status)
                VALUES (?, ?, ?, ?, ?, 'critical', 'open')
            ");

            $stmt->execute([
                $sensorId,
                $sensor["resource_id"],
                $readingId,
                "critical_" . $sensor["type_name"],
                $message
            ]);
        }

        $pdo->commit();

        header("Location: ../admin/sensors.php?success=1");
        exit;
    }

    throw new Exception("Modo inválido.");

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die("Erro no sistema IoT: " . $e->getMessage());
}
?>