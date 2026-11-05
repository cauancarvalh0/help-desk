<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

exigir_login('../');

$rotulos = rotulos();
$erros = [];
$titulo = '';
$descricao = '';
$categoria = 'problema';
$prioridade = 'media';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $titulo     = trim($_POST['titulo'] ?? '');
    $descricao  = trim($_POST['descricao'] ?? '');
    $categoria  = $_POST['categoria'] ?? '';
    $prioridade = $_POST['prioridade'] ?? '';

    // Validação no servidor
    if ($titulo === '' || mb_strlen($titulo) > 150) {
        $erros[] = 'Informe um título com até 150 caracteres.';
    }
    if ($descricao === '' || mb_strlen($descricao) > 2000) {
        $erros[] = 'Descreva o problema com até 2000 caracteres.';
    }
    if (!array_key_exists($categoria, $rotulos['categoria'])) {
        $erros[] = 'Categoria inválida.';
    }
    if (!array_key_exists($prioridade, $rotulos['prioridade'])) {
        $erros[] = 'Prioridade inválida.';
    }

    if (empty($erros)) {
        try {
            // O user_id vem da sessão, nunca do formulário
            $stmt = $pdo->prepare(
                'INSERT INTO chamados (user_id, titulo, descricao, categoria, prioridade)
                 VALUES (:uid, :titulo, :descricao, :categoria, :prioridade)'
            );
            $stmt->execute([
                ':uid'        => $_SESSION['usuario_id'],
                ':titulo'     => $titulo,
                ':descricao'  => $descricao,
                ':categoria'  => $categoria,
                ':prioridade' => $prioridade,
            ]);

            // lastInsertId() devolve o id do chamado que acabou de ser criado
            $novoId = (int) $pdo->lastInsertId();

            definir_flash('success', 'Chamado aberto com sucesso!');
            redirecionar('view.php?id=' . $novoId);
        } catch (PDOException $e) {
            error_log('Erro ao abrir chamado: ' . $e->getMessage());
            $erros[] = 'Erro ao abrir o chamado. Tente novamente.';
        }
    }
}

$base         = '../';
$tituloPagina = 'Novo chamado';
$heroTitulo   = 'Novo chamado';
$heroSub      = 'Descreva sua solicitação para que possamos ajudar.';
require __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center sobe">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h2 class="h6 mb-0"><i class="bi bi-ticket-detailed me-2"></i>Dados do chamado</h2>
            </div>
            <div class="card-body p-4">

                <?php if ($erros): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($erros as $erro): ?>
                                <li><?= e($erro) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <?= csrf_campo() ?>

                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo"
                               maxlength="150" placeholder="Ex.: Não consigo acessar o sistema" required
                               value="<?= e($titulo) ?>">
                        <div class="invalid-feedback">Informe o título do chamado.</div>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="6"
                                  maxlength="2000" placeholder="Explique o que aconteceu, quando começou e o que você já tentou."
                                  required><?= e($descricao) ?></textarea>
                        <div class="invalid-feedback">Descreva o problema.</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-sm-6">
                            <label for="categoria" class="form-label">Categoria</label>
                            <select class="form-select" id="categoria" name="categoria">
                                <?php foreach ($rotulos['categoria'] as $valor => $texto): ?>
                                    <option value="<?= e($valor) ?>" <?= $categoria === $valor ? 'selected' : '' ?>>
                                        <?= e($texto) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="prioridade" class="form-label">Prioridade</label>
                            <select class="form-select" id="prioridade" name="prioridade">
                                <?php foreach ($rotulos['prioridade'] as $valor => $texto): ?>
                                    <option value="<?= e($valor) ?>" <?= $prioridade === $valor ? 'selected' : '' ?>>
                                        <?= e($texto) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                        <a href="../dashboard.php" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Abrir chamado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
