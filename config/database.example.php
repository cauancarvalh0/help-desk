<?php
/**
 * EXEMPLO de configuração da conexão PDO.
 *
 * Copie este arquivo para config/database.php e preencha com os dados
 * do seu banco. O config/database.php real NUNCA deve ir para o GitHub
 * (ele já está no .gitignore).
 */

$host    = 'localhost';          // InfinityFree: algo como sqlXXX.infinityfree.com
$banco   = 'db_chamados';        // nome do banco criado por você
$usuario = 'root';               // usuário do banco
$senha   = '';                   // senha do banco
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$banco};charset={$charset}";

$opcoes = [
    // Lança exceções em caso de erro (permite usar try/catch)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Retorna os resultados como arrays associativos
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Usa prepared statements reais do MySQL (proteção contra SQL Injection)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $usuario, $senha, $opcoes);
} catch (PDOException $e) {
    // O detalhe do erro vai para o log do servidor, nunca para o usuário
    error_log('Erro de conexão: ' . $e->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados. Tente novamente mais tarde.');
}
