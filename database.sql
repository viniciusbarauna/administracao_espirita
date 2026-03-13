CREATE DATABASE lar_espirita_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lar_espirita_db;

-- Tabela de Administradores
CREATE TABLE administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL, -- Nunca salvar senha em texto puro
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Mensalistas
CREATE TABLE mensalistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    data_nascimento DATE NOT NULL,
    data_ingresso DATE NOT NULL, -- Tempo de casa
    telefone VARCHAR(20) NOT NULL,
    is_whatsapp BOOLEAN DEFAULT 0,
    email VARCHAR(150),
    periodicidade ENUM('Mensal', 'Bimestral', 'Trimestral', 'Semestral', 'Anual') NOT NULL,
    proximo_vencimento DATE NOT NULL,
    status ENUM('Ativo', 'Inativo') DEFAULT 'Ativo',
    consentimento_msg BOOLEAN DEFAULT 1, -- LGPD: Aceite de recebimento de msg
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Pagamentos (Auditoria Financeira)
CREATE TABLE pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensalista_id INT NOT NULL,
    admin_id INT NOT NULL, -- Quem registrou (Auditoria)
    valor DECIMAL(10, 2) NOT NULL,
    data_pagamento DATETIME DEFAULT CURRENT_TIMESTAMP,
    meio_pagamento VARCHAR(50) NOT NULL, -- PIX, Dinheiro, Cartão
    referencia_periodo VARCHAR(50),
    FOREIGN KEY (mensalista_id) REFERENCES mensalistas(id),
    FOREIGN KEY (admin_id) REFERENCES administradores(id)
);

-- Tabela de Templates de Mensagem
CREATE TABLE templates_mensagem (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    texto TEXT NOT NULL,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir um Admin Padrão (Senha: admin123) - APENAS PARA TESTE INICIAL
INSERT INTO administradores (nome, email, senha_hash) VALUES 
('Admin Mestre', 'admin@lar.com', '$2y$10$e.vV4T.M7n/W7W.');