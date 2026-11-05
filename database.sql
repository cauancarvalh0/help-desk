-- HelpDesk - criação das tabelas
-- Importe com o banco JÁ selecionado (phpMyAdmin > banco > Importar).

-- Usuários: a senha é guardada apenas como hash (password_hash).
-- O perfil começa sempre como 'usuario'; o primeiro atendente é definido manualmente (veja o README).
CREATE TABLE IF NOT EXISTS usuarios (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome         VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL,
    senha_hash   VARCHAR(255) NOT NULL,
    perfil       ENUM('usuario', 'atendente') NOT NULL DEFAULT 'usuario',
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Chamados: user_id é quem abriu; atendente_id é quem assumiu (pode ficar vazio).
CREATE TABLE IF NOT EXISTS chamados (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED NOT NULL,
    atendente_id INT UNSIGNED NULL DEFAULT NULL,
    titulo       VARCHAR(150) NOT NULL,
    descricao    TEXT NOT NULL,
    categoria    ENUM('duvida', 'problema', 'solicitacao') NOT NULL DEFAULT 'problema',
    prioridade   ENUM('baixa', 'media', 'alta') NOT NULL DEFAULT 'media',
    status       ENUM('aberto', 'em_andamento', 'resolvido', 'fechado') NOT NULL DEFAULT 'aberto',
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_chamados_user (user_id),
    KEY idx_chamados_atendente (atendente_id),
    KEY idx_chamados_status (status),
    -- Se o usuário for apagado, os chamados dele também são
    CONSTRAINT fk_chamados_user
        FOREIGN KEY (user_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    -- Se o atendente for apagado, o chamado continua e fica sem atendente
    CONSTRAINT fk_chamados_atendente
        FOREIGN KEY (atendente_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentários: histórico de conversa de cada chamado.
CREATE TABLE IF NOT EXISTS comentarios (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    chamado_id   INT UNSIGNED NOT NULL,
    user_id      INT UNSIGNED NOT NULL,
    mensagem     TEXT NOT NULL,
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_comentarios_chamado (chamado_id),
    CONSTRAINT fk_comentarios_chamado
        FOREIGN KEY (chamado_id) REFERENCES chamados (id) ON DELETE CASCADE,
    CONSTRAINT fk_comentarios_user
        FOREIGN KEY (user_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
