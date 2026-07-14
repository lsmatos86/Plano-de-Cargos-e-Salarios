BEGIN;

CREATE TABLE IF NOT EXISTS campanhas_pesquisa (
    "campanhaId" SERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    data_abertura DATE NOT NULL,
    data_fechamento DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'Aberta'
        CHECK (status IN ('Aberta', 'Encerrada', 'Aplicada')),
    observacoes TEXT
);

CREATE TABLE IF NOT EXISTS empresas_mercado (
    "empresaId" SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    setor VARCHAR(100),
    porte VARCHAR(20)
        CHECK (porte IS NULL OR porte IN ('Pequeno', 'Médio', 'Grande', 'Multinacional')),
    data_cadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pesquisa_valores (
    "valorId" SERIAL PRIMARY KEY,
    "campanhaId" INTEGER NOT NULL,
    "empresaId" INTEGER NOT NULL,
    "cboId" INTEGER NOT NULL,
    cargo_nome_mercado VARCHAR(255) NOT NULL,
    salario_base NUMERIC(10,2) NOT NULL CHECK (salario_base >= 0),
    ano_referencia INTEGER,
    salario_original NUMERIC(10,2),
    foi_reajustado SMALLINT NOT NULL DEFAULT 0 CHECK (foi_reajustado IN (0, 1)),
    CONSTRAINT fk_pesquisa_campanha
        FOREIGN KEY ("campanhaId") REFERENCES campanhas_pesquisa ("campanhaId") ON DELETE CASCADE,
    CONSTRAINT fk_pesquisa_empresa
        FOREIGN KEY ("empresaId") REFERENCES empresas_mercado ("empresaId") ON DELETE CASCADE,
    CONSTRAINT fk_pesquisa_cbo
        FOREIGN KEY ("cboId") REFERENCES cbos ("cboId") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_pesquisa_valores_campanha
    ON pesquisa_valores ("campanhaId");
CREATE INDEX IF NOT EXISTS idx_pesquisa_valores_empresa
    ON pesquisa_valores ("empresaId");
CREATE INDEX IF NOT EXISTS idx_pesquisa_valores_cbo
    ON pesquisa_valores ("cboId");

CREATE TABLE IF NOT EXISTS historico_inpc (
    ano INTEGER PRIMARY KEY,
    acumulado_ano NUMERIC(6,2) NOT NULL
);

INSERT INTO historico_inpc (ano, acumulado_ano) VALUES
    (2019, 4.48), (2020, 5.45), (2021, 10.16), (2022, 5.93),
    (2023, 3.71), (2024, 4.50), (2025, 4.00)
ON CONFLICT (ano) DO NOTHING;

CREATE TABLE IF NOT EXISTS historico_salario_minimo (
    id SERIAL PRIMARY KEY,
    data_vigencia DATE NOT NULL UNIQUE,
    valor NUMERIC(10,2) NOT NULL
);

INSERT INTO historico_salario_minimo (data_vigencia, valor) VALUES
    ('2021-01-01', 1100.00), ('2022-01-01', 1212.00),
    ('2023-01-01', 1302.00), ('2023-05-01', 1320.00),
    ('2024-01-01', 1412.00), ('2025-01-01', 1502.00),
    ('2026-01-01', 1621.00)
ON CONFLICT (data_vigencia) DO NOTHING;

COMMIT;
