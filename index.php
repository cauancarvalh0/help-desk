<?php
require_once __DIR__ . '/includes/auth.php';

// A página inicial só decide para onde enviar o visitante
redirecionar(usuario_logado() ? 'dashboard.php' : 'login.php');
