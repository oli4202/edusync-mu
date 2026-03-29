<?php
// pages/fees.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$currentPage = 'fees';

$db->exec("CREATE TABLE IF NOT EXISTS fee_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fee_type VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    semester INT,
    payment_method ENUM('bkash','nrb_bank','cash','other') DEFAULT 'bkash',
    transaction_id VARCHAR(100),
    payment_date DATE NOT NULL,
    status ENUM('paid','pending','waived') DEFAULT 'paid',
    notes VARCHAR(255),
    receipt_ref VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$feeTypes = ['Monthly Tuition'];
$allowedStatuses = ['paid', 'pending', 'waived'];
$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $feeType   = clean($_POST['fee_type'] ?? 'Monthly Tuition');
        $amount    = (float)$_POST['amount'];
        $semester  = (int)($_POST['semester'] ?? $user['semester']);
        $method    = 'bkash';
        $txnId     = clean($_POST['transaction_id'] ?? '');
        $date      = clean($_POST['payment_date']);
        $status    = clean($_POST['status'] ?? 'paid');
        $notes     = clean($_POST['notes'] ?? '');
        if (!in_array($feeType, $feeTypes, true)) {
            $feeType = 'Monthly Tuition';
        }
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'paid';
        }
        if (!$feeType || !$amount || !$date) {
            $err = 'Fill in required fields.';
        } elseif ($amount <= 0) {
            $err = 'Amount must be greater than 0.';
        } else {
            $db->prepare("INSERT INTO fee_payments (user_id,fee_type,amount,semester,payment_method,transaction_id,payment_date,status,notes) VALUES (?,?,?,?,?,?,?,?,?)")
               ->execute([$user['id'],$feeType,$amount,$semester,$method,$txnId,$date,$status,$notes]);
            $msg = 'Payment record added!';
        }
    } elseif ($action === 'delete') {
        $db->prepare("DELETE FROM fee_payments WHERE id=? AND user_id=?")->execute([(int)$_POST['fee_id'],$user['id']]);
        $msg = 'Record deleted.';
    }
}

$fees = $db->prepare("SELECT * FROM fee_payments WHERE user_id=? ORDER BY payment_date DESC");
$fees->execute([$user['id']]);
$payments = $fees->fetchAll();

$totalPaid    = array_sum(array_map(fn($p)=>$p['status']==='paid'?$p['amount']:0, $payments));
$totalPending = array_sum(array_map(fn($p)=>$p['status']==='pending'?$p['amount']:0, $payments));
$byCat = [];
foreach ($payments as $p) {
    $byCat[$p['fee_type']] = ($byCat[$p['fee_type']] ?? 0) + $p['amount'];
}

