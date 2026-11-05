<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Só atendente altera o status (conferido no servidor)
exigir_perfil('atendente', '../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../dashboard.php');
}
csrf_validar();

$rotulos = rotulos();
$id      = (int) ($_POST['id'] ?? 0);
$status  = $_POST['status'] ?? '';

// Só aceita um dos status permitidos
if ($id <= 0 || !array_key_exists($status, $rotulos['status'])) {
    definir_flash('danger', 'Dados inválidos.');
    redirecionar('../dashboard.php');
}

try {
    $stmt = $pdo->prepare('UPDATE chamados SET status = :status WHERE id = :id');
    $stmt->execute([':status' => $status, ':id' => $id]);

    // rowCount() diz quantas linhas foram alteradas
    if ($stmt->rowCount() > 0) {
        definir_flash('success', 'Status atualizado.');
    } else {
        definir_flash('info', 'Nenhuma alteração feita.');
    }
} catch (PDOException $e) {
    error_log('Erro ao alterar status: ' . $e->getMessage());
    definir_flash('danger', 'Erro ao alterar o status.');
}

redirecionar('view.php?id=' . $id);
