<?php

session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("User not authenticated.");
}

$userId = $_SESSION['user_id'];

try {

    $sql = "
        SELECT
            r.reservation_id,
            r.start_datetime,
            r.end_datetime,
            r.created_at,
            rs.status_name,
            re.name AS resource_name,
            re.code AS resource_code,
            rt.type_name

        FROM reservations r

        INNER JOIN reservation_status rs
            ON r.status_id = rs.status_id

        INNER JOIN resources re
            ON r.resource_id = re.resource_id

        INNER JOIN resource_types rt
            ON re.resource_type_id = rt.resource_type_id

        WHERE r.user_id = :user_id

        ORDER BY r.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(
        ':user_id',
        $userId,
        PDO::PARAM_INT
    );

    $stmt->execute();

    $reservations = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>As minhas reservas</h2>

        <a href="../dashboard.php"
           class="btn btn-secondary">
            Voltar
        </a>

    </div>

<?php 
    if (isset($_GET['success']) && %_GET['success'] === 'cancelled'): ?>
        <div class="alert alert-success">
            Reserva cancelada com sucesso.
        </div>
    <?php endif; ?>
    

    <?php if (count($reservations) > 0): ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Recurso</th>
                        <th>Tipo</th>
                        <th>Iniico</th>
                        <th>Fim</th>
                        <th>Status</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($reservations as $reservation): ?>

                        <tr>
                            <td>
                                <strong>
                                    <?= htmlspecialchars($reservation['resource_name']) ?>
                                </strong>
                                <br>
                                <small>
                                    <?= htmlspecialchars($reservation['resource_code']) ?>
                                </small>
                            </td>

                            <td>
                                <?= htmlspecialchars($reservation['type_name']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($reservation['start_datetime']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($reservation['end_datetime']) ?>
                            </td>

                            <td>
                                <?php if ($reservation['status_name'] === 'active'): ?>
                                    <span class="badge bg-success">
                                        Ativo
                                    </span>
                                <?php elseif ($reservation['status_name'] === 'cancelled'): ?>
                                    <span class="badge bg-danger">
                                        Cancelado
                                    </span>
                                <?php elseif ($reservation['status_name'] === 'completed'): ?>
                                    <span class="badge bg-secondary">
                                        Completo
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">
                                        <?= htmlspecialchars($reservation['status_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($reservation['created_at']) ?>
                            </td>

                            <td>
                                // Apenas reservas ativas podem ser canceladas
                                <?php if ($reservation['status_name'] === 'active'): ?>
                                    <form
                                        action="../actions/cancel_reservation.php"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Tem certeza que deseja cancelar esta reserva?');"
                                >
                                    <input
                                        type="hidden"
                                        name="reservation_id"
                                        value="<?= $reservation['reservation_id'] ?>"
                                    >
                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm"
                                    >
                                        Cancelar
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span class="text-muted">
                                        —
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            Nenehuma reserva encontrada.
        </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
