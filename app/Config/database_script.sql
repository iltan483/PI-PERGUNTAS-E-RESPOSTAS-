CREATE DATABASE perguntas_db;

CREATE TABLE perguntas (
    id SERIAL PRIMARY KEY,
    texto_pergunta TEXT NOT NULL,
    tipo_pergunta VARCHAR(50) NOT NULL,
    tema VARCHAR(255),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resposta_modelo_id INTEGER, 
    fonte_texto TEXT,
    status_pergunta VARCHAR(50) DEFAULT 'rascunho'
);

CREATE TABLE respostas_modelo (
    id SERIAL PRIMARY KEY,
    pergunta_id INTEGER NOT NULL REFERENCES perguntas(id) ON DELETE CASCADE,
    tipo_modelo VARCHAR(50) NOT NULL,
    conteudo TEXT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE perguntas
ADD CONSTRAINT fk_resposta_modelo
FOREIGN KEY (resposta_modelo_id)
REFERENCES respostas_modelo(id)
ON DELETE SET NULL;

CREATE TABLE opcoes_resposta (
    id SERIAL PRIMARY KEY,
    pergunta_id INTEGER NOT NULL REFERENCES perguntas(id) ON DELETE CASCADE,
    texto_opcao TEXT NOT NULL,
    e_correta BOOLEAN DEFAULT FALSE
);

CREATE TABLE respostas_usuario (
    id SERIAL PRIMARY KEY,
    pergunta_id INTEGER NOT NULL REFERENCES perguntas(id) ON DELETE CASCADE,
    texto_resposta TEXT NOT NULL,
    data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pontuacao NUMERIC(5,2),
    feedback TEXT
);