$statusColors = ['paid'=>'var(--accent3)','pending'=>'var(--warn)','waived'=>'var(--accent2)'];
$methodIcons  = ['bkash'=>'bKash'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fee Payment Tracker - EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.bkash-card{background:linear-gradient(135deg,#E2136E,#b5105a);border-radius:16px;padding:24px;color:#fff;position:relative;overflow:hidden;margin-bottom:20px;}
.bkash-card::before{content:'';position:absolute;top:-30px;right:-30px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.08);}
.bkash-card::after{content:'';position:absolute;bottom:-20px;right:60px;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,.05);}
.bkash-logo{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;letter-spacing:-0.5px;margin-bottom:4px;}
.bkash-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:16px;}
.bkash-step{background:rgba(255,255,255,.12);border-radius:10px;padding:12px;text-align:center;font-size:12px;}
.bkash-step .num{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;margin-bottom:4px;}
.bank-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:20px;}
.bank-detail{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);font-size:14px;}
.bank-detail:last-child{border-bottom:none;}
.bank-label{color:var(--muted);font-size:13px;}
.bank-value{font-weight:600;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:999;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px;width:100%;max-width:480px;animation:fadeUp .3s ease;max-height:90vh;overflow-y:auto;}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.modal-title{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:20px;}
.field{margin-bottom:14px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.copy-btn{background:rgba(34,211,238,.1);border:1px solid rgba(34,211,238,.2);color:var(--accent);font-size:11px;padding:6px 10px;border-radius:6px;cursor:pointer;border-style:solid;}
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">Fee Payment Tracker</div>
            <div class="page-sub">Track monthly tuition payments with bKash only</div>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')">+ Add Payment</button>
    </div>

    <?php if ($msg): ?><div class="alert-success"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert-error"><?= $err ?></div><?php endif; ?>

    <div class="grid-4" style="margin-bottom:24px;">
        <div class="stat-card green"><div class="stat-value"><?= number_format($totalPaid,0) ?></div><div class="stat-label">Total Paid</div></div>
        <div class="stat-card yellow"><div class="stat-value"><?= number_format($totalPending,0) ?></div><div class="stat-label">Pending</div></div>
        <div class="stat-card cyan"><div class="stat-value"><?= count($payments) ?></div><div class="stat-label">Payment Records</div></div>
        <div class="stat-card purple"><div class="stat-value">Sem <?= $user['semester'] ?></div><div class="stat-label">Current Semester</div></div>
    </div>

    <div class="grid-2" style="margin-bottom:24px;">
        <div>
            <div class="bkash-card">
                <div class="bkash-logo">bKash</div>
                <div style="font-size:13px;opacity:.85;margin-bottom:4px;">Metropolitan University Sylhet - Fee Payment</div>
                <div style="font-size:11px;opacity:.8;">Use bKash Education Bill for Metropolitan University or open the official biller page directly.</div>
                <div class="bkash-steps">
                    <div class="bkash-step"><div class="num">1</div>Open bKash App</div>
                    <div class="bkash-step"><div class="num">2</div>Choose Education Bill</div>
                    <div class="bkash-step"><div class="num">3</div>Find Metropolitan University</div>
                    <div class="bkash-step"><div class="num">4</div>Save TxnID</div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;">
                    <a href="https://www.bkash.com/en" target="_blank" class="btn btn-outline btn-sm" style="background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.2);color:#fff;">Visit bKash Website</a>
                    <a href="https://www.bkash.com/en/products-services/education/billers/01757535844" target="_blank" class="btn btn-outline btn-sm" style="background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.2);color:#fff;">Open bKash App Link</a>
                </div>
                <div style="margin-top:12px;font-size:12px;line-height:1.6;word-break:break-all;">
                    Website: <a href="https://www.bkash.com/en" target="_blank" style="color:#fff;text-decoration:underline;">https://www.bkash.com/en</a>
                </div>
            </div>

        </div>

        <div class="card">
            <div class="card-title">Monthly Tuition Summary</div>
            <?php if (empty($byCat)): ?>
            <div style="text-align:center;padding:24px;color:var(--muted);">No payments logged yet.</div>
            <?php else: ?>
            <?php $maxAmt = max(array_values($byCat)); ?>
            <?php foreach ($byCat as $type => $amt): ?>
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:5px;">
                    <span><?= htmlspecialchars($type) ?></span>
                    <strong><?= number_format($amt,0) ?></strong>
                </div>
                <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
                    <div style="height:100%;border-radius:3px;background:linear-gradient(90deg,var(--accent),var(--accent2));width:<?= round($amt/$maxAmt*100) ?>%;transition:width .5s;"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-title">All Payment Records</div>
        <?php if (empty($payments)): ?>
        <div style="text-align:center;padding:32px;color:var(--muted);">
            No payments logged yet.
            <div style="margin-top:10px;"><button onclick="document.getElementById('addModal').classList.add('open')" class="btn btn-primary btn-sm">+ Add First Payment</button></div>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Date</th><th>Fee Type</th><th>Amount</th><th>Semester</th><th>Method</th><th>Transaction ID</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td style="color:var(--muted);font-size:13px;"><?= date('M j, Y',strtotime($p['payment_date'])) ?></td>
                <td><strong><?= htmlspecialchars($p['fee_type']) ?></strong><?php if($p['notes']): ?><div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($p['notes']) ?></div><?php endif; ?></td>
                <td><strong style="color:var(--accent3)"><?= number_format($p['amount'],2) ?></strong></td>
                <td style="text-align:center;"><?= $p['semester'] ?: '-' ?></td>
                <td><?= $methodIcons[$p['payment_method']] ?? 'bKash' ?></td>
                <td style="font-family:monospace;font-size:12px;color:var(--muted)"><?= htmlspecialchars($p['transaction_id'] ?: '-') ?></td>
                <td><span style="color:<?= $statusColors[$p['status']] ?? 'var(--muted)' ?>;font-weight:600;font-size:13px;">● <?= ucfirst($p['status']) ?></span></td>
                <td>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="fee_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</main>

<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-title">Log Fee Payment</div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-row">
                <div class="field">
                    <label>Fee Type *</label>
                    <select name="fee_type" required>
                        <?php foreach ($feeTypes as $ft): ?>
                        <option value="<?= $ft ?>"><?= $ft ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Amount *</label>
                    <input type="number" name="amount" step="0.01" placeholder="e.g. 15000" required>
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Payment Method</label>
                    <input type="text" value="bKash" readonly>
                    <input type="hidden" name="payment_method" value="bkash">
                </div>
                <div class="field">
                    <label>Payment Date *</label>
                    <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="field" id="txnField">
                <label>bKash Transaction ID</label>
                <input type="text" name="transaction_id" placeholder="e.g. 8N6A3KQDEF">
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Semester</label>
                    <select name="semester">
                        <?php for($i=1;$i<=8;$i++): ?>
                        <option value="<?= $i ?>" <?= $user['semester']==$i?'selected':'' ?>>Semester <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status">
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="waived">Waived</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Notes (optional)</label>
                <input type="text" name="notes" placeholder="e.g. March 2026 monthly tuition payment">
            </div>
            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="btn btn-primary">Save Payment</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('addModal').addEventListener('click',function(e){if(e.target===this)this.classList.remove('open')});
</script>
</body>
</html>
