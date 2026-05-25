-- 004_create_approvals.sql
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