-- 003_create_reservations.sql
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