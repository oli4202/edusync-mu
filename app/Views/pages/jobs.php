<?php $currentPage = 'jobs'; ?>

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
.hint-text{font-size:12px;color:var(--muted);margin-top:-6px;margin-bottom:12px;line-height:1.6;}
</style>

<div class="topbar">
    <div>
        <div class="page-title">💼 Internship & Job Board</div>
        <div class="page-sub">Opportunities for MU Sylhet SE students</div>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('postModal').classList.add('open')">+ Post Opportunity</button>
</div>

<?php if ($flash): ?><div class="alert-<?= $flash['type'] ?>">✅ <?= $flash['message'] ?></div><?php endif; ?>

<form method="GET" action="/jobs" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
    <input type="text" name="q" placeholder="🔍 Search jobs, companies..." value="<?= htmlspecialchars($filters['q']) ?>" style="flex:1;min-width:220px;margin:0;">
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
    <?php if ($filters['q']): ?><a href="/jobs" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
</form>

<div class="filter-tabs">
    <a href="/jobs" class="tab <?= !$filters['type'] ? 'active' : '' ?>">All</a>
    <a href="/jobs?type=internship" class="tab <?= $filters['type'] === 'internship' ? 'active' : '' ?>">🎓 Internship</a>
    <a href="/jobs?type=full_time" class="tab <?= $filters['type'] === 'full_time' ? 'active' : '' ?>">💼 Full Time</a>
    <a href="/jobs?type=part_time" class="tab <?= $filters['type'] === 'part_time' ? 'active' : '' ?>">⏰ Part Time</a>
    <a href="/jobs?type=remote" class="tab <?= $filters['type'] === 'remote' ? 'active' : '' ?>">🌐 Remote</a>
    <a href="/jobs?type=freelance" class="tab <?= $filters['type'] === 'freelance' ? 'active' : '' ?>">🔧 Freelance</a>
</div>

<?php if (empty($jobList)): ?>
<div style="text-align:center;padding:48px;background:var(--card);border:1px solid var(--border);border-radius:14px;">
    <div style="font-size:40px;margin-bottom:12px;">💼</div>
    <div style="font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:8px;">No jobs posted yet</div>
    <button onclick="document.getElementById('postModal').classList.add('open')" class="btn btn-primary">+ Post First Job</button>
</div>
<?php else: ?>
<?php foreach ($jobList as $job):
    $tc = 'var(--accent)'; $tbg = 'rgba(34,211,238,.1)'; $ticon = '🎓';
    if($job['type']==='full_time'){ $tc='var(--accent3)'; $tbg='rgba(52,211,153,.1)'; $ticon='💼'; }
?>
<div class="job-card">
    <div class="job-header">
        <div style="display:flex;gap:14px;align-items:flex-start;">
            <div class="company-logo"><?= strtoupper(substr($job['company'], 0, 2)) ?></div>
            <div>
                <div class="job-title"><?= htmlspecialchars($job['title']) ?></div>
                <div class="job-company">🏢 <?= htmlspecialchars($job['company']) ?></div>
            </div>
        </div>
        <button class="save-btn <?= $job['saved'] ? 'saved' : '' ?>" onclick="saveJob(<?= $job['id'] ?>, this)">
            <?= $job['saved'] ? '🔖 Saved' : '🏷 Save' ?>
        </button>
    </div>
    <div class="job-badges">
        <span class="job-badge" style="background:<?= $tbg ?>;color:<?= $tc ?>"><?= $ticon ?> <?= ucfirst(str_replace('_', ' ', $job['type'])) ?></span>
        <?php if ($job['location']): ?><span class="job-badge" style="background:rgba(100,116,139,.1);color:var(--muted)">📍 <?= htmlspecialchars($job['location']) ?></span><?php endif; ?>
    </div>
    <div class="job-desc"><?= htmlspecialchars($job['description']) ?></div>
    <div class="job-footer">
        <div class="job-meta">
            <span>👤 <?= htmlspecialchars($job['poster_name']) ?></span>
            <span>🕒 <?= App\timeAgo($job['posted_at']) ?></span>
        </div>
        <div class="job-actions">
            <?php if ($job['apply_link']): ?><a href="<?= htmlspecialchars($job['apply_link']) ?>" target="_blank" class="btn btn-primary btn-sm">Apply Now →</a><?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<div class="modal-overlay" id="postModal">
    <div class="modal">
        <div class="modal-title">💼 Post Job / Internship</div>
        <form action="/jobs/post" method="POST">
            <div class="form-row">
                <div class="field"><label>Company Name *</label><input type="text" name="company" required></div>
                <div class="field"><label>Job Title *</label><input type="text" name="title" required></div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Type</label>
                    <select name="type">
                        <option value="internship">🎓 Internship</option>
                        <option value="full_time">💼 Full Time</option>
                        <option value="part_time">⏰ Part Time</option>
                        <option value="remote">🌐 Remote</option>
                        <option value="freelance">🔧 Freelance</option>
                    </select>
                </div>
                <div class="field"><label>Location</label><input type="text" name="location"></div>
            </div>
            <div class="field"><label>Description *</label><textarea name="description" rows="4" required></textarea></div>
            <div class="field"><label>Apply Link</label><input type="url" name="apply_link"></div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary">Post Job</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('postModal').classList.remove('open')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
async function saveJob(id, btn) {
    const fd = new FormData(); fd.append('job_id', id);
    await fetch('/jobs/save', { method: 'POST', body: fd });
    const saved = btn.classList.toggle('saved');
    btn.textContent = saved ? '🔖 Saved' : '🏷 Save';
}
document.getElementById('postModal').addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open'); });
</script>
