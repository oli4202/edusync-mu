<?php

namespace App\Models;

use function App\getDB;
use function App\clean;

/**
 * Fee Model
 */
class Fee
{
    public static function findByUser(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM fee_payments WHERE user_id=? ORDER BY payment_date DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, array $data): bool
    {
        $db = getDB();
        $feeType = clean($data['fee_type'] ?? 'Monthly Tuition');
        $amount = (float)$data['amount'];
        $semester = (int)($data['semester'] ?? 0);
        $method = clean($data['payment_method'] ?? 'bkash');
        $txnId = clean($data['transaction_id'] ?? '');
        $date = clean($data['payment_date']);
        $status = clean($data['status'] ?? 'paid');
        $notes = clean($data['notes'] ?? '');

        $stmt = $db->prepare("INSERT INTO fee_payments (user_id, fee_type, amount, semester, payment_method, transaction_id, payment_date, status, notes) VALUES (?,?,?,?,?,?,?,?,?)");
        return $stmt->execute([$userId, $feeType, $amount, $semester, $method, $txnId, $date, $status, $notes]);
    }

    public static function delete(int $id, int $userId): bool
    {
        $db = getDB();
        return $db->prepare("DELETE FROM fee_payments WHERE id=? AND user_id=?")->execute([$id, $userId]);
    }
}
