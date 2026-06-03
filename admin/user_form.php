<?php
include __DIR__ . "/../includes/auth_check.php";
include __DIR__ . "/../includes/role_check.php";
requireRole(["administrator"]);

require_once __DIR__ . "/../config/db.php";

$pageTitle = "Utilizador - PCU";
$basePath = "../";

function e($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

$userId = $_GET["id"] ?? null;
$isEdit = !empty($userId);

$user = null;
$student = [];
$professor = [];
$funcionario = [];
$administrator = [];
$userPermissions = [];

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: users.php?error=1");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $student = $stmt->fetch() ?: [];

    $stmt = $pdo->prepare("SELECT * FROM professor_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $professor = $stmt->fetch() ?: [];

    $stmt = $pdo->prepare("SELECT * FROM funcionario_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $funcionario = $stmt->fetch() ?: [];

    $stmt = $pdo->prepare("SELECT * FROM administrator_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $administrator = $stmt->fetch() ?: [];

    $stmt = $pdo->prepare("SELECT permission_id FROM user_permissions WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userPermissions = array_column($stmt->fetchAll(), "permission_id");
}

$roles = $pdo->query("SELECT * FROM roles ORDER BY role_name")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
$courses = $pdo->query("SELECT * FROM courses ORDER BY course_name")->fetchAll();
$permissions = $pdo->query("SELECT * FROM permissions ORDER BY permission_name")->fetchAll();

include __DIR__ . "/../includes/header.php";
?>

<div class="app-layout">
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><?= $isEdit ? "Editar Utilizador" : "Novo Utilizador" ?></h2>
                <p class="text-muted">
                    Preencha os dados comuns e os dados específicos do tipo de utilizador.
                </p>
            </div>

            <a href="users.php" class="btn btn-outline-secondary">
                Voltar
            </a>
        </div>

        <form action="../actions/register_user_action.php" method="POST">
            <input type="hidden" name="mode" value="<?= $isEdit ? "edit" : "create" ?>">
            <input type="hidden" name="user_id" value="<?= e($user["user_id"] ?? "") ?>">

            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">
                    Dados gerais
                </div>

                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input 
                            type="text" 
                            name="name" 
                            class="form-control" 
                            required
                            value="<?= e($user["name"] ?? "") ?>"
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input 
                            type="email" 
                            name="email" 
                            class="form-control" 
                            required
                            value="<?= e($user["email"] ?? "") ?>"
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Palavra-passe <?= $isEdit ? "(deixar vazio para manter)" : "" ?>
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            class="form-control"
                            <?= $isEdit ? "" : "required" ?>
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Telefone</label>
                        <input 
                            type="text" 
                            name="phone_number" 
                            class="form-control"
                            value="<?= e($user["phone_number"] ?? "") ?>"
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Data de nascimento</label>
                        <input 
                            type="date" 
                            name="birthday" 
                            class="form-control"
                            value="<?= e($user["birthday"] ?? "") ?>"
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tipo de documento</label>
                        <input 
                            type="text" 
                            name="document_type" 
                            class="form-control"
                            placeholder="CC, Passaporte, etc."
                            value="<?= e($user["document_type"] ?? "") ?>"
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Número do documento</label>
                        <input 
                            type="text" 
                            name="document_number" 
                            class="form-control"
                            value="<?= e($user["document_number"] ?? "") ?>"
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= (($user["status"] ?? "active") === "active") ? "selected" : "" ?>>
                                Ativo
                            </option>
                            <option value="inactive" <?= (($user["status"] ?? "") === "inactive") ? "selected" : "" ?>>
                                Inativo
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tipo de utilizador</label>
                        <select name="role_id" id="role_id" class="form-select" required>
                            <option value="">Selecionar...</option>

                            <?php foreach ($roles as $role): ?>
                                <option 
                                    value="<?= $role["role_id"] ?>"
                                    data-role="<?= e($role["role_name"]) ?>"
                                    <?= (($user["role_id"] ?? "") == $role["role_id"]) ? "selected" : "" ?>
                                >
                                    <?= e($role["role_name"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Observações</label>
                        <textarea name="observations" class="form-control" rows="1"><?= e($user["observations"] ?? "") ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4 role-section" data-role-section="student">
                <div class="card-header fw-bold">Dados de Estudante</div>

                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Número de estudante</label>
                        <input type="text" name="student_number" class="form-control" value="<?= e($student["student_number"] ?? "") ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Curso</label>
                        <select name="student_course_id" class="form-select">
                            <option value="">Selecionar...</option>
                            <?php foreach ($courses as $course): ?>
                                <option 
                                    value="<?= $course["course_id"] ?>"
                                    <?= (($student["course_id"] ?? "") == $course["course_id"]) ? "selected" : "" ?>
                                >
                                    <?= e($course["course_name"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Ano académico</label>
                        <input type="text" name="student_academic_year" class="form-control" placeholder="2025/2026" value="<?= e($student["academic_year"] ?? "") ?>">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4 role-section" data-role-section="professor">
                <div class="card-header fw-bold">Dados de Professor</div>

                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Número de professor</label>
                        <input type="text" name="professor_number" class="form-control" value="<?= e($professor["professor_number"] ?? "") ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Departamento</label>
                        <select name="professor_department_id" class="form-select">
                            <option value="">Selecionar...</option>
                            <?php foreach ($departments as $department): ?>
                                <option 
                                    value="<?= $department["department_id"] ?>"
                                    <?= (($professor["department_id"] ?? "") == $department["department_id"]) ? "selected" : "" ?>
                                >
                                    <?= e($department["name"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Ano académico</label>
                        <input type="text" name="professor_academic_year" class="form-control" value="<?= e($professor["academic_year"] ?? "") ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Gabinete</label>
                        <input type="text" name="office" class="form-control" value="<?= e($professor["office"] ?? "") ?>">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4 role-section" data-role-section="funcionario">
                <div class="card-header fw-bold">Dados de Funcionário</div>

                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Número de funcionário</label>
                        <input type="text" name="funcionario_number" class="form-control" value="<?= e($funcionario["funcionario_number"] ?? "") ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Departamento</label>
                        <select name="funcionario_department_id" class="form-select">
                            <option value="">Selecionar...</option>
                            <?php foreach ($departments as $department): ?>
                                <option 
                                    value="<?= $department["department_id"] ?>"
                                    <?= (($funcionario["department_id"] ?? "") == $department["department_id"]) ? "selected" : "" ?>
                                >
                                    <?= e($department["name"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Cargo</label>
                        <input type="text" name="cargo" class="form-control" value="<?= e($funcionario["cargo"] ?? "") ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Área de serviço</label>
                        <input type="text" name="service_area" class="form-control" value="<?= e($funcionario["service_area"] ?? "") ?>">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4 role-section" data-role-section="administrator">
                <div class="card-header fw-bold">Dados de Administrador</div>

                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Número de administrador</label>
                        <input type="text" name="administrator_number" class="form-control" value="<?= e($administrator["administrator_number"] ?? "") ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Departamento</label>
                        <select name="administrator_department_id" class="form-select">
                            <option value="">Selecionar...</option>
                            <?php foreach ($departments as $department): ?>
                                <option 
                                    value="<?= $department["department_id"] ?>"
                                    <?= (($administrator["department_id"] ?? "") == $department["department_id"]) ? "selected" : "" ?>
                                >
                                    <?= e($department["name"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nível de administrador</label>
                        <select name="admin_level" class="form-select">
                            <option value="normal" <?= (($administrator["admin_level"] ?? "") === "normal") ? "selected" : "" ?>>Normal</option>
                            <option value="super_admin" <?= (($administrator["admin_level"] ?? "") === "super_admin") ? "selected" : "" ?>>Super Admin</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">Permissões</div>

                <div class="card-body row g-2">
                    <?php foreach ($permissions as $permission): ?>
                        <div class="col-md-4">
                            <label class="form-check">
                                <input 
                                    type="checkbox" 
                                    name="permissions[]" 
                                    value="<?= $permission["permission_id"] ?>"
                                    class="form-check-input"
                                    <?= in_array($permission["permission_id"], $userPermissions) ? "checked" : "" ?>
                                >
                                <span class="form-check-label">
                                    <?= e($permission["permission_name"]) ?>
                                </span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                Guardar
            </button>

            <a href="users.php" class="btn btn-outline-secondary">
                Cancelar
            </a>
        </form>
    </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const roleSelect = document.getElementById("role_id");
    const sections = document.querySelectorAll(".role-section");

    function updateRoleSections() {
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        const selectedRole = selectedOption ? selectedOption.dataset.role : "";

        sections.forEach(section => {
            section.style.display = "none";
        });

        const activeSection = document.querySelector(`[data-role-section="${selectedRole}"]`);
        if (activeSection) {
            activeSection.style.display = "block";
        }
    }

    roleSelect.addEventListener("change", updateRoleSections);
    updateRoleSections();
});
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>