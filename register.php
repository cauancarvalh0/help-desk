<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

redirecionar_se_logado();

$erros = [];
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $nome      = trim($_POST['nome'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $senha     = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    // Validação no servidor (a do navegador pode ser ignorada por quem quiser)
    if ($nome === '' || mb_strlen($nome) > 100) {
        $erros[] = 'Informe um nome com até 100 caracteres.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if (strlen($senha) < 8) {
        $erros[] = 'A senha deve ter no mínimo 8 caracteres.';
    }
    if ($senha !== $confirmar) {
        $erros[] = 'As senhas não coincidem.';
    }

    if (empty($erros)) {
        try {
            // Verifica se o e-mail já está cadastrado
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
            $stmt->execute([':email' => $email]);

            if ($stmt->fetch()) {
                $erros[] = 'Este e-mail já está cadastrado.';
            } else {
                // password_hash gera o hash da senha (a senha nunca é salva em texto puro)
                $hash = password_hash($senha, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    'INSERT INTO usuarios (nome, email, senha_hash) VALUES (:nome, :email, :hash)'
                );
                $stmt->execute([
                    ':nome'  => $nome,
                    ':email' => $email,
                    ':hash'  => $hash,
                ]);

                definir_flash('success', 'Cadastro realizado! Faça login para continuar.');
                redirecionar('login.php');
            }
        } catch (PDOException $e) {
            error_log('Erro no cadastro: ' . $e->getMessage());
            $erros[] = 'Erro ao cadastrar. Tente novamente.';
        }
    }
}

$tituloPagina  = 'Criar conta';
$heroTitulo    = 'Crie sua conta';
$heroSub       = 'Leva menos de um minuto. Você poderá abrir chamados e acompanhar o atendimento.';
$authLinkUrl   = 'login.php';
$authLinkTexto = 'Entrar';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center sobe">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card">
            <div class="card-body p-4">
                <h2 class="h4 mb-1">Criar sua conta</h2>
                <p class="text-muted mb-4">Preencha os dados abaixo.</p>

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
                        <label for="nome" class="form-label">Nome</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="nome" name="nome"
                                   maxlength="100" placeholder="Seu nome completo" required autofocus
                                   value="<?= e($nome) ?>">
                            <div class="invalid-feedback">Informe seu nome.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email"
                                   maxlength="150" placeholder="voce@empresa.com" required
                                   value="<?= e($email) ?>">
                            <div class="invalid-feedback">Informe um e-mail válido.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="senha" name="senha"
                                   minlength="8" placeholder="Mínimo de 8 caracteres" required>
                            <button type="button" class="btn btn-outline-secondary btn-toggle-senha"
                                    data-alvo="#senha" aria-label="Mostrar senha">
                                <i class="bi bi-eye"></i>
                            </button>
                            <div class="invalid-feedback">A senha precisa ter no mínimo 8 caracteres.</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirmar_senha" class="form-label">Confirmar senha</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha"
                                   placeholder="Repita a senha" required>
                            <div class="invalid-feedback">As senhas precisam ser iguais.</div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">Criar conta</button>
                </form>
            </div>
        </div>

        <p class="text-center text-muted mt-4">
            Já tem conta? <a href="login.php" class="fw-semibold">Entrar</a>
        </p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
