CREATE DATABASE IF NOT EXISTS usep_vrs
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE usep_vrs;

-- ── 1. Users ────────────────────────────────────────────────────────
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

-- ── 2. Vehicles ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS vehicles (
    vehicle_id    INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    plate_number  VARCHAR(15)      NOT NULL,
    make_model    VARCHAR(100)     NOT NULL,
    vehicle_type  VARCHAR(50)      NULL,
    capacity      TINYINT UNSIGNED NOT NULL DEFAULT 1,
    year          YEAR             NULL,
    color         VARCHAR(40)      NULL,
        assigned_driver_id INT UNSIGNED NULL,
    status        ENUM(
                    'available',
                    'in_use',
                    'maintenance',
                    'retired'
                  ) NOT NULL DEFAULT 'available',
    notes         TEXT             NULL,
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (vehicle_id),
    UNIQUE KEY uq_plate   (plate_number),
    INDEX idx_status      (status),
    INDEX idx_assigned_driver (assigned_driver_id)
) ENGINE=InnoDB;

-- ── 3. Reservations ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reservations (
    reservation_id   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    reference_no     VARCHAR(20)   NOT NULL,
    requester_id     INT UNSIGNED  NOT NULL,
    vehicle_id       INT UNSIGNED  NULL,
    purpose          TEXT          NOT NULL,
    destination      VARCHAR(255)  NOT NULL,
    passengers       TINYINT UNSIGNED NOT NULL DEFAULT 1,
    departure_date   DATE          NOT NULL,
    departure_time   TIME          NOT NULL,
    return_date      DATE          NOT NULL,
    return_time      TIME          NOT NULL,
    status           ENUM(
                       'pending',
                                             'approved',
                       'dispatched',
                       'completed',
                       'rejected',
                       'cancelled'
                     ) NOT NULL DEFAULT 'pending',
        requester_remarks TEXT         NULL,
    remarks          TEXT          NULL,
    requested_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (reservation_id),
    UNIQUE KEY uq_reference_no (reference_no),
    INDEX idx_status           (status),
    INDEX idx_requester        (requester_id),
    INDEX idx_departure_date   (departure_date),

    CONSTRAINT fk_res_requester FOREIGN KEY (requester_id)
        REFERENCES users (user_id) ON DELETE CASCADE,

    CONSTRAINT fk_res_vehicle   FOREIGN KEY (vehicle_id)
        REFERENCES vehicles (vehicle_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── 4. Approvals ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS approvals (
    approval_id      INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    reservation_id   INT UNSIGNED  NOT NULL,
    approved_by      INT UNSIGNED  NOT NULL,
    approval_level   ENUM('staff') NOT NULL,
    decision         ENUM('approved', 'rejected')         NOT NULL,
    remarks          TEXT          NULL,
    decided_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (approval_id),
    INDEX idx_reservation (reservation_id),
    INDEX idx_approver    (approved_by),

    CONSTRAINT fk_appr_reservation FOREIGN KEY (reservation_id)
        REFERENCES reservations (reservation_id) ON DELETE CASCADE,

    CONSTRAINT fk_appr_user         FOREIGN KEY (approved_by)
        REFERENCES users        (user_id)        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ── 5. Drivers ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS drivers (
    driver_id      INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id        INT UNSIGNED  NOT NULL,
    is_available   TINYINT(1)    NOT NULL DEFAULT 1,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (driver_id),
    UNIQUE KEY uq_user_id    (user_id),

    CONSTRAINT fk_drv_user FOREIGN KEY (user_id)
        REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── 6. Dispatch Logs ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS dispatch_logs (
    log_id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    reservation_id    INT UNSIGNED  NOT NULL,
    driver_id         INT UNSIGNED  NOT NULL,
    vehicle_id        INT UNSIGNED  NOT NULL,
    actual_passengers TINYINT UNSIGNED NULL,
    start_mileage     DECIMAL(10,2) NULL     DEFAULT 0.00,
    end_mileage       DECIMAL(10,2) NULL,
    fuel_consumed     DECIMAL(8,2)  NULL,
    actual_departure  DATETIME      NULL,
    actual_return     DATETIME      NULL,
    trip_notes        TEXT          NULL,
    logged_at         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (log_id),
    UNIQUE KEY uq_dispatch_res (reservation_id),
    INDEX idx_driver           (driver_id),
    INDEX idx_vehicle          (vehicle_id),

    CONSTRAINT fk_log_reservation FOREIGN KEY (reservation_id)
        REFERENCES reservations (reservation_id) ON DELETE CASCADE,

    CONSTRAINT fk_log_driver      FOREIGN KEY (driver_id)
        REFERENCES drivers      (driver_id)      ON DELETE RESTRICT,

    CONSTRAINT fk_log_vehicle     FOREIGN KEY (vehicle_id)
        REFERENCES vehicles     (vehicle_id)     ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ── 7. Audit Logs ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_logs (
    audit_id   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    table_name VARCHAR(64)     NOT NULL,
    record_id  BIGINT UNSIGNED NULL,
    action     ENUM('INSERT','UPDATE','DELETE') NOT NULL,
    changed_by INT UNSIGNED    NULL,
    old_data   JSON            NULL,
    new_data   JSON            NULL,
    changed_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (audit_id),
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_changed_by (changed_by)
) ENGINE=InnoDB;

-- ── 8. Views ───────────────────────────────────────────────────────
CREATE OR REPLACE VIEW vw_reservation_summary AS
SELECT r.reservation_id,
       r.reference_no,
       r.status,
       r.departure_date,
       r.return_date,
       u.full_name AS requester_name,
       u.department,
       v.make_model,
       v.plate_number,
       dl.driver_id,
       dl.actual_departure,
       dl.actual_return
FROM   reservations r
JOIN   users u ON u.user_id = r.requester_id
LEFT JOIN vehicles v ON v.vehicle_id = r.vehicle_id
LEFT JOIN dispatch_logs dl ON dl.reservation_id = r.reservation_id;

-- ── 9. Additional Indexes ─────────────────────────────────────────
SET @idx := (
        SELECT COUNT(*)
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
            AND table_name = 'reservations'
            AND index_name = 'idx_res_status_date'
);
SET @sql := IF(@idx > 0, 'DROP INDEX idx_res_status_date ON reservations', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
CREATE INDEX idx_res_status_date ON reservations (status, departure_date);

-- ── 10. Stored Function ────────────────────────────────────────────
DELIMITER //

DROP FUNCTION IF EXISTS fn_make_reference_no//
CREATE FUNCTION fn_make_reference_no()
RETURNS VARCHAR(20)
NOT DETERMINISTIC
NO SQL
BEGIN
    RETURN CONCAT(
        'VRS-',
        DATE_FORMAT(NOW(), '%Y%m%d'),
        '-',
        UPPER(SUBSTRING(REPLACE(UUID(), '-', ''), 1, 6))
    );
END//

DELIMITER ;

-- ── 11. Stored Procedure ───────────────────────────────────────────
DELIMITER //
DROP PROCEDURE IF EXISTS sp_create_reservation//
CREATE PROCEDURE sp_create_reservation(
    IN  p_requester_id   INT UNSIGNED,
    IN  p_purpose         TEXT,
    IN  p_destination     VARCHAR(255),
    IN  p_passengers      TINYINT UNSIGNED,
    IN  p_departure_date  DATE,
    IN  p_departure_time  TIME,
    IN  p_return_date     DATE,
    IN  p_return_time     TIME,
    IN  p_vehicle_id      INT UNSIGNED,
    IN  p_requester_remarks TEXT,
    OUT o_reservation_id  INT UNSIGNED,
    OUT o_reference_no    VARCHAR(20)
)
BEGIN
    SET o_reference_no = fn_make_reference_no();

    INSERT INTO reservations
        (reference_no, requester_id, purpose, destination, passengers,
         departure_date, departure_time, return_date, return_time, vehicle_id, requester_remarks, status)
    VALUES
        (o_reference_no, p_requester_id, p_purpose, p_destination, p_passengers,
         p_departure_date, p_departure_time, p_return_date, p_return_time, p_vehicle_id, p_requester_remarks, 'pending');

    SET o_reservation_id = LAST_INSERT_ID();
END//
DELIMITER ;

-- ── 12. Triggers (Audit) ───────────────────────────────────────────
DELIMITER //
DROP TRIGGER IF EXISTS trg_reservation_status_audit//
CREATE TRIGGER trg_reservation_status_audit
AFTER UPDATE ON reservations
FOR EACH ROW
BEGIN
    IF OLD.status <> NEW.status THEN
        INSERT INTO audit_logs
            (table_name, record_id, action, changed_by, old_data, new_data)
        VALUES
            ('reservations', NEW.reservation_id, 'UPDATE', @app_user_id,
             JSON_OBJECT('status', OLD.status, 'remarks', OLD.remarks),
             JSON_OBJECT('status', NEW.status, 'remarks', NEW.remarks));
    END IF;
END//
DELIMITER ;

DELIMITER //
DROP TRIGGER IF EXISTS trg_approval_audit//
CREATE TRIGGER trg_approval_audit
AFTER INSERT ON approvals
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs
        (table_name, record_id, action, changed_by, new_data)
    VALUES
        ('approvals', NEW.approval_id, 'INSERT', NEW.approved_by,
         JSON_OBJECT('reservation_id', NEW.reservation_id, 'decision', NEW.decision));
END//
DELIMITER ;

-- ── 13. Event ─────────────────────────────────────────────────────
DELIMITER //
DROP EVENT IF EXISTS ev_auto_cancel_past_pending//
CREATE EVENT ev_auto_cancel_past_pending
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP + INTERVAL 1 DAY
DO
BEGIN
    UPDATE reservations
    SET status = 'cancelled',
        remarks = 'Auto-cancelled: departure date elapsed.'
    WHERE status = 'pending'
      AND departure_date < CURDATE();
END//
DELIMITER ;

--  Default accounts and sample fleet
-- Passwords are bcrypt hashes — regenerate with: php -r "echo password_hash('pw', PASSWORD_BCRYPT);"

INSERT INTO users
    (full_name, email, username, password_hash, role, department, contact_no)
VALUES
-- Administrator  (password: admin@USeP2026)
('System Administrator', 'admin@usep.edu.ph',
 'admin',
 '$2y$10$1HUxPEzHlzg/vFU3xSfqAOjZdA.irdReUT8fHcMdr8W.8AbhJOj9u',
 'admin', 'Administrative Services Division', '082-227-8192'),

-- Staff  (password: password123)
('Maria Clara Reyes', 'mclara.reyes@usep.edu.ph',
 'staff',
 '$2y$10$YarHdN2Q5BHpsXhTK2ae/.mceB.hPAV3Q8k9eEZ7hkjvIQ6jIrsZW',
 'staff', 'Administrative Services Division', '09171234567'),

-- Requester  (password: password123)
('Ana Marie Gonzales', 'ana.gonzales@usep.edu.ph',
 'requester',
 '$2y$10$YarHdN2Q5BHpsXhTK2ae/.mceB.hPAV3Q8k9eEZ7hkjvIQ6jIrsZW',
 'requester', 'Registrar Office', '09391234567'),

-- Driver  (password: password123)
('Juan Dela Cruz', 'juan.delacruz@usep.edu.ph',
 'driver',
 '$2y$10$YarHdN2Q5BHpsXhTK2ae/.mceB.hPAV3Q8k9eEZ7hkjvIQ6jIrsZW',
 'driver', 'Motorpool', '09501234567');

INSERT INTO drivers (user_id, is_available)
    SELECT user_id, 1
    FROM   users WHERE username = 'driver';

-- Sample vehicle fleet
INSERT INTO vehicles (plate_number, make_model, vehicle_type, capacity, year, color, status) VALUES
('USeP-0001', 'Toyota HiAce Grandia',   'Van',     15, 2022, 'Silver',  'available'),
('USeP-0002', 'Toyota Coaster',          'Bus',     25, 2021, 'White',   'available'),
('USeP-0003', 'Mitsubishi L300 FB',      'Van',     14, 2020, 'White',   'available'),
('USeP-0004', 'Toyota Fortuner',         'SUV',      7, 2023, 'Black',   'available'),
('USeP-0005', 'Toyota Innova',           'Van',      7, 2022, 'Pearl',   'maintenance');