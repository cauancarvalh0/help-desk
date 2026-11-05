<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Só atendente assume chamados (conferido no servidor)
exigir_perfil('atendente', '../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../dashboard.php');
}
csrf_validar();

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    redirecionar('../dashboard.php');
}

try {
    // O atendente vem da sessão. Se o chamado estava aberto, passa para "em andamento".
    $stmt = $pdo->prepare(
        "UPDATE chamados
            SET atendente_id = :uid,
                status = CASE WHEN status = 'aberto' THEN 'em_andamento' ELSE status END
          WHERE id = :id"
    );
    $stmt->execute([':uid' => $_SESSION['usuario_id'], ':id' => $id]);

    if ($stmt->rowCount() > 0) {
        definir_flash('success', 'Você assumiu este chamado.');
    } else {
        definir_flash('info', 'Nenhuma alteração feita.');
    }
} catch (PDOException $e) {
    error_log('Erro ao assumir chamado: ' . $e->getMessage());
    definir_flash('danger', 'Erro ao assumir o chamado.');
}

redirecionar('view.php?id=' . $id);
