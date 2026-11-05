<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

exigir_login();

$userId    = (int) $_SESSION['usuario_id'];
$atendente = eh_atendente();
$rotulos   = rotulos();

// Filtros pela URL: só aceita os valores permitidos
$filtroStatus = $_GET['status'] ?? 'todos';
if (!in_array($filtroStatus, ['todos', 'aberto', 'em_andamento', 'resolvido', 'fechado'], true)) {
    $filtroStatus = 'todos';
}

$filtroPrioridade = $_GET['prioridade'] ?? 'todas';
if (!in_array($filtroPrioridade, ['todas', 'alta', 'media', 'baixa'], true)) {
    $filtroPrioridade = 'todas';
}

/** Monta o endereço do painel mantendo os dois filtros */
function url_painel(string $status, string $prioridade): string
{
    return 'dashboard.php?status=' . $status . '&prioridade=' . $prioridade;
}

$chamados = [];
$totais = ['aberto' => 0, 'em_andamento' => 0, 'resolvido' => 0, 'fechado' => 0];
$erro = null;

try {
    // Monta o WHERE aos poucos. Usuário comum só enxerga os chamados que ele abriu.
    $condicoes = [];
    $params = [];

    if (!$atendente) {
        $condicoes[] = 'c.user_id = :uid';
        $params[':uid'] = $userId;
    }
    if ($filtroStatus !== 'todos') {
        $condicoes[] = 'c.status = :status';
        $params[':status'] = $filtroStatus;
    }
    if ($filtroPrioridade !== 'todas') {
        $condicoes[] = 'c.prioridade = :prioridade';
        $params[':prioridade'] = $filtroPrioridade;
    }

    $where = !empty($condicoes) ? 'WHERE ' . implode(' AND ', $condicoes) : '';

    // JOIN traz o nome de quem abriu; LEFT JOIN traz o atendente (que pode ainda não existir)
    $sql = "SELECT c.id, c.titulo, c.categoria, c.prioridade, c.status, c.data_criacao,
                   u.nome AS solicitante, a.nome AS atendente
              FROM chamados c
              JOIN usuarios u ON u.id = c.user_id
              LEFT JOIN usuarios a ON a.id = c.atendente_id
              $where
             ORDER BY CASE c.status WHEN 'aberto' THEN 1 WHEN 'em_andamento' THEN 2 WHEN 'resolvido' THEN 3 ELSE 4 END,
                      CASE c.prioridade WHEN 'alta' THEN 1 WHEN 'media' THEN 2 ELSE 3 END,
                      c.data_criacao DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $chamados = $stmt->fetchAll();

    // Quantos chamados existem em cada status (cartões do topo; não dependem dos filtros)
    if ($atendente) {
        $stmt = $pdo->prepare('SELECT status, COUNT(*) AS total FROM chamados GROUP BY status');
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare('SELECT status, COUNT(*) AS total FROM chamados WHERE user_id = :uid GROUP BY status');
        $stmt->execute([':uid' => $userId]);
    }
    foreach ($stmt->fetchAll() as $linha) {
        $totais[$linha['status']] = (int) $linha['total'];
    }
} catch (PDOException $e) {
    error_log('Erro ao listar chamados: ' . $e->getMessage());
    $erro = 'Não foi possível carregar os chamados.';
}

$total = array_sum($totais);
$primeiroNome = explode(' ', trim($_SESSION['usuario_nome']))[0];

$tituloPagina = 'Painel';
$heroTitulo   = 'Olá, ' . $primeiroNome;
$heroSub      = $atendente ? 'Visão geral de todos os chamados.' : 'Acompanhe os chamados que você abriu.';
require __DIR__ . '/includes/header.php';
?>

<?php if ($erro): ?>
    <div class="alert alert-danger"><?= e($erro) ?></div>
<?php endif; ?>

<!-- Cartões de resumo -->
<section class="row g-3 mb-4 sobe" aria-label="Resumo">
    <div class="col-6 col-lg-3">
        <div class="card estatistica">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="estatistica-rotulo">Abertos</div>
                        <div class="estatistica-valor"><?= $totais['aberto'] ?></div>
                    </div>
                    <span class="icone-estatistica fundo-azul"><i class="bi bi-inbox"></i></span>
                </div>
                <p class="estatistica-nota">Aguardando atendimento</p>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card estatistica">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="estatistica-rotulo">Em andamento</div>
                        <div class="estatistica-valor"><?= $totais['em_andamento'] ?></div>
                    </div>
                    <span class="icone-estatistica fundo-laranja"><i class="bi bi-hourglass-split"></i></span>
                </div>
                <p class="estatistica-nota">Sendo atendidos</p>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card estatistica">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="estatistica-rotulo">Resolvidos</div>
                        <div class="estatistica-valor"><?= $totais['resolvido'] ?></div>
                    </div>
                    <span class="icone-estatistica fundo-verde"><i class="bi bi-check2-circle"></i></span>
                </div>
                <p class="estatistica-nota">Solução entregue</p>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card estatistica">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="estatistica-rotulo">Total</div>
                        <div class="estatistica-valor"><?= $total ?></div>
                    </div>
                    <span class="icone-estatistica fundo-ciano"><i class="bi bi-collection"></i></span>
                </div>
                <p class="estatistica-nota">Todos os chamados</p>
            </div>
        </div>
    </div>
