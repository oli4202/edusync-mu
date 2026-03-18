<?php
// pages/jobs.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$currentPage = 'jobs';

$db->exec("CREATE TABLE IF NOT EXISTS job_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company VARCHAR(150) NOT NULL,
    title VARCHAR(200) NOT NULL,
    type ENUM('internship','full_time','part_time','remote','freelance') DEFAULT 'internship',
    location VARCHAR(150),
    description TEXT,
    requirements TEXT,
    salary VARCHAR(100),
    deadline DATE,
    apply_link VARCHAR(500),
    apply_email VARCHAR(150),
    is_approved BOOLEAN DEFAULT FALSE,
    approved_by INT,
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$db->exec("CREATE TABLE IF NOT EXISTS job_saves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_save (user_id, job_id)
)");

$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'post') {
        $company = clean($_POST['company']);
        $title   = clean($_POST['title']);
        $type    = clean($_POST['type']);
        $loc     = clean($_POST['location'] ?? '');
        $desc    = clean($_POST['description']);
        $req     = clean($_POST['requirements'] ?? '');
        $salary  = clean($_POST['salary'] ?? '');
        $deadline= clean($_POST['deadline'] ?? '');
        $link    = clean($_POST['apply_link'] ?? '');
        $email   = clean($_POST['apply_email'] ?? '');
        if (!$company || !$title || !$desc) { $err = 'Fill in required fields.'; }
        else {
            $approved = $user['role']==='admin' ? 1 : 0;
            $db->prepare("INSERT INTO job_posts (user_id,company,title,type,location,description,requirements,salary,deadline,apply_link,apply_email,is_approved) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$user['id'],$company,$title,$type,$loc,$desc,$req,$salary,$deadline?:null,$link,$email,$approved]);
            $msg = $user['role']==='admin' ? 'Job posted!' : 'Job submitted for review!';
        }
    } elseif ($action === 'approve') {
        $db->prepare("UPDATE job_posts SET is_approved=1,approved_by=? WHERE id=?")->execute([$user['id'],(int)$_POST['job_id']]);
        $msg = 'Job approved!';
    } elseif ($action === 'delete') {
        $db->prepare("DELETE FROM job_posts WHERE id=?")->execute([(int)$_POST['job_id']]);
        $msg = 'Deleted.';
    } elseif ($action === 'save') {
        $jid = (int)$_POST['job_id'];
        $check = $db->prepare("SELECT id FROM job_saves WHERE user_id=? AND job_id=?");
        $check->execute([$user['id'],$jid]);
        if ($check->fetch()) {
            $db->prepare("DELETE FROM job_saves WHERE user_id=? AND job_id=?")->execute([$user['id'],$jid]);
        } else {
            $db->prepare("INSERT INTO job_saves (user_id,job_id) VALUES (?,?)")->execute([$user['id'],$jid]);
        }
        header('Content-Type: application/json'); echo json_encode(['ok'=>1]); exit();
    }
}

$typeFilter = clean($_GET['type'] ?? '');
$search     = clean($_GET['q'] ?? '');
$where = ['j.is_approved=1'];
$params = [];
if ($typeFilter) { $where[] = 'j.type=?'; $params[] = $typeFilter; }
if ($search) { $where[] = '(j.title LIKE ? OR j.company LIKE ? OR j.description LIKE ?)'; $params = array_merge($params,array_fill(0,3,"%$search%")); }

