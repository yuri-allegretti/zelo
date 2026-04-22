CREATE DATABASE IF NOT EXISTS zelo;

USE zelo;

CREATE TABLE cadastro (
  id         INT(11)                            NOT NULL AUTO_INCREMENT,
  nome       VARCHAR(255)                       NOT NULL,
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