<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

exigir_login('../');

$userId    = (int) $_SESSION['usuario_id'];
$atendente = eh_atendente();
$rotulos   = rotulos();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirecionar('../dashboard.php');
}

$chamado = false;
$comentarios = [];

try {
    // Busca o chamado. Usuário comum só encontra os chamados que ele mesmo abriu.
    $sql = 'SELECT c.id, c.titulo, c.descricao, c.categoria, c.prioridade, c.status,
                   c.atendente_id, c.data_criacao,
                   u.nome AS solicitante, a.nome AS atendente
              FROM chamados c
              JOIN usuarios u ON u.id = c.user_id
              LEFT JOIN usuarios a ON a.id = c.atendente_id
             WHERE c.id = :id';
    $params = [':id' => $id];

    if (!$atendente) {
        $sql .= ' AND c.user_id = :uid';
        $params[':uid'] = $userId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $chamado = $stmt->fetch();

    if ($chamado) {
        // Histórico de comentários, do mais antigo para o mais novo
        $stmt = $pdo->prepare(
            'SELECT m.mensagem, m.data_criacao, u.nome AS autor, u.perfil
               FROM comentarios m
               JOIN usuarios u ON u.id = m.user_id
              WHERE m.chamado_id = :id
              ORDER BY m.data_criacao ASC, m.id ASC'
        );
        $stmt->execute([':id' => $id]);
        $comentarios = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log('Erro ao abrir chamado: ' . $e->getMessage());
    $chamado = false;
}

// Não achou (ou não é seu): mesma mensagem nos dois casos, para não revelar que o chamado existe
if (!$chamado) {
    definir_flash('danger', 'Chamado não encontrado.');
    redirecionar('../dashboard.php');
}

$fechado = $chamado['status'] === 'fechado';

$base         = '../';
$tituloPagina = 'Chamado #' . $chamado['id'];
$heroTitulo   = 'Chamado #' . $chamado['id'];
$heroSub      = $chamado['titulo'];
require __DIR__ . '/../includes/header.php';
?>

<div class="row g-4 align-items-start sobe">

    <!-- Coluna principal: descrição e conversa -->
    <div class="col-12 col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <a href="../dashboard.php" class="link-voltar"><i class="bi bi-arrow-left"></i> Voltar ao painel</a>
                <div class="d-flex flex-wrap gap-2">
                    <span class="selo selo-<?= e($chamado['status']) ?>"><?= e($rotulos['status'][$chamado['status']]) ?></span>
                    <span class="selo selo-<?= e($chamado['prioridade']) ?>">
                        <i class="bi bi-flag-fill"></i> <?= e($rotulos['prioridade'][$chamado['prioridade']]) ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <h2 class="h5"><?= e($chamado['titulo']) ?></h2>
                <p class="mb-0"><?= nl2br(e($chamado['descricao'])) ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="h6 mb-0">
                    <i class="bi bi-chat-dots me-2"></i>Comentários
                    <span class="contador"><?= count($comentarios) ?></span>
                </h2>
            </div>

            <?php if (empty($comentarios)): ?>
                <div class="vazio vazio-pequeno">
                    <i class="bi bi-chat"></i>
                    <p class="text-muted small mb-0">Nenhum comentário ainda.</p>
                </div>
            <?php else: ?>
                <ul class="lista-comentarios">
                    <?php foreach ($comentarios as $m): ?>
                        <li class="comentario <?= $m['perfil'] === 'atendente' ? 'comentario-atendente' : '' ?>">
                            <span class="comentario-avatar">
                                <i class="bi <?= $m['perfil'] === 'atendente' ? 'bi-headset' : 'bi-person-fill' ?>"></i>
                            </span>
                            <div class="comentario-corpo">
                                <div>
                                    <span class="comentario-autor"><?= e($m['autor']) ?></span>
                                    <?php if ($m['perfil'] === 'atendente'): ?>
                                        <span class="perfil-selo perfil-selo-escuro">Atendente</span>
                                    <?php endif; ?>
                                    <span class="comentario-data"><?= date('d/m/Y H:i', strtotime($m['data_criacao'])) ?></span>
                                </div>
                                <div class="comentario-texto"><?= nl2br(e($m['mensagem'])) ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="card-body border-top">
                <?php if ($fechado): ?>
                    <p class="text-muted mb-0">
                        <i class="bi bi-lock me-1"></i>Este chamado está fechado e não aceita novos comentários.
                    </p>
                <?php else: ?>
                    <form action="comment.php" method="POST" class="needs-validation" novalidate>
                        <?= csrf_campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $chamado['id'] ?>">

                        <label for="mensagem" class="form-label">Novo comentário</label>
                        <textarea class="form-control mb-3" id="mensagem" name="mensagem" rows="3"
                                  maxlength="2000" placeholder="Escreva sua mensagem..." required></textarea>
                        <div class="invalid-feedback mb-3">Escreva uma mensagem.</div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Enviar comentário
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Coluna lateral: detalhes e ações do atendente -->
    <div class="col-12 col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h6 mb-0"><i class="bi bi-info-circle me-2"></i>Detalhes</h2>
            </div>
            <div class="card-body">
                <dl class="info-lista mb-0">
                    <div><dt>Solicitante</dt><dd><?= e($chamado['solicitante']) ?></dd></div>
                    <div><dt>Atendente</dt><dd><?= $chamado['atendente'] !== null ? e($chamado['atendente']) : 'Sem atendente' ?></dd></div>
                    <div><dt>Categoria</dt><dd><?= e($rotulos['categoria'][$chamado['categoria']]) ?></dd></div>
                    <div><dt>Prioridade</dt><dd><?= e($rotulos['prioridade'][$chamado['prioridade']]) ?></dd></div>
                    <div><dt>Aberto em</dt><dd><?= date('d/m/Y H:i', strtotime($chamado['data_criacao'])) ?></dd></div>
                </dl>
            </div>
        </div>

        <?php if ($atendente): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="h6 mb-0"><i class="bi bi-gear me-2"></i>Ações do atendente</h2>
                </div>
                <div class="card-body">
                    <!-- Assumir o chamado -->
                    <form action="assign.php" method="POST" class="mb-4">
                        <?= csrf_campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $chamado['id'] ?>">
                        <button type="submit" class="btn btn-outline-primary w-100"
                                <?= (int) $chamado['atendente_id'] === $userId ? 'disabled' : '' ?>>
                            <i class="bi bi-person-check"></i>
                            <?= (int) $chamado['atendente_id'] === $userId ? 'Você já assumiu este chamado' : 'Assumir chamado' ?>
                        </button>
                    </form>

                    <!-- Alterar o status -->
                    <form action="status.php" method="POST">
                        <?= csrf_campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $chamado['id'] ?>">

                        <label for="status" class="form-label">Alterar status</label>
                        <select class="form-select mb-3" id="status" name="status">
                            <?php foreach ($rotulos['status'] as $valor => $texto): ?>
                                <option value="<?= e($valor) ?>" <?= $chamado['status'] === $valor ? 'selected' : '' ?>>
                                    <?= e($texto) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary w-100">Salvar status</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