$jobs = $db->prepare("SELECT j.*, u.name AS poster_name,
    (SELECT COUNT(*) FROM job_saves s WHERE s.job_id=j.id AND s.user_id=?) AS saved
    FROM job_posts j JOIN users u ON j.user_id=u.id
    WHERE ".implode(' AND ',$where)." ORDER BY j.posted_at DESC");
$jobs->execute(array_merge([$user['id']],$params));
$jobList = $jobs->fetchAll();

$typeColors=['internship'=>['var(--accent)','rgba(34,211,238,.1)','🎓'],
    'full_time'=>['var(--accent3)','rgba(52,211,153,.1)','💼'],
    'part_time'=>['var(--accent2)','rgba(129,140,248,.1)','⏰'],
    'remote'=>['var(--warn)','rgba(251,191,36,.1)','🌐'],
    'freelance'=>['#f97316','rgba(249,115,22,.1)','🔧']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Internship & Jobs — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.job-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:22px;margin-bottom:14px;transition:all .2s;}
.job-card:hover{border-color:rgba(34,211,238,.3);}
.job-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;gap:12px;}
.company-logo{width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:16px;color:#0a0e1a;flex-shrink:0;}
.job-title{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:3px;}
.job-company{font-size:13px;color:var(--muted);}
.job-badges{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;}
.job-badge{font-size:11px;padding:3px 10px;border-radius:20px;}
.job-desc{font-size:13px;color:#cbd5e1;line-height:1.7;margin-bottom:14px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
.job-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;}
.job-meta{display:flex;gap:14px;font-size:12px;color:var(--muted);}
.job-actions{display:flex;gap:8px;}
.save-btn{background:none;border:1px solid var(--border);border-radius:8px;padding:6px 12px;font-size:12px;cursor:pointer;color:var(--muted);transition:all .2s;}
.save-btn.saved{color:var(--warn);border-color:rgba(251,191,36,.3);}
.filter-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;}
.tab{padding:7px 16px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:1px solid var(--border);color:var(--muted);transition:all .2s;background:transparent;text-decoration:none;display:inline-block;}
.tab:hover,.tab.active{background:rgba(34,211,238,.1);border-color:var(--accent);color:var(--accent);}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:999;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px;width:100%;max-width:540px;animation:fadeUp .3s ease;max-height:90vh;overflow-y:auto;}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.modal-title{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:20px;}
.field{margin-bottom:14px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">💼 Internship & Job Board</div>
            <div class="page-sub">Opportunities for MU Sylhet SE students — internships, jobs & freelance</div>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('postModal').classList.add('open')">+ Post Opportunity</button>
    </div>

    <?php if ($msg): ?><div class="alert-success">✅ <?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert-error">⚠ <?= $err ?></div><?php endif; ?>

    <!-- Search & Filter -->
    <form method="GET" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <input type="text" name="q" placeholder="🔍 Search jobs, companies..." value="<?= htmlspecialchars($search) ?>" style="flex:1;min-width:220px;margin:0;">
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
        <?php if($search): ?><a href="jobs.php" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
    </form>

    <div class="filter-tabs">
        <a href="jobs.php" class="tab <?= !$typeFilter?'active':'' ?>">All</a>
        <a href="?type=internship" class="tab <?= $typeFilter==='internship'?'active':'' ?>">🎓 Internship</a>
        <a href="?type=full_time" class="tab <?= $typeFilter==='full_time'?'active':'' ?>">💼 Full Time</a>
        <a href="?type=part_time" class="tab <?= $typeFilter==='part_time'?'active':'' ?>">⏰ Part Time</a>
        <a href="?type=remote" class="tab <?= $typeFilter==='remote'?'active':'' ?>">🌐 Remote</a>
        <a href="?type=freelance" class="tab <?= $typeFilter==='freelance'?'active':'' ?>">🔧 Freelance</a>
    </div>

    <?php if (empty($jobList)): ?>
    <div style="text-align:center;padding:48px;background:var(--card);border:1px solid var(--border);border-radius:14px;">
        <div style="font-size:40px;margin-bottom:12px;">💼</div>
        <div style="font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:8px;">No jobs posted yet</div>
        <div style="color:var(--muted);font-size:14px;margin-bottom:16px;">Be the first to post an opportunity for SE students!</div>
        <button onclick="document.getElementById('postModal').classList.add('open')" class="btn btn-primary">+ Post First Job</button>
    </div>
    <?php else: ?>
    <div style="color:var(--muted);font-size:13px;margin-bottom:16px;"><?= count($jobList) ?> opportunities found</div>
    <?php foreach ($jobList as $job):
        [$tc,$tbg,$ticon] = $typeColors[$job['type']] ?? [$typeColors['internship']];
    ?>
    <div class="job-card">
        <div class="job-header">
            <div style="display:flex;gap:14px;align-items:flex-start;">
                <div class="company-logo"><?= strtoupper(substr($job['company'],0,2)) ?></div>
                <div>
                    <div class="job-title"><?= htmlspecialchars($job['title']) ?></div>
                    <div class="job-company">🏢 <?= htmlspecialchars($job['company']) ?></div>
                </div>
            </div>
            <button class="save-btn <?= $job['saved']?'saved':'' ?>" id="save-<?= $job['id'] ?>"
                onclick="saveJob(<?= $job['id'] ?>)"><?= $job['saved']?'🔖 Saved':'🏷 Save' ?></button>
        </div>
        <div class="job-badges">
            <span class="job-badge" style="background:<?= $tbg ?>;color:<?= $tc ?>"><?= $ticon ?> <?= ucfirst(str_replace('_',' ',$job['type'])) ?></span>
            <?php if ($job['location']): ?><span class="job-badge" style="background:rgba(100,116,139,.1);color:var(--muted)">📍 <?= htmlspecialchars($job['location']) ?></span><?php endif; ?>
            <?php if ($job['salary']): ?><span class="job-badge" style="background:rgba(52,211,153,.1);color:var(--accent3)">💰 <?= htmlspecialchars($job['salary']) ?></span><?php endif; ?>
            <?php if ($job['deadline']): ?>
            <?php $daysLeft = (strtotime($job['deadline'])-time())/86400; ?>
            <span class="job-badge" style="background:<?= $daysLeft<7?'rgba(248,113,113,.1)':'rgba(251,191,36,.1)' ?>;color:<?= $daysLeft<7?'var(--danger)':'var(--warn)' ?>">
                ⏳ <?= $daysLeft < 0 ? 'Expired' : 'Deadline: '.date('M j',strtotime($job['deadline'])) ?>
            </span>
            <?php endif; ?>
        </div>
        <div class="job-desc"><?= htmlspecialchars($job['description']) ?></div>
        <div class="job-footer">
            <div class="job-meta">
                <span>👤 <?= htmlspecialchars($job['poster_name']) ?></span>
                <span>🕒 <?= timeAgo($job['posted_at']) ?></span>
            </div>
            <div class="job-actions">
                <?php if ($job['apply_link']): ?>
                <a href="<?= htmlspecialchars($job['apply_link']) ?>" target="_blank" class="btn btn-primary btn-sm">Apply Now →</a>
                <?php elseif ($job['apply_email']): ?>
                <a href="mailto:<?= htmlspecialchars($job['apply_email']) ?>" class="btn btn-primary btn-sm">📧 Apply via Email</a>
                <?php endif; ?>
                <?php if ($user['role']==='admin' && !$job['is_approved']): ?>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm">✅ Approve</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</main>

<!-- Post Job Modal -->
<div class="modal-overlay" id="postModal">
    <div class="modal">
        <div class="modal-title">💼 Post Job / Internship</div>
        <?php if ($user['role']!=='admin'): ?>
        <div style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.2);border-radius:8px;padding:10px;font-size:13px;color:var(--muted);margin-bottom:16px;">
            ℹ️ Your post will be reviewed by admin before appearing publicly.
        </div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="action" value="post">
            <div class="form-row">
                <div class="field"><label>Company Name *</label><input type="text" name="company" placeholder="e.g. Brain Station 23" required></div>
                <div class="field"><label>Job Title *</label><input type="text" name="title" placeholder="e.g. Junior Software Engineer" required></div>
            </div>
            <div class="form-row">
                <div class="field"><label>Type</label>
                    <select name="type">
                        <option value="internship">🎓 Internship</option>
                        <option value="full_time">💼 Full Time</option>
                        <option value="part_time">⏰ Part Time</option>
                        <option value="remote">🌐 Remote</option>
                        <option value="freelance">🔧 Freelance</option>
                    </select>
                </div>
                <div class="field"><label>Location</label><input type="text" name="location" placeholder="e.g. Sylhet / Remote / Dhaka"></div>
            </div>
            <div class="field"><label>Description *</label><textarea name="description" rows="4" placeholder="Describe the role, responsibilities..." required></textarea></div>
            <div class="field"><label>Requirements</label><textarea name="requirements" rows="3" placeholder="Skills required: PHP, MySQL, JavaScript..."></textarea></div>
            <div class="form-row">
                <div class="field"><label>Salary / Stipend</label><input type="text" name="salary" placeholder="e.g. ৳15,000/month"></div>
                <div class="field"><label>Application Deadline</label><input type="date" name="deadline"></div>
            </div>
            <div class="form-row">
                <div class="field"><label>Apply Link (URL)</label><input type="url" name="apply_link" placeholder="https://..."></div>
                <div class="field"><label>Apply via Email</label><input type="email" name="apply_email" placeholder="hr@company.com"></div>
            </div>
            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="btn btn-primary">Post Job</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('postModal').classList.remove('open')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
async function saveJob(id) {
    const btn = document.getElementById('save-'+id);
    const form = new FormData();
    form.append('action','save');
    form.append('job_id',id);
    await fetch('', {method:'POST', body:form});
    const saved = btn.classList.toggle('saved');
    btn.textContent = saved ? '🔖 Saved' : '🏷 Save';
}
document.getElementById('postModal').addEventListener('click',function(e){if(e.target===this)this.classList.remove('open')});
</script>
</body>
</html>
