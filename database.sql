-- DataCerta - Banco de Dados v3
-- No phpMyAdmin: entre no banco de dados já existente (crie um vazio antes,
-- se for a primeira vez) e use Importar > selecionar este arquivo.
-- Script único: apaga e recria as tabelas (os dados de exemplo são inseridos
-- de novo). Usa DROP TABLE em vez de DROP DATABASE porque hospedagens
-- compartilhadas (e alguns phpMyAdmin locais) bloqueiam comandos de banco.

SET NAMES utf8mb4;

DROP TABLE IF EXISTS carrinho;
DROP TABLE IF EXISTS itens_pedido;
DROP TABLE IF EXISTS avaliacoes;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS estabelecimentos;
DROP TABLE IF EXISTS clientes;

-- ── TABELAS ────────────────────────────────────────────────

CREATE TABLE clientes (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nome           VARCHAR(100) NOT NULL,
    email          VARCHAR(100) NOT NULL UNIQUE,
    senha          VARCHAR(255) NOT NULL,
    telefone       VARCHAR(20),
    cep            VARCHAR(8),
    rua            VARCHAR(200),
    numero         VARCHAR(10),
    bairro         VARCHAR(100),
    cidade         VARCHAR(100),
    estado         VARCHAR(2),
    lgpd_aceite_em TIMESTAMP NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE estabelecimentos (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nome           VARCHAR(150) NOT NULL,
    email          VARCHAR(100) NOT NULL UNIQUE,
    senha          VARCHAR(255) NOT NULL,
    cnpj           VARCHAR(18),
    telefone       VARCHAR(20),
    whatsapp       VARCHAR(20),
    cep            VARCHAR(8),
    rua            VARCHAR(200),
    numero         VARCHAR(10),
    bairro         VARCHAR(100),
    cidade         VARCHAR(100),
    estado         VARCHAR(2),
    categoria      ENUM('padaria','hortifruti','mercado','acougue','laticinios','frios','mercearia','outros') DEFAULT 'outros',
    chave_pix      VARCHAR(100),
    lgpd_aceite_em TIMESTAMP NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produtos (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    estabelecimento_id  INT NOT NULL,
    nome                VARCHAR(150) NOT NULL,
    descricao           TEXT,
    categoria           VARCHAR(60),
    imagem              VARCHAR(255),
    quantidade          INT NOT NULL DEFAULT 0,
    data_validade       DATE NOT NULL,
    preco_original      DECIMAL(10,2) NOT NULL,
    preco_promocional   DECIMAL(10,2) NOT NULL,
    ativo               TINYINT(1) DEFAULT 1,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estabelecimento_id) REFERENCES estabelecimentos(id) ON DELETE CASCADE
);

CREATE TABLE pedidos (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id         INT NOT NULL,
    estabelecimento_id INT NOT NULL,
    total              DECIMAL(10,2) NOT NULL,
    status             ENUM('pendente','confirmado','entregue','cancelado') DEFAULT 'pendente',
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id)         REFERENCES clientes(id),
    FOREIGN KEY (estabelecimento_id) REFERENCES estabelecimentos(id)
);

CREATE TABLE itens_pedido (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id       INT NOT NULL,
    produto_id      INT NOT NULL,
    quantidade      INT NOT NULL,
    preco_unitario  DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id)  REFERENCES pedidos(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

CREATE TABLE avaliacoes (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id         INT NOT NULL,
    estabelecimento_id INT NOT NULL,
    pedido_id          INT,
    nota               INT NOT NULL,
    comentario         TEXT,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id)         REFERENCES clientes(id),
    FOREIGN KEY (estabelecimento_id) REFERENCES estabelecimentos(id)
);

CREATE TABLE carrinho (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id  INT NOT NULL,
    produto_id  INT NOT NULL,
    quantidade  INT NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id)  REFERENCES clientes(id),
    FOREIGN KEY (produto_id)  REFERENCES produtos(id)
);

-- ── DADOS DE EXEMPLO ───────────────────────────────────────