</section>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h2 class="h6 mb-0">
            <i class="bi bi-card-checklist me-2"></i><?= $atendente ? 'Todos os chamados' : 'Meus chamados' ?>
        </h2>
        <a href="tickets/create.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Novo chamado
        </a>
    </div>

    <!-- Filtros (pílulas do Bootstrap) -->
    <div class="barra-filtros">
        <div class="grupo-filtro">
            <span class="grupo-filtro-rotulo">Status</span>
            <ul class="nav nav-pills filtros">
                <li class="nav-item">
                    <a class="nav-link <?= $filtroStatus === 'todos' ? 'active' : '' ?>"
                       href="<?= e(url_painel('todos', $filtroPrioridade)) ?>">Todos</a>
                </li>
                <?php foreach ($rotulos['status'] as $valor => $texto): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $filtroStatus === $valor ? 'active' : '' ?>"
                           href="<?= e(url_painel($valor, $filtroPrioridade)) ?>">
                            <?= e($texto) ?> <span class="contador"><?= $totais[$valor] ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="grupo-filtro">
            <span class="grupo-filtro-rotulo">Prioridade</span>
            <ul class="nav nav-pills filtros">
                <li class="nav-item">
                    <a class="nav-link <?= $filtroPrioridade === 'todas' ? 'active' : '' ?>"
                       href="<?= e(url_painel($filtroStatus, 'todas')) ?>">Todas</a>
                </li>
                <?php foreach ($rotulos['prioridade'] as $valor => $texto): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $filtroPrioridade === $valor ? 'active' : '' ?>"
                           href="<?= e(url_painel($filtroStatus, $valor)) ?>"><?= e($texto) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <?php if (empty($chamados)): ?>
        <div class="vazio">
            <i class="bi bi-inbox"></i>
            <p class="fw-semibold mb-1">Nenhum chamado encontrado</p>
            <p class="text-muted small mb-0">
                <?= ($filtroStatus === 'todos' && $filtroPrioridade === 'todas')
                    ? 'Clique em "Novo chamado" para abrir o primeiro.'
                    : 'Nenhum chamado com esses filtros.' ?>
            </p>
        </div>
    <?php else: ?>
        <ul class="lista-chamados">
            <?php foreach ($chamados as $c): ?>
                <li class="chamado">
                    <span class="chamado-icone"><i class="bi bi-ticket-detailed"></i></span>

                    <div class="chamado-corpo">
                        <a class="chamado-titulo" href="tickets/view.php?id=<?= (int) $c['id'] ?>">
                            #<?= (int) $c['id'] ?> &middot; <?= e($c['titulo']) ?>
                        </a>

                        <div class="chamado-info">
                            <span class="selo selo-<?= e($c['status']) ?>"><?= e($rotulos['status'][$c['status']]) ?></span>
                            <span class="selo selo-<?= e($c['prioridade']) ?>">
                                <i class="bi bi-flag-fill"></i> <?= e($rotulos['prioridade'][$c['prioridade']]) ?>
                            </span>
                            <span class="selo selo-categoria"><?= e($rotulos['categoria'][$c['categoria']]) ?></span>
                        </div>

                        <div class="chamado-meta">
                            <?php if ($atendente): ?>
                                <span><i class="bi bi-person"></i> <?= e($c['solicitante']) ?></span>
                            <?php endif; ?>
                            <span>
                                <i class="bi bi-headset"></i>
                                <?= $c['atendente'] !== null ? e($c['atendente']) : 'Sem atendente' ?>
                            </span>
                            <span><i class="bi bi-calendar3"></i> <?= date('d/m/Y H:i', strtotime($c['data_criacao'])) ?></span>
                        </div>
                    </div>

                    <a href="tickets/view.php?id=<?= (int) $c['id'] ?>" class="botao-icone"
                       aria-label="Abrir chamado <?= (int) $c['id'] ?>" title="Abrir">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
