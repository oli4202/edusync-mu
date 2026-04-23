<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Fee;
use App\Models\User;
use function App\redirect;

class FeeController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $payments = Fee::findByUser($userId);
        
        $totalPaid = array_sum(array_map(fn($p) => $p['status'] === 'paid' ? $p['amount'] : 0, $payments));
        $totalPending = array_sum(array_map(fn($p) => $p['status'] === 'pending' ? $p['amount'] : 0, $payments));
        
        $byCat = [];
        foreach ($payments as $p) {
            $byCat[$p['fee_type']] = ($byCat[$p['fee_type']] ?? 0) + $p['amount'];
        }

        $feeTypes = ['Monthly Tuition', 'Admission Fee', 'Semester Fee', 'Lab Fee', 'Library Fee', 'Transport Fee', 'Exam Fee', 'Retake Fee', 'Convocation Fee', 'ID Card Fee', 'Other'];
        $statusColors = ['paid' => 'var(--accent3)', 'pending' => 'var(--warn)', 'waived' => 'var(--accent2)'];
        $methodIcons = ['bkash' => '📱 bKash', 'nrbc_bank' => '🏦 NRBC Bank', 'cash' => '💵 Cash', 'other' => '💳 Other'];
        $latestPayment = $payments[0] ?? null;

        $this->render('pages/fees', compact(
            'user', 'payments', 'totalPaid', 'totalPending', 'byCat', 
            'feeTypes', 'statusColors', 'methodIcons', 'latestPayment'
        ));
    }

    public function add(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Fee::create($this->session->userId(), $_POST);
        }
        redirect('/fees');
    }

    public function delete(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Fee::delete((int)$_POST['fee_id'], $this->session->userId());
        }
        redirect('/fees');
    }
}