INSERT INTO estabelecimentos (nome, email, senha, cnpj, telefone, whatsapp, cep, rua, numero, bairro, cidade, estado, categoria, chave_pix, lgpd_aceite_em)
VALUES
('Padaria Central',       'padaria@demo.com',    '$2y$10$VzaAWC7fTtDx3esJIiVjvOnwgVOytfh5jJkkq1MIyYe8nu9oaqlOy', '12.345.678/0001-99', '(11) 99999-1111', '11999991111', '01310000', 'Avenida Paulista',       '1000', 'Bela Vista',   'São Paulo', 'SP', 'padaria',    'padaria@demo.com', NOW()),
('Hortifruti Verde Vida', 'hortifruti@demo.com', '$2y$10$VzaAWC7fTtDx3esJIiVjvOnwgVOytfh5jJkkq1MIyYe8nu9oaqlOy', '98.765.432/0001-11', '(11) 99999-2222', '11999992222', '01421000', 'Rua Augusta',            '500',  'Consolação',   'São Paulo', 'SP', 'hortifruti', NULL, NOW()),
('Mercado Bom Preço',     'mercado@demo.com',    '$2y$10$VzaAWC7fTtDx3esJIiVjvOnwgVOytfh5jJkkq1MIyYe8nu9oaqlOy', '11.222.333/0001-44', '(11) 99999-3333', '11999993333', '04101300', 'Rua Domingos de Morais', '2000', 'Vila Mariana', 'São Paulo', 'SP', 'mercado',    '11.222.333/0001-44', NOW());

INSERT INTO clientes (nome, email, senha, telefone, cep, rua, numero, bairro, cidade, estado, lgpd_aceite_em)
VALUES
('João Silva',   'joao@demo.com',  '$2y$10$VzaAWC7fTtDx3esJIiVjvOnwgVOytfh5jJkkq1MIyYe8nu9oaqlOy', '(11) 98888-1111', '01310100', 'Alameda Santos',   '200', 'Bela Vista', 'São Paulo', 'SP', NOW()),
('Maria Santos', 'maria@demo.com', '$2y$10$VzaAWC7fTtDx3esJIiVjvOnwgVOytfh5jJkkq1MIyYe8nu9oaqlOy', '(11) 98888-2222', '01421001', 'Rua Oscar Freire', '100', 'Consolação', 'São Paulo', 'SP', NOW());

INSERT INTO produtos (estabelecimento_id, nome, descricao, categoria, quantidade, data_validade, preco_original, preco_promocional)
VALUES
(1, 'Pão Francês',         'Pão francês fresquinho, assado hoje', 'padaria',    20, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 0.50,  0.25),
(1, 'Bolo de Chocolate',   'Bolo de chocolate artesanal',         'padaria',     5, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 35.00, 20.00),
(1, 'Croissant de Queijo', 'Croissant recheado com queijo',       'padaria',    10, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 8.00,  4.50),
(2, 'Maçã Gala',           'Maçãs frescas, selecionadas',         'fruta',      30, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 6.00,  3.50),
(2, 'Banana Prata',        'Cacho de banana prata madura',        'fruta',      15, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 4.00,  2.00),
(2, 'Alface Americana',    'Alface crocante',                     'verdura',    20, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 3.50,  1.80),
(3, 'Iogurte Natural',     'Iogurte natural integral 500g',       'laticinios', 12, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 9.00,  5.00),
(3, 'Queijo Mussarela',    'Queijo mussarela fatiado 300g',       'frios',       8, DATE_ADD(CURDATE(), INTERVAL 4 DAY), 18.00, 10.00);

INSERT INTO avaliacoes (cliente_id, estabelecimento_id, nota, comentario)
VALUES
(1, 1, 5, 'Ótima padaria! Produtos frescos e ótimo atendimento.'),
(2, 1, 4, 'Pão muito bom, preço excelente!'),
(1, 2, 5, 'Frutas sempre frescas e baratas.'),
(2, 3, 4, 'Bom mercado, produtos de qualidade.');
