-- 001_create_users.sql
CREATE TABLE IF NOT EXISTS users (
    user_id       INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    full_name     VARCHAR(100)     NOT NULL,
    email         VARCHAR(100)     NOT NULL,
    username      VARCHAR(50)      NOT NULL,
    password_hash VARCHAR(255)     NOT NULL,
    role          ENUM(
                    'admin',
                                        'staff',
                                        'requester',
                    'driver'
                  ) NOT NULL DEFAULT 'requester',
    department    VARCHAR(100)     NULL,
    contact_no    VARCHAR(20)      NULL,
    is_active     TINYINT(1)       NOT NULL DEFAULT 1,
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id),
    UNIQUE KEY uq_email       (email),
    UNIQUE KEY uq_username    (username),
    INDEX idx_role            (role),
    INDEX idx_department      (department)
) ENGINE=InnoDB;

-- Default Admin (password: admin@USeP2026)
INSERT INTO users
    (full_name, email, username, password_hash, role, department)
VALUES
('System Administrator', 'admin@usep.edu.ph',
 'admin',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'admin', 'Administrative Services Division');