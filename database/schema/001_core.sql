-- Esquema inicial del dominio del Validador Resolución 202.
-- La estructura definitiva se ajustará después de normalizar la matriz oficial.

CREATE TABLE variables (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resolution VARCHAR(50) NOT NULL,
    annex VARCHAR(100) NOT NULL,
    variable_number INT NOT NULL,
    variable_name VARCHAR(255) NOT NULL,
    data_type VARCHAR(30) NULL,
    field_length INT NULL,
    required BOOLEAN NOT NULL DEFAULT FALSE,
    format_pattern VARCHAR(255) NULL,
    allowed_values JSON NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    version VARCHAR(50) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_variable_version (resolution, annex, variable_number, version)
);

CREATE TABLE validation_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    variable_id BIGINT UNSIGNED NULL,
    rule_code VARCHAR(50) NOT NULL,
    rule_name VARCHAR(255) NOT NULL,
    rule_type VARCHAR(50) NOT NULL,
    expression TEXT NOT NULL,
    severity ENUM('ERROR', 'WARNING') NOT NULL DEFAULT 'ERROR',
    auto_correct BOOLEAN NOT NULL DEFAULT FALSE,
    correction_expression TEXT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    version VARCHAR(50) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_rules_variable FOREIGN KEY (variable_id) REFERENCES variables(id),
    UNIQUE KEY uq_rule_version (rule_code, version)
);

CREATE TABLE validation_errors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    validation_rule_id BIGINT UNSIGNED NOT NULL,
    record_number BIGINT UNSIGNED NULL,
    variable_number INT NULL,
    value_received TEXT NULL,
    message TEXT NOT NULL,
    severity ENUM('ERROR', 'WARNING') NOT NULL,
    created_at TIMESTAMP NULL,
    CONSTRAINT fk_error_rule FOREIGN KEY (validation_rule_id) REFERENCES validation_rules(id)
);
