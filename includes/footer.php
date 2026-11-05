<?php
/**
 * Final de todas as páginas: fecha o <main>, rodapé e scripts.
 * Usa a variável $base ('' na raiz e '../' dentro de tickets/).
 */
$base = $base ?? '';
?>
</main>

<footer class="rodape">
    <div class="container">
        &copy; <?= date('Y') ?> HelpDesk &middot; PHP, MySQL e Bootstrap 5
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e($base) ?>assets/js/script.js?v=1"></script>
</body>
</html>
