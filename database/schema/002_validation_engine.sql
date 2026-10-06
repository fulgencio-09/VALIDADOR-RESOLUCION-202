-- =============================================================================
-- VALIDADOR RESOLUCION 202
-- Motor parametrizado de variables, reglas y resultados de validacion.
-- Fuente normativa: Resolucion 202 de 2021 y Lineamientos anexo tecnico v8.
-- =============================================================================

CREATE TABLE IF NOT EXISTS res202_variables (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    annex VARCHAR(20) NOT NULL DEFAULT 'ANEXO_1',
    variable_no SMALLINT UNSIGNED NOT NULL,
    variable_name VARCHAR(255) NOT NULL,
    field_length SMALLINT UNSIGNED NOT NULL,
    data_type CHAR(1) NOT NULL,
    allowed_values TEXT NULL,
    allowed_values_usage TEXT NULL,
    validation_text TEXT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    source_version VARCHAR(30) NOT NULL DEFAULT 'v8',
    UNIQUE KEY uq_res202_variable (annex, variable_no),
    INDEX idx_res202_variable_name (variable_name)
);

CREATE TABLE IF NOT EXISTS res202_validation_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    variable_id BIGINT UNSIGNED NULL,
    rule_code VARCHAR(30) NOT NULL,
    rule_description TEXT NOT NULL,
    rule_expression TEXT NULL,
    related_variables VARCHAR(255) NULL,
    severity ENUM('ERROR','WARNING','CRITICAL') NOT NULL DEFAULT 'ERROR',
    auto_correct BOOLEAN NOT NULL DEFAULT FALSE,
    correction_rule TEXT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    source_version VARCHAR(30) NOT NULL DEFAULT 'v8',
    CONSTRAINT fk_res202_rule_variable
        FOREIGN KEY (variable_id) REFERENCES res202_variables(id)
        ON DELETE SET NULL,
    UNIQUE KEY uq_res202_rule_code (rule_code),
    INDEX idx_res202_rule_variable (variable_id)
);

CREATE TABLE IF NOT EXISTS res202_validation_runs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_id BIGINT UNSIGNED NULL,
    annex VARCHAR(20) NOT NULL,
    started_at DATETIME NOT NULL,
    finished_at DATETIME NULL,
    total_records INT UNSIGNED NOT NULL DEFAULT 0,
    total_errors INT UNSIGNED NOT NULL DEFAULT 0,
    total_warnings INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('PENDING','PROCESSING','COMPLETED','FAILED') NOT NULL DEFAULT 'PENDING',
    INDEX idx_res202_run_status (status),
    INDEX idx_res202_run_file (file_id)
);

CREATE TABLE IF NOT EXISTS res202_validation_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    run_id BIGINT UNSIGNED NOT NULL,
    record_number INT UNSIGNED NOT NULL,
    variable_no SMALLINT UNSIGNED NULL,
    rule_code VARCHAR(30) NOT NULL,
    severity ENUM('ERROR','WARNING','CRITICAL') NOT NULL,
    field_value TEXT NULL,
    message TEXT NOT NULL,
    corrected_value TEXT NULL,
    corrected BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_res202_result_run
        FOREIGN KEY (run_id) REFERENCES res202_validation_runs(id)
        ON DELETE CASCADE,
    INDEX idx_res202_result_run_record (run_id, record_number),
    INDEX idx_res202_result_code (rule_code)
);
