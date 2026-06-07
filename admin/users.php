<?php

include __DIR__ . "/../includes/auth_check.php"; //verifica se o utilizador está autenticado
include __DIR__ . "/../includes/role_check.php"; //verifica as permissões
requireRole(["administrator"]); //permite acesso apenas a administradores

require_once __DIR__ . "/../config/db.php"; //conexão à bd

$pageTitle = "Gestão de Utilizadores - PCU"; //título da página
$basePath = "../"; //caminho base

$q = trim($_GET["q"] ?? ""); //texto da pesquisa
$roleFilter = $_GET["role_id"] ?? ""; //filtro por tipo de utilizador

$where = [];
$params = [];

if ($q !== "") //se existir texto de pesquisa
{
    $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.document_number LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($roleFilter !== "") //se existir filtro de tipo
{
    $where[] = "u.role_id = ?";
    $params[] = $roleFilter;
}

$whereSql = "";

if (!empty($where)) //monta as condições da consulta
{
    $whereSql = "WHERE " . implode(" AND ", $where);
}

$sql = "
    SELECT 
        u.user_id,
        u.name,
        u.email,
        u.phone_number,
        u.status,
        u.created_at,
        r.role_name,

        COALESCE(
            sp.student_number,
            pp.professor_number,
            fp.funcionario_number,
            ap.administrator_number
        ) AS profile_number

    FROM users u
    INNER JOIN roles r ON u.role_id = r.role_id
    LEFT JOIN student_profiles sp ON u.user_id = sp.user_id
    LEFT JOIN professor_profiles pp ON u.user_id = pp.user_id
    LEFT JOIN funcionario_profiles fp ON u.user_id = fp.user_id
    LEFT JOIN administrator_profiles ap ON u.user_id = ap.user_id
    $whereSql
    ORDER BY u.created_at DESC
";

$stmt = $pdo->prepare($sql); //prepara a consulta
$stmt->execute($params); //executa a consulta
$users = $stmt->fetchAll(); //obtém os utilizadores encontrados

//obtém todos os tipos de utilizador
$roles = $pdo->query("
    SELECT role_id, role_name
    FROM roles
    ORDER BY role_name
")->fetchAll();

include __DIR__ . "/../includes/header.php"; //inclui o cabeçalho
?>

<div class="app-layout">

    <?php include __DIR__ . "/../includes/sidebar.php"; //inclui o menu lateral ?>

    <main class="main-content">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2>
                    Gestão de Utilizadores
                </h2>

                <p class="text-muted">
                    Criar, editar e desativar utilizadores do sistema.
                </p>

            </div>

            <a href="user_form.php" class="btn btn-primary">
                Novo Utilizador
            </a>

        </div>

        <?php if (isset($_GET["success"])): //mensagem de sucesso ?>

            <div class="alert alert-success">
                Operação realizada com sucesso.
            </div>

        <?php endif; ?>

        <?php if (isset($_GET["error"])): //mensagem de erro ?>

            <div class="alert alert-danger">
                Ocorreu um erro ao realizar a operação.
            </div>

        <?php endif; ?>

        <div class="card shadow-sm mb-4">

            <div class="card-body">

                <form method="GET" class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label">
                            Pesquisar
                        </label>

                        <input
                            type="text"
                            name="q"
                            class="form-control"
                            placeholder="Nome, email ou documento"
                            value="<?= htmlspecialchars($q) ?>"
                        >

                    </div>

                    <div class="col-md-4">

                        <label class="form-label">
                            Tipo de utilizador
                        </label>

                        <select name="role_id" class="form-select">

                            <option value="">
                                Todos
                            </option>

                            <?php foreach ($roles as $role): //lista todos os tipos ?>

                                <option
                                    value="<?= $role["role_id"] ?>"
                                    <?= ($roleFilter == $role["role_id"]) ? "selected" : "" ?>
                                >
                                    <?= htmlspecialchars($role["role_name"]) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-2 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-dark w-100"
                        >
                            Filtrar
                        </button>

                    </div>

                </form>

            </div>

        </div>

        <div class="card shadow-sm">

            <div class="card-body table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Número</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th class="text-end">Ações</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (empty($users)): //se não existirem utilizadores ?>

                            <tr>

                                <td colspan="7" class="text-center text-muted">
                                    Nenhum utilizador encontrado.
                                </td>

                            </tr>

                        <?php endif; ?>

                        <?php foreach ($users as $user): //percorre todos os utilizadores ?>

                            <tr>

                                <td>
                                    <?= $user["user_id"] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($user["profile_number"] ?? "-") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($user["name"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($user["email"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($user["role_name"]) ?>
                                </td>

                                <td>

                                    <?php if ($user["status"] === "active"): //se estiver ativo ?>

                                        <span class="badge bg-success">
                                            Ativo
                                        </span>

                                    <?php else: //se estiver inativo ?>

                                        <span class="badge bg-secondary">
                                            Inativo
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td class="text-end">

                                    <a
                                        href="user_form.php?id=<?= $user["user_id"] ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Editar
                                    </a>

                                    <?php if ($user["status"] === "active"): //permite desativar apenas utilizadores ativos ?>

                                        <form
                                            action="../actions/register_user_action.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirmAction('Tem a certeza que pretende desativar este utilizador?');"
                                        >

                                            <!-- ação a executar -->
                                            <input
                                                type="hidden"
                                                name="mode"
                                                value="deactivate"
                                            >

                                            <!-- id do utilizador -->
                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= $user["user_id"] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                            >
                                                Desativar
                                            </button>

                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . "/../includes/footer.php"; //inclui o rodapé ?>