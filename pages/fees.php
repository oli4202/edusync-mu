<?php
// LEGACY FILE - REDIRECT TO MVC
header('Location: /fees');
exit;

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

$feeTypes = ['Monthly Tuition', 'Admission Fee', 'Semester Fee', 'Lab Fee', 'Library Fee', 'Transport Fee', 'Exam Fee', 'Retake Fee', 'Convocation Fee', 'ID Card Fee', 'Other'];
$allowedStatuses = ['paid', 'pending', 'waived'];
$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $feeType   = clean($_POST['fee_type'] ?? 'Monthly Tuition');
        $amount    = (float)$_POST['amount'];
        $semester  = (int)($_POST['semester'] ?? $user['semester']);
        $method    = clean($_POST['payment_method'] ?? 'bkash');
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
$methodIcons  = ['bkash'=>'📱 bKash','nrbc_bank'=>'🏦 NRBC Bank','cash'=>'💵 Cash','other'=>'💳 Other'];
$latestPayment = $payments[0] ?? null;
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
.payment-layout{display:grid;grid-template-columns:1.2fr .8fr;gap:20px;margin-bottom:24px;}
.bkash-card{background:
    radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 34%),
    radial-gradient(circle at 18% 18%, rgba(255,255,255,.1), transparent 18%),
    linear-gradient(135deg,#E2136E 0%,#c01160 45%,#96104d 100%);
    border-radius:24px;padding:26px;color:#fff;position:relative;overflow:hidden;margin-bottom:20px;box-shadow:0 26px 50px rgba(226,19,110,.22);}
.bkash-card::before{content:'';position:absolute;inset:auto -54px -54px auto;width:190px;height:190px;border-radius:50%;background:rgba(255,255,255,.08);}
.bkash-card::after{content:'';position:absolute;top:22px;right:22px;width:92px;height:92px;border-radius:28px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06);backdrop-filter:blur(4px);}
.bkash-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;position:relative;z-index:1;}
.bkash-brand{display:flex;align-items:center;gap:14px;}
.bkash-mark{width:62px;height:62px;border-radius:20px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);position:relative;box-shadow:inset 0 1px 0 rgba(255,255,255,.18);}
.bkash-mark::before,.bkash-mark::after{content:'';position:absolute;background:#fff;border-radius:999px;}
.bkash-mark::before{width:14px;height:34px;left:22px;top:12px;transform:rotate(28deg);}
.bkash-mark::after{width:28px;height:12px;left:16px;top:28px;transform:rotate(-24deg);}
.bkash-brand-copy{min-width:0;}
.bkash-logo{font-family:'Syne',sans-serif;font-size:30px;font-weight:800;letter-spacing:-1px;line-height:1;}
.bkash-subtitle{font-size:13px;opacity:.86;margin-top:6px;max-width:440px;line-height:1.5;}
.bkash-badge{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;}
.bkash-meta{position:relative;z-index:1;display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:18px;}
.bkash-meta-card{padding:14px;border-radius:16px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.12);}
.bkash-meta-label{font-size:11px;opacity:.72;text-transform:uppercase;letter-spacing:.12em;}
.bkash-meta-value{font-family:'Syne',sans-serif;font-size:20px;font-weight:700;margin-top:6px;}
.bkash-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:18px;position:relative;z-index:1;}
.bkash-step{background:rgba(255,255,255,.12);border-radius:16px;padding:14px 12px;min-height:110px;font-size:12px;line-height:1.45;border:1px solid rgba(255,255,255,.14);}
.bkash-step .num{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:12px;font-family:'Syne',sans-serif;font-size:17px;font-weight:800;margin-bottom:10px;background:rgba(10,14,26,.18);}
.bkash-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:18px;position:relative;z-index:1;}
.bkash-btn{background:rgba(255,255,255,.12)!important;border-color:rgba(255,255,255,.22)!important;color:#fff!important;}
.bkash-btn:hover{background:rgba(255,255,255,.18)!important;border-color:rgba(255,255,255,.3)!important;color:#fff!important;}
.payment-summary-card{background:linear-gradient(180deg,rgba(17,24,39,.98),rgba(9,14,26,.96));border:1px solid rgba(129,140,248,.18);border-radius:24px;padding:22px;box-shadow:0 18px 40px rgba(0,0,0,.24);}
.summary-kicker{font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#7dd3fc;margin-bottom:10px;font-weight:700;}
.summary-head{display:flex;align-items:flex-end;justify-content:space-between;gap:10px;margin-bottom:18px;}
.summary-amount{font-family:'Syne',sans-serif;font-size:34px;font-weight:800;line-height:1;}
.summary-note{color:var(--muted);font-size:13px;line-height:1.5;}
.summary-list{display:grid;gap:12px;margin-top:18px;}
.summary-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-radius:16px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);}
.summary-item-label{font-size:12px;color:var(--muted);}
.summary-item-value{font-weight:700;color:var(--text);}
.summary-pill{display:inline-flex;align-items:center;gap:8px;padding:7px 10px;border-radius:999px;background:rgba(52,211,153,.1);color:var(--accent3);font-size:11px;font-weight:700;}
.summary-empty{padding:26px 18px;border-radius:18px;background:rgba(255,255,255,.03);border:1px dashed rgba(255,255,255,.1);text-align:center;color:var(--muted);font-size:14px;line-height:1.6;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:999;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px;width:100%;max-width:480px;animation:fadeUp .3s ease;max-height:90vh;overflow-y:auto;}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.modal-title{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:20px;}
.field{margin-bottom:14px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.copy-btn{background:rgba(34,211,238,.1);border:1px solid rgba(34,211,238,.2);color:var(--accent);font-size:11px;padding:6px 10px;border-radius:6px;cursor:pointer;border-style:solid;}
@media (max-width: 1100px){
    .payment-layout{grid-template-columns:1fr;}
}
@media (max-width: 700px){
    .bkash-header{flex-direction:column;align-items:flex-start;}
    .bkash-meta{grid-template-columns:1fr;}
    .bkash-steps{grid-template-columns:1fr 1fr;}
}
@media (max-width: 520px){
    .bkash-brand{align-items:flex-start;}
    .bkash-logo{font-size:26px;}
    .bkash-steps{grid-template-columns:1fr;}
}
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">Fee Payment Tracker</div>
            <div class="page-sub">Track tuition payments via bKash and NRBC Bank for Metropolitan University</div>
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

    <div class="payment-layout">
        <div>
            <div class="bkash-card">
                <div class="bkash-header">
                    <div class="bkash-brand">
                        <div class="bkash-mark" aria-hidden="true"></div>
                        <div class="bkash-brand-copy">
                            <div class="bkash-logo">bKash</div>
                            <div style="font-size:13px;opacity:.9;margin-top:5px;">Metropolitan University Sylhet fee payment</div>
                            <div class="bkash-subtitle">Pay from the bKash app, then log your transaction here so semester payment history stays organized inside EduSync.</div>
                        </div>
                    </div>
                    <div class="bkash-badge">Education Bill Active</div>
                </div>
                <div class="bkash-meta">
                    <div class="bkash-meta-card">
                        <div class="bkash-meta-label">Current Semester</div>
                        <div class="bkash-meta-value">Sem <?= (int) $user['semester'] ?></div>
                    </div>
                    <div class="bkash-meta-card">
                        <div class="bkash-meta-label">Records Logged</div>
                        <div class="bkash-meta-value"><?= count($payments) ?></div>
                    </div>
                    <div class="bkash-meta-card">
                        <div class="bkash-meta-label">Paid So Far</div>
                        <div class="bkash-meta-value"><?= number_format($totalPaid,0) ?> BDT</div>
                    </div>
                </div>
                <div class="bkash-steps">
                    <div class="bkash-step"><div class="num">1</div>Open your bKash app and go to the education bill section.</div>
                    <div class="bkash-step"><div class="num">2</div>Select Metropolitan University from the available billers.</div>
                    <div class="bkash-step"><div class="num">3</div>Complete the tuition payment for your current semester.</div>
                    <div class="bkash-step"><div class="num">4</div>Copy the transaction ID and store it here with the amount.</div>
                </div>
                <div class="bkash-actions">
                    <a href="https://www.bkash.com/en/products-services/education/billers/01757535844" target="_blank" class="btn btn-outline btn-sm bkash-btn">Open Education Bill Link</a>
                    <button type="button" class="btn btn-outline btn-sm bkash-btn" onclick="document.getElementById('addModal').classList.add('open')">Log Payment Now</button>
                </div>
            </div>

            <!-- NRBC Bank Card -->
            <div style="background:linear-gradient(135deg,#1a3a5c 0%,#0d2137 45%,#0a1929 100%);border-radius:24px;padding:26px;color:#fff;position:relative;overflow:hidden;box-shadow:0 26px 50px rgba(13,33,55,.22);">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
                    <div style="width:62px;height:62px;border-radius:20px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-size:26px;font-weight:800;">🏦</div>
                    <div>
                        <div style="font-family:'Syne',sans-serif;font-size:24px;font-weight:800;">NRBC Bank</div>
                        <div style="font-size:13px;opacity:.85;margin-top:4px;">Metropolitan University Sylhet — Bank Payment</div>
                    </div>
                </div>
                <div style="font-size:14px;line-height:1.7;opacity:.9;margin-bottom:16px;">
                    Pay tuition fees through NRBC Bank branches or online banking. Metropolitan University has a dedicated collection account with NRBC Bank.
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                    <div style="background:rgba(255,255,255,.08);border-radius:12px;padding:12px;border:1px solid rgba(255,255,255,.1);">
                        <div style="font-size:11px;opacity:.6;text-transform:uppercase;letter-spacing:.1em;">Bank Name</div>
                        <div style="font-weight:700;margin-top:4px;">NRBC Bank Limited</div>
                    </div>
                    <div style="background:rgba(255,255,255,.08);border-radius:12px;padding:12px;border:1px solid rgba(255,255,255,.1);">
                        <div style="font-size:11px;opacity:.6;text-transform:uppercase;letter-spacing:.1em;">Branch</div>
                        <div style="font-weight:700;margin-top:4px;">Sylhet Branch</div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;font-size:12px;">
                    <div style="background:rgba(255,255,255,.08);border-radius:10px;padding:10px;text-align:center;border:1px solid rgba(255,255,255,.08);">
                        <div style="font-size:11px;opacity:.6;">Step 1</div>
                        <div style="margin-top:4px;">Visit NRBC Bank or use online banking</div>
                    </div>
                    <div style="background:rgba(255,255,255,.08);border-radius:10px;padding:10px;text-align:center;border:1px solid rgba(255,255,255,.08);">
                        <div style="font-size:11px;opacity:.6;">Step 2</div>
                        <div style="margin-top:4px;">Pay to MU collection account</div>
                    </div>
                    <div style="background:rgba(255,255,255,.08);border-radius:10px;padding:10px;text-align:center;border:1px solid rgba(255,255,255,.08);">
                        <div style="font-size:11px;opacity:.6;">Step 3</div>
                        <div style="margin-top:4px;">Save receipt and log here</div>
                    </div>
                </div>
                <div style="margin-top:16px;">
                    <button type="button" class="btn btn-outline btn-sm" style="background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.22);color:#fff;" onclick="document.getElementById('addModal').classList.add('open')">Log Bank Payment</button>
                </div>
            </div>

        </div>

        <div class="payment-summary-card">
            <div class="summary-kicker">Payment Snapshot</div>
            <div class="summary-head">
                <div>
                    <div class="summary-amount"><?= number_format($totalPaid,0) ?> BDT</div>
                    <div class="summary-note">Recorded tuition payments across your logged history.</div>
                </div>
                <?php if ($latestPayment && $latestPayment['status'] === 'paid'): ?>
                <div class="summary-pill">Latest payment cleared</div>
                <?php endif; ?>
            </div>
            <?php if (empty($payments)): ?>
            <div class="summary-empty">No payments logged yet. Add your first bKash payment to start building a semester-by-semester history.</div>
            <?php else: ?>
            <div class="summary-list">
                <div class="summary-item">
                    <div>
                        <div class="summary-item-label">Latest payment date</div>
                        <div class="summary-item-value"><?= date('M j, Y', strtotime($latestPayment['payment_date'])) ?></div>
                    </div>
                    <div class="summary-item-value"><?= number_format($latestPayment['amount'], 2) ?> BDT</div>
                </div>
                <div class="summary-item">
                    <div>
                        <div class="summary-item-label">Transaction ID</div>
                        <div class="summary-item-value"><?= htmlspecialchars($latestPayment['transaction_id'] ?: 'Not saved') ?></div>
                    </div>
                    <div class="summary-item-value"><?= ucfirst($latestPayment['status']) ?></div>
                </div>
                <?php $maxAmt = max(array_values($byCat)); ?>
                <?php foreach ($byCat as $type => $amt): ?>
                <div class="summary-item">
                    <div style="flex:1;">
                        <div class="summary-item-label"><?= htmlspecialchars($type) ?></div>
                        <div style="height:7px;background:rgba(255,255,255,.06);border-radius:999px;overflow:hidden;margin-top:10px;">
                            <div style="height:100%;border-radius:999px;background:linear-gradient(90deg,#22d3ee,#818cf8);width:<?= round($amt/$maxAmt*100) ?>%;"></div>
                        </div>
                    </div>
                    <div class="summary-item-value"><?= number_format($amt,0) ?> BDT</div>
                </div>
                <?php endforeach; ?>
            </div>
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
                    <select name="payment_method" id="paymentMethodSelect" onchange="updatePaymentUI()">
                        <option value="bkash">📱 bKash</option>
                        <option value="nrbc_bank">🏦 NRBC Bank</option>
                        <option value="cash">💵 Cash</option>
                        <option value="other">💳 Other</option>
                    </select>
                </div>
                <div class="field">
                    <label>Payment Date *</label>
                    <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="field" id="txnField">
                <label id="txnLabel">bKash Transaction ID</label>
                <input type="text" name="transaction_id" id="txnInput" placeholder="e.g. 8N6A3KQDEF">
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
function updatePaymentUI() {
    const method = document.getElementById('paymentMethodSelect').value;
    const txnLabel = document.getElementById('txnLabel');
    const txnInput = document.getElementById('txnInput');
    if (method === 'bkash') {
        txnLabel.textContent = 'bKash Transaction ID';
        txnInput.placeholder = 'e.g. 8N6A3KQDEF';
    } else if (method === 'nrbc_bank') {
        txnLabel.textContent = 'Bank Receipt / Reference No.';
        txnInput.placeholder = 'e.g. NRBC-2026-04-12345';
    } else if (method === 'cash') {
        txnLabel.textContent = 'Receipt Number';
        txnInput.placeholder = 'e.g. Cash receipt number from accounts';
    } else {
        txnLabel.textContent = 'Reference / Transaction ID';
        txnInput.placeholder = 'e.g. Reference number';
    }
}
</script>
</body>
</html>
