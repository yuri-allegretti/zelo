CREATE DATABASE IF NOT EXISTS zelo;

USE zelo;

CREATE TABLE cadastro (
  id         INT(11)                            NOT NULL AUTO_INCREMENT,
  nome       VARCHAR(255)                       NOT NULL,
  sobrenome  VARCHAR(255)                       NOT NULL,
  email      VARCHAR(255)                       NOT NULL,
  senha      VARCHAR(255)                       NOT NULL,
  data_nasc  DATE                               NOT NULL,
  nivel      ENUM('free', 'premium', 'suporte') NOT NULL DEFAULT 'free',
  item_id    VARCHAR(255),
  account_id VARCHAR(255),
  saldo      DECIMAL(15,2)                      DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE banco (
  Numero    INT(12)                             NOT NULL,
  Agencia   INT(5)                              NOT NULL,
  Banco     VARCHAR(255)                        NOT NULL,
  Nome      VARCHAR(255)                        NOT NULL,
  Cpf       VARCHAR(11)                         NOT NULL,
  PRIMARY KEY (Numero)
);

CREATE TABLE transactions (
    id VARCHAR(255) PRIMARY KEY,
    user_id INT NOT NULL,
    account_id VARCHAR(255),
    amount FLOAT,
    description TEXT,
    date DATETIME,
    categoria VARCHAR(50),
    categoria_editada VARCHAR(50),

    FOREIGN KEY (user_id) REFERENCES cadastro(id)
);

CREATE TABLE tickets (
  id              INT(11)                                    NOT NULL AUTO_INCREMENT,
  user_id         INT(11)                                    NOT NULL,
  agente_id       INT(11)                                    DEFAULT NULL,
  titulo          VARCHAR(255)                               NOT NULL,
  descricao       TEXT                                       NOT NULL,
  status          ENUM('aberta', 'em_andamento', 'fechada') NOT NULL DEFAULT 'aberta',
  prioridade      ENUM('baixa', 'media', 'alta')            NOT NULL DEFAULT 'media',
  categoria       VARCHAR(100)                               DEFAULT NULL,
  data_abertura   DATETIME                                   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_fechamento DATETIME                                   DEFAULT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id)   REFERENCES cadastro(id),
  FOREIGN KEY (agente_id) REFERENCES cadastro(id)
);

CREATE TABLE solicitacao_suporte (
  id               INT(11)                                NOT NULL AUTO_INCREMENT,
  user_id          INT(11)                                NOT NULL,
  nome_completo    VARCHAR(255)                           NOT NULL,
  idade            INT(3)                                 NOT NULL,
  motivacao        TEXT                                   NOT NULL,
  experiencia      TEXT                                   DEFAULT NULL,
  status           ENUM('pendente', 'aprovada', 'negada') NOT NULL DEFAULT 'pendente',
  admin_id         INT(11)                                DEFAULT NULL,
  observacao_admin TEXT                                   DEFAULT NULL,
  novo_login_id    INT(11)                                DEFAULT NULL,
  data_solicitacao DATETIME                               NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_resposta    DATETIME                               DEFAULT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id)       REFERENCES cadastro(id),
  FOREIGN KEY (admin_id)      REFERENCES cadastro(id),
  FOREIGN KEY (novo_login_id) REFERENCES cadastro(id)
);