<?php
class Approval extends Model {

    public function forReservation(int $reservationId): array {
        return $this->query(
            'SELECT a.*, u.full_name AS approver_name
             FROM   approvals a
             JOIN   users u ON u.user_id = a.approved_by
             WHERE  a.reservation_id = ?
             ORDER  BY a.decided_at ASC',
            [$reservationId]
        );
    }

    public function create(array $data): void {
        $this->execute(
            'INSERT INTO approvals
             (reservation_id, approved_by, approval_level, decision, remarks)
             VALUES (?,?,?,?,?)',
            [
                $data['reservation_id'],
                $data['approved_by'],
                $data['approval_level'],
                $data['decision'],
                $data['remarks'] ?? null,
            ]
        );
    }
}