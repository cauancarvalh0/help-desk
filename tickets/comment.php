<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

exigir_login('../');

// Esta página só aceita o envio do formulário (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../dashboard.php');
}
csrf_validar();

$userId   = (int) $_SESSION['usuario_id'];
$id       = (int) ($_POST['id'] ?? 0);
$mensagem = trim($_POST['mensagem'] ?? '');

if ($id <= 0) {
    redirecionar('../dashboard.php');
}

if ($mensagem === '' || mb_strlen($mensagem) > 2000) {
    definir_flash('danger', 'Escreva uma mensagem com até 2000 caracteres.');
    redirecionar('view.php?id=' . $id);
}

try {
    // Confere se o chamado existe e se o usuário pode comentar nele (dono ou atendente)
    $sql = 'SELECT id, status FROM chamados WHERE id = :id';
    $params = [':id' => $id];

    if (!eh_atendente()) {
        $sql .= ' AND user_id = :uid';
        $params[':uid'] = $userId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $chamado = $stmt->fetch();

    if (!$chamado) {
        definir_flash('danger', 'Chamado não encontrado.');
        redirecionar('../dashboard.php');
    }

    if ($chamado['status'] === 'fechado') {
        definir_flash('warning', 'Este chamado está fechado e não aceita comentários.');
        redirecionar('view.php?id=' . $id);
    }

    // O autor do comentário vem da sessão, nunca do formulário
    $stmt = $pdo->prepare('INSERT INTO comentarios (chamado_id, user_id, mensagem) VALUES (:cid, :uid, :mensagem)');
    $stmt->execute([':cid' => $id, ':uid' => $userId, ':mensagem' => $mensagem]);

    definir_flash('success', 'Comentário enviado.');
} catch (PDOException $e) {
    error_log('Erro ao comentar: ' . $e->getMessage());
    definir_flash('danger', 'Erro ao enviar o comentário.');
}

redirecionar('view.php?id=' . $id);
