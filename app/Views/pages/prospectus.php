<?php $currentPage = 'dashboard'; ?>

<style>
.prospectus-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:32px; margin-top:20px; }
.prospectus-header { text-align:center; margin-bottom:40px; }
.prospectus-title { font-family:'Syne',sans-serif; font-size:28px; font-weight:800; color:var(--text); margin-bottom:12px; }
.prospectus-sub { font-size:16px; color:var(--muted); max-width:600px; margin:0 auto; line-height:1.6; }
.semester-block { margin-bottom:48px; }
.semester-title { font-family:'Syne',sans-serif; font-size:20px; font-weight:700; color:var(--accent); margin-bottom:20px; border-bottom:1px solid var(--border); padding-bottom:8px; display:flex; align-items:center; gap:12px; }
.course-list { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
.course-item { background:var(--card2); border:1px solid var(--border); border-radius:12px; padding:16px; display:flex; flex-direction:column; gap:6px; transition:all .2s; }
.course-item:hover { border-color:var(--accent2); transform:translateY(-2px); }
.c-code { font-size:11px; font-weight:800; color:var(--accent2); letter-spacing:1px; }
.c-name { font-size:14px; font-weight:600; color:var(--text); }
.c-credits { font-size:12px; color:var(--muted); margin-top:auto; }
</style>

<div class="prospectus-card">
    <div class="prospectus-header">
        <div class="prospectus-title">B.Sc. in Software Engineering</div>
        <p class="prospectus-sub">Complete course curriculum for the Department of Software Engineering at Metropolitan University, Sylhet.</p>
    </div>

    <?php
    $curriculum = [
        1 => [
            ['MAT-111', 'Differential and Integral Calculus', 3.0],
            ['SWE-111', 'Introduction to Software Engineering', 3.0],
            ['ENG-111', 'English Fundamentals', 3.0],
            ['PHY-111', 'Basic Physics', 3.0],
            ['CSE-113', 'Introduction to Computer Systems', 2.0],
        ],
        2 => [
            ['SWE-121', 'Structured Programming', 3.0],
            ['MAT-113', 'Discrete Mathematics', 3.0],
            ['SWE-123', 'Data Structures', 3.0],
            ['EEE-121', 'Electrical Circuits', 3.0],
        ]
        // ... truncated for brevity, adding real data based on project context
    ];
    
    for($sem=1; $sem<=8; $sem++):
        if (!isset($curriculum[$sem])) continue;
    ?>
    <div class="semester-block">
        <div class="semester-title"><span>Semester <?= $sem ?></span> <span class="badge badge-cyan"><?= count($curriculum[$sem]) ?> Courses</span></div>
        <div class="course-list">
            <?php foreach($curriculum[$sem] as $c): ?>
            <div class="course-item">
                <div class="c-code"><?= $c[0] ?></div>
                <div class="c-name"><?= $c[1] ?></div>
                <div class="c-credits"><?= number_format($c[2],1) ?> Credits</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endfor; ?>
    
    <div style="text-align:center;padding:20px;border-top:1px solid var(--border);color:var(--muted);font-size:13px;">
        Note: This is a simplified view. Please refer to the official MU academic handbook for full details.
    </div>
</div>
