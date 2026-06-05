<?php
include __DIR__ . "/../includes/auth_check.php"; //verifica se o utilizador está autenticado
include __DIR__ . "/../includes/role_check.php"; //verifica as permissões
requireRole(["administrator"]); //apenas administradores podem aceder

require_once __DIR__ . "/../config/db.php"; //conexão à BD

//converte campos vazios para NULL
function nullIfEmpty($value) {
    $value = trim($value ?? "");
    return $value === "" ? null : $value;
}

//obtém o nome da função através do ID
function getRoleName(PDO $pdo, $roleId) {
    $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE role_id = ?");
    $stmt->execute([$roleId]);
    return $stmt->fetchColumn();
}

//remove perfis antigos do utilizador
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

//verifica se o formulário foi enviado por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../admin/users.php");
    exit;
}

$mode = $_POST["mode"] ?? ""; //modo de operação

try {

    //desativa um utilizador
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

    $pdo->beginTransaction(); //inicia transação

    $userId = $_POST["user_id"] ?? null;
    $roleId = $_POST["role_id"] ?? null;
    $roleName = getRoleName($pdo, $roleId);

    //verifica se a função existe
    if (!$roleName) {
        throw new Exception("Tipo de utilizador inválido.");
    }

    //obtém os dados gerais do utilizador
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $phoneNumber = nullIfEmpty($_POST["phone_number"] ?? "");
    $birthday = nullIfEmpty($_POST["birthday"] ?? "");
    $observations = nullIfEmpty($_POST["observations"] ?? "");
    $documentType = nullIfEmpty($_POST["document_type"] ?? "");
    $documentNumber = nullIfEmpty($_POST["document_number"] ?? "");
    $status = $_POST["status"] ?? "active";

    //verifica os campos obrigatórios
    if ($name === "" || $email === "") {
        throw new Exception("Nome e email são obrigatórios.");
    }

    //cria um novo utilizador
    if ($mode === "create") {

        if ($password === "") {
            throw new Exception("A palavra-passe é obrigatória.");
        }

        //encripta a palavra-passe
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        //insere o utilizador na BD
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

        $userId = $pdo->lastInsertId(); //obtém o ID do novo utilizador

        //edita um utilizador existente
    } elseif ($mode === "edit") {

        if (!$userId) {
            throw new Exception("Utilizador inválido.");
        }

        //atualiza também a palavra-passe
        if ($password !== "") {

            $passwordHash = password_hash($password, PASSWORD_DEFAULT); //encripta a palavra-passe

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

        //atualiza sem alterar a palavra-passe
        } 
        
        else {

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

        deleteAllProfiles($pdo, $userId); //remove perfis antigos

    } else {

        throw new Exception("Modo inválido.");
    }

    //perfil de estudante
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

    //perfil de professor
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

    //perfil de funcionário
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

        //perfil de administrador
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

    //remove permissões antigas
    $stmt = $pdo->prepare("DELETE FROM user_permissions WHERE user_id = ?");
    $stmt->execute([$userId]);

    //obtém as permissões selecionadas
    $permissions = $_POST["permissions"] ?? [];

    //atribui as novas permissões
    foreach ($permissions as $permissionId) {

        $stmt = $pdo->prepare("
            INSERT INTO user_permissions (user_id, permission_id)
            VALUES (?, ?)
        ");

        $stmt->execute([
            $userId,
            $permissionId
        ]);
    }

    $pdo->commit(); //confirma todas as alterações
    header("Location: ../admin/users.php?success=1");
    exit;

} catch (Exception $e) {
    //desfaz as alterações em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Erro ao guardar utilizador: " . $e->getMessage());
}
?>