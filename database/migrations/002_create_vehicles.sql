-- 002_create_vehicles.sql
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