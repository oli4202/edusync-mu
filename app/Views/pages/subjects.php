<?php
$currentPage = 'subjects';
?>

<div class="page-header">
    <h2>My Subjects</h2>
    <button class="btn btn-primary" onclick="openModal('addSubject')">+ Add Subject</button>
</div>

<div class="subjects-grid">
    <?php if (!empty($subjects)): ?>
        <?php foreach ($subjects as $subject): ?>
            <div class="subject-card" style="border-left: 4px solid <?php echo htmlspecialchars($subject['color'] ?? '#818cf8'); ?>">
                <h3><?php echo htmlspecialchars($subject['name']); ?></h3>
                <p class="code"><?php echo htmlspecialchars($subject['code'] ?? ''); ?></p>
                <p class="semester">Semester <?php echo htmlspecialchars($subject['semester'] ?? 1); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="empty-state">No subjects yet. Add one to get started!</p>
    <?php endif; ?>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.subject-card {
    background: #111827;
    border: 1px solid #1e2d45;
    border-radius: 8px;
    padding: 20px;
}

.subject-card h3 {
    margin: 0 0 8px 0;
    color: #e2e8f0;
}

.subject-card .code {
    font-size: 12px;
    color: #64748b;
    margin: 0 0 8px 0;
}

.subject-card .semester {
    font-size: 12px;
    color: #64748b;
}

/* Modal Styles */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:200; align-items:center; justify-content:center; }
.modal-overlay.active { display:flex; }
.modal { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:440px; }
.modal h3 { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; margin-bottom:20px; color:#fff; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; margin-bottom:8px; font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em; }
.form-group input, .form-group select { width:100%; padding:10px 14px; border-radius:8px; border:1px solid var(--border); background:rgba(255,255,255,0.05); color:#fff; font-family:inherit; transition:border-color 0.2s; }
.form-group input:focus, .form-group select:focus { outline:none; border-color:var(--accent); }
.color-options { display:flex; gap:8px; flex-wrap:wrap; }
.color-opt { width:28px; height:28px; border-radius:50%; cursor:pointer; border:2px solid transparent; transition:all .2s; }
.color-opt:hover, .color-opt.selected { border-color:#fff; transform:scale(1.1); }
</style>

<div class="modal-overlay" id="addSubject">
    <div class="modal">
        <h3>📚 Add Subject</h3>
        <form method="POST" action="/subjects">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Subject Name <span style="font-size:10px; color:var(--accent); font-weight:400; margin-left:8px;">(Filtered by your Batch: <?= htmlspecialchars($user['batch'] ?? 'N/A') ?>)</span></label>
                <input type="text" name="name" list="subjectList" required placeholder="e.g. Data Structures & Algorithms">
                <datalist id="subjectList">
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course['code'] . ': ' . $course['name']) ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                </datalist>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Course Code</label>
                    <input type="text" name="code" placeholder="e.g. SWE201">
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <input type="number" name="semester" min="1" max="8" value="<?= $user['semester'] ?? 1 ?>">
                </div>
            </div>
            <input type="hidden" name="year" value="<?= date('Y') ?>">
            <div class="form-group">
                <label>Target Hours/Week</label>
                <input type="number" name="target_hours" step="0.5" min="0" max="40" value="5">
            </div>
            <div class="form-group">
                <label>Color</label>
                <input type="hidden" name="color" id="colorInput" value="#22d3ee">
                <div class="color-options">
                    <?php foreach(['#22d3ee','#818cf8','#34d399','#fbbf24','#f87171','#a78bfa','#f472b6','#fb923c'] as $c): ?>
                    <div class="color-opt" style="background:<?= $c ?>" onclick="document.getElementById('colorInput').value='<?= $c ?>';document.querySelectorAll('.color-opt').forEach(e=>e.classList.remove('selected'));this.classList.add('selected')"></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('addSubject')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Subject</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

document.getElementById('addSubject').addEventListener('click', function(e){
    if(e.target===this) this.classList.remove('active');
});

const defaultColorOpt = document.querySelector('.color-opt');
if (defaultColorOpt) defaultColorOpt.classList.add('selected');

const courseCatalog = <?= json_encode($courseCatalog ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const subjectInput = document.querySelector('input[name="name"]');
const codeInput = document.querySelector('input[name="code"]');
const semesterInput = document.querySelector('input[name="semester"]');
const yearInput = document.querySelector('input[name="year"]');
const defaultSemester = <?= (int) ($user['semester'] ?? 1) ?>;

function syncSubjectMeta(selectedValue) {
    const selectedCourse = courseCatalog[selectedValue];
    if (selectedCourse) {
        codeInput.value = selectedCourse.code;
        semesterInput.value = selectedCourse.semester;
        yearInput.value = selectedCourse.year;
        return;
    }

    const codeMatch = selectedValue.match(/^([A-Z]{3}\s+\d{3}(?:\/\d{3})?):/);
    codeInput.value = codeMatch ? codeMatch[1].trim() : '';
    semesterInput.value = defaultSemester;
    yearInput.value = new Date().getFullYear();
}

if (subjectInput) {
    subjectInput.addEventListener('input', function() {
        syncSubjectMeta(this.value.trim());
    });
}
</script>
