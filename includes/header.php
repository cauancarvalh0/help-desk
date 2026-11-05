<?php
/**
 * Topo de todas as páginas: <head>, faixa azul com a navegação e mensagens.
 *
 * Variáveis que cada página pode definir antes de incluir este arquivo:
 *   $tituloPagina - título da aba do navegador
 *   $base         - '' na raiz do site e '../' dentro da pasta tickets/
 *   $heroTitulo   - título grande da faixa azul
 *   $heroSub      - frase abaixo do título
 *   $authLinkUrl / $authLinkTexto - botão do canto direito (login e cadastro)
 */
$tituloPagina  = $tituloPagina ?? 'HelpDesk';
$base          = $base ?? '';
$heroTitulo    = $heroTitulo ?? '';
$heroSub       = $heroSub ?? '';
$authLinkUrl   = $authLinkUrl ?? '';
$authLinkTexto = $authLinkTexto ?? '';
$flash         = obter_flash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tituloPagina) ?> | HelpDesk</title>

    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- O ?v= força o navegador a baixar o CSS de novo quando eu mudar o número -->
    <link href="<?= e($base) ?>assets/css/style.css?v=1" rel="stylesheet">
</head>
<body>

<header class="topo">
    <div class="container">
        <div class="topo-nav">
            <a class="marca" href="<?= e($base) ?>index.php">
                <span class="marca-icone"><i class="bi bi-headset"></i></span>
                HelpDesk
            </a>

            <?php if (usuario_logado()): ?>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-white small d-none d-sm-inline">
                        <i class="bi bi-person-circle me-1"></i><?= e($_SESSION['usuario_nome']) ?>
                        <span class="perfil-selo"><?= eh_atendente() ? 'Atendente' : 'Usuário' ?></span>
                    </span>
                    <!-- O logout é um formulário POST com token CSRF -->
                    <form action="<?= e($base) ?>logout.php" method="POST" class="m-0">
                        <?= csrf_campo() ?>
                        <button type="submit" class="btn btn-light btn-sm">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </button>
                    </form>
                </div>
            <?php elseif ($authLinkUrl !== ''): ?>
                <a class="btn btn-light btn-sm" href="<?= e($authLinkUrl) ?>"><?= e($authLinkTexto) ?></a>
            <?php endif; ?>
        </div>

        <div class="topo-texto">
            <h1><?= e($heroTitulo) ?></h1>
            <p><?= e($heroSub) ?></p>
        </div>

        <?= flash_alerta($flash) ?>
    </div>
</header>

<main class="container conteudo">
