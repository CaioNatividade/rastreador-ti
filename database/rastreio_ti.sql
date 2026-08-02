-- =====================================================================
-- Rastreador TI - Script de criação do banco de dados
-- Compatível com config/Database.php (host: localhost, db: rastreio_ti)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS rastreio_ti
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE rastreio_ti;

-- ---------------------------------------------------------------------
-- Tabela: usuarios
-- Administradores/Técnicos de TI e Colaboradores
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    senha_hash      VARCHAR(255) NOT NULL,
    perfil          ENUM('admin', 'colaborador') NOT NULL DEFAULT 'colaborador',
    ativo           TINYINT(1) NOT NULL DEFAULT 1,
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela: categorias
-- Ex: Notebooks, Monitores, Periféricos, Servidores
-- ---------------------------------------------------------------------
CREATE TABLE categorias (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(100) NOT NULL UNIQUE,
    descricao       VARCHAR(255) NULL,
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela: equipamentos
-- Cada ativo individual, rastreado por número de série
-- ---------------------------------------------------------------------
CREATE TABLE equipamentos (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome                VARCHAR(150) NOT NULL,
    marca               VARCHAR(100) NULL,
    modelo              VARCHAR(100) NULL,
    numero_serie        VARCHAR(100) NOT NULL UNIQUE,
    categoria_id        INT UNSIGNED NOT NULL,
    status              ENUM('disponivel', 'em_uso', 'manutencao', 'baixado') NOT NULL DEFAULT 'disponivel',
    data_aquisicao      DATE NULL,
    observacoes         TEXT NULL,
    criado_em           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_equipamentos_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela: emprestimos
-- Controle de entrega/devolução de equipamentos a colaboradores
-- ---------------------------------------------------------------------
CREATE TABLE emprestimos (
    id                          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipamento_id              INT UNSIGNED NOT NULL,
    colaborador_id              INT UNSIGNED NOT NULL,
    responsavel_entrega_id      INT UNSIGNED NULL,
    data_entrega                DATETIME NOT NULL,
    data_devolucao_prevista     DATE NULL,
    data_devolucao              DATETIME NULL,
    observacoes_entrega         TEXT NULL,
    observacoes_devolucao       TEXT NULL,
    status                      ENUM('ativo', 'devolvido') NOT NULL DEFAULT 'ativo',
    criado_em                   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_emprestimos_equipamento
        FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_emprestimos_colaborador
        FOREIGN KEY (colaborador_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_emprestimos_responsavel
        FOREIGN KEY (responsavel_entrega_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela: manutencoes
-- Histórico de manutenção de cada equipamento
-- ---------------------------------------------------------------------
CREATE TABLE manutencoes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipamento_id  INT UNSIGNED NOT NULL,
    descricao       TEXT NOT NULL,
    data_inicio     DATE NOT NULL,
    data_fim        DATE NULL,
    custo           DECIMAL(10,2) NULL,
    status          ENUM('em_andamento', 'concluida') NOT NULL DEFAULT 'em_andamento',
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_manutencoes_equipamento
        FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela: termos_responsabilidade
-- Termo gerado/assinado vinculado a um empréstimo
-- ---------------------------------------------------------------------
CREATE TABLE termos_responsabilidade (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    emprestimo_id   INT UNSIGNED NOT NULL,
    caminho_arquivo VARCHAR(255) NULL,
    assinado        TINYINT(1) NOT NULL DEFAULT 0,
    assinado_em     DATETIME NULL,
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_termos_emprestimo
        FOREIGN KEY (emprestimo_id) REFERENCES emprestimos(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Índices auxiliares para consultas frequentes
-- ---------------------------------------------------------------------
CREATE INDEX idx_equipamentos_status ON equipamentos(status);
CREATE INDEX idx_emprestimos_status ON emprestimos(status);
CREATE INDEX idx_emprestimos_colaborador ON emprestimos(colaborador_id);

-- ---------------------------------------------------------------------
-- Dados iniciais (seed) - categorias sugeridas pelo README
-- ---------------------------------------------------------------------
INSERT INTO categorias (nome, descricao) VALUES
    ('Notebooks', 'Notebooks e laptops corporativos'),
    ('Monitores', 'Monitores e telas externas'),
    ('Periféricos', 'Mouses, teclados, headsets, webcams'),
    ('Servidores', 'Servidores físicos e equipamentos de rede');

-- Usuário admin inicial - login: admin@rastreadorti.local | senha: admin123
-- (hash já válido, gerado com bcrypt; troque a senha assim que possível)
INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES
    ('Administrador', 'admin@rastreadorti.local', '$2b$10$9/QyMnwk1jcJRkX2bDHz3O0j6bPVgkPRDuYNIjqxzywLTzOw8RO.2', 'admin');
