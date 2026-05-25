-- 005_create_dispatch_logs.sql
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

CREATE TABLE IF NOT EXISTS dispatch_logs (
    log_id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    reservation_id    INT UNSIGNED  NOT NULL,
    driver_id         INT UNSIGNED  NOT NULL,
    vehicle_id        INT UNSIGNED  NOT NULL,
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