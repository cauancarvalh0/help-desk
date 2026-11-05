<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    // Apaga os dados da sessão e encerra o login
    $_SESSION = [];
    session_destroy();

    redirecionar('login.php?saiu=1');
}

redirecionar('login.php');
