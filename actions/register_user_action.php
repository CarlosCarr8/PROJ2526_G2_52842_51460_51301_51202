<?php
include __DIR__ . "/../includes/auth_check.php";
include __DIR__ . "/../includes/role_check.php";
requireRole(["administrator"]);

require_once __DIR__ . "/../config/db.php";

function nullIfEmpty($value) {
    $value = trim($value ?? "");
    return $value === "" ? null : $value;
}

function getRoleName(PDO $pdo, $roleId) {
    $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE role_id = ?");
    $stmt->execute([$roleId]);
    return $stmt->fetchColumn();
}

function deleteAllProfiles(PDO $pdo, $userId) {
    $tables = [
        "student_profiles",
        "professor_profiles",
        "funcionario_profiles",
        "administrator_profiles"
    ];

    foreach ($tables as $table) {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../admin/users.php");
    exit;
}

$mode = $_POST["mode"] ?? "";

try {
    if ($mode === "deactivate") {
        $userId = $_POST["user_id"] ?? null;

        if (!$userId) {
            header("Location: ../admin/users.php?error=1");
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE user_id = ?");
        $stmt->execute([$userId]);

        header("Location: ../admin/users.php?success=1");
        exit;
    }

    $pdo->beginTransaction();

    $userId = $_POST["user_id"] ?? null;
    $roleId = $_POST["role_id"] ?? null;
    $roleName = getRoleName($pdo, $roleId);

    if (!$roleName) {
        throw new Exception("Tipo de utilizador inválido.");
    }

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $phoneNumber = nullIfEmpty($_POST["phone_number"] ?? "");
    $birthday = nullIfEmpty($_POST["birthday"] ?? "");
    $observations = nullIfEmpty($_POST["observations"] ?? "");
    $documentType = nullIfEmpty($_POST["document_type"] ?? "");
    $documentNumber = nullIfEmpty($_POST["document_number"] ?? "");
    $status = $_POST["status"] ?? "active";

    if ($name === "" || $email === "") {
        throw new Exception("Nome e email são obrigatórios.");
    }

    if ($mode === "create") {
        if ($password === "") {
            throw new Exception("A palavra-passe é obrigatória.");
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users
            (role_id, name, phone_number, email, password_hash, birthday, observations, document_type, document_number, status)
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $roleId,
            $name,
            $phoneNumber,
            $email,
            $passwordHash,
            $birthday,
            $observations,
            $documentType,
            $documentNumber,
            $status
        ]);

        $userId = $pdo->lastInsertId();

    } elseif ($mode === "edit") {
        if (!$userId) {
            throw new Exception("Utilizador inválido.");
        }

        if ($password !== "") {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                UPDATE users
                SET role_id = ?, name = ?, phone_number = ?, email = ?, password_hash = ?,
                    birthday = ?, observations = ?, document_type = ?, document_number = ?, status = ?
                WHERE user_id = ?
            ");

            $stmt->execute([
                $roleId,
                $name,
                $phoneNumber,
                $email,
                $passwordHash,
                $birthday,
                $observations,
                $documentType,
                $documentNumber,
                $status,
                $userId
            ]);

        } else {
            $stmt = $pdo->prepare("
                UPDATE users
                SET role_id = ?, name = ?, phone_number = ?, email = ?,
                    birthday = ?, observations = ?, document_type = ?, document_number = ?, status = ?
                WHERE user_id = ?
            ");

            $stmt->execute([
                $roleId,
                $name,
                $phoneNumber,
                $email,
                $birthday,
                $observations,
                $documentType,
                $documentNumber,
                $status,
                $userId
            ]);
        }

        deleteAllProfiles($pdo, $userId);

    } else {
        throw new Exception("Modo inválido.");
    }

    if ($roleName === "student") {
        $studentNumber = trim($_POST["student_number"] ?? "");
        $courseId = $_POST["student_course_id"] ?? null;
        $academicYear = nullIfEmpty($_POST["student_academic_year"] ?? "");

        if ($studentNumber === "" || empty($courseId)) {
            throw new Exception("Dados de estudante incompletos.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO student_profiles
            (user_id, student_number, course_id, academic_year)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $studentNumber,
            $courseId,
            $academicYear
        ]);
    }

    if ($roleName === "professor") {
        $professorNumber = trim($_POST["professor_number"] ?? "");
        $departmentId = $_POST["professor_department_id"] ?? null;
        $academicYear = nullIfEmpty($_POST["professor_academic_year"] ?? "");
        $office = nullIfEmpty($_POST["office"] ?? "");

        if ($professorNumber === "" || empty($departmentId)) {
            throw new Exception("Dados de professor incompletos.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO professor_profiles
            (user_id, professor_number, department_id, academic_year, office)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $professorNumber,
            $departmentId,
            $academicYear,
            $office
        ]);
    }

    if ($roleName === "funcionario") {
        $funcionarioNumber = trim($_POST["funcionario_number"] ?? "");
        $departmentId = $_POST["funcionario_department_id"] ?? null;
        $cargo = trim($_POST["cargo"] ?? "");
        $serviceArea = nullIfEmpty($_POST["service_area"] ?? "");

        if ($funcionarioNumber === "" || empty($departmentId) || $cargo === "") {
            throw new Exception("Dados de funcionário incompletos.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO funcionario_profiles
            (user_id, funcionario_number, department_id, cargo, service_area)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $funcionarioNumber,
            $departmentId,
            $cargo,
            $serviceArea
        ]);
    }

    if ($roleName === "administrator") {
        $administratorNumber = trim($_POST["administrator_number"] ?? "");
        $departmentId = $_POST["administrator_department_id"] ?? null;
        $adminLevel = $_POST["admin_level"] ?? "normal";

        if ($administratorNumber === "" || empty($departmentId)) {
            throw new Exception("Dados de administrador incompletos.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO administrator_profiles
            (user_id, administrator_number, department_id, admin_level)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $administratorNumber,
            $departmentId,
            $adminLevel
        ]);
    }

    $stmt = $pdo->prepare("DELETE FROM user_permissions WHERE user_id = ?");
    $stmt->execute([$userId]);

    $permissions = $_POST["permissions"] ?? [];

    foreach ($permissions as $permissionId) {
        $stmt = $pdo->prepare("
            INSERT INTO user_permissions (user_id, permission_id)
            VALUES (?, ?)
        ");

        $stmt->execute([$userId, $permissionId]);
    }

    $pdo->commit();

    header("Location: ../admin/users.php?success=1");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die("Erro ao guardar utilizador: " . $e->getMessage());
}
?>