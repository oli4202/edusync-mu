<?php $currentPage = 'learn'; ?>

<style>
.course-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:12px;margin-bottom:28px;}
.course-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;cursor:pointer;transition:all .2s;text-decoration:none;display:block;}
.course-card:hover,.course-card.active{border-color:rgba(34,211,238,.4);background:rgba(34,211,238,.05);}
.course-code{font-size:11px;color:var(--accent);font-weight:700;margin-bottom:4px;}
.course-name{font-size:13px;font-weight:600;line-height:1.4;}
.course-year{font-size:11px;color:var(--muted);margin-top:4px;}
.video-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:0;overflow:hidden;margin-bottom:14px;transition:all .2s;}
.video-card:hover{border-color:rgba(34,211,238,.3);transform:translateY(-1px);}
.video-thumbnail{background:linear-gradient(135deg,#ff0000,#cc0000);height:140px;display:flex;align-items:center;justify-content:center;position:relative;cursor:pointer;}
.play-btn{width:60px;height:60px;background:rgba(255,255,255,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;transition:transform .2s;}
.video-card:hover .play-btn{transform:scale(1.1);}
.yt-label{position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.8);color:#fff;font-size:10px;padding:2px 8px;border-radius:4px;}
.video-body{padding:16px;}
.video-title{font-size:14px;font-weight:600;margin-bottom:6px;line-height:1.4;}
.video-meta{display:flex;gap:12px;font-size:12px;color:var(--muted);}
.video-channel{color:var(--accent);}
.ai-search-box{background:linear-gradient(135deg,rgba(34,211,238,.08),rgba(129,140,248,.08));border:1px solid rgba(34,211,238,.2);border-radius:16px;padding:24px;margin-bottom:24px;}
.ai-search-title{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:8px;}
.ai-video-result{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;margin-top:14px;font-size:14px;line-height:1.9;white-space:pre-wrap;display:none;}
.year-header{font-family:'Syne',sans-serif;font-size:14px;font-weight:700;padding:12px 0 8px;color:var(--accent);border-bottom:1px solid var(--border);margin-bottom:12px;}
</style>

<div class="topbar">
    <div>
        <div class="page-title">▶️ Learning Resources</div>
        <div class="page-sub">Curated YouTube videos & AI-recommended content for MU SE courses</div>
    </div>
</div>

<div class="ai-search-box">
    <div class="ai-search-title">🤖 AI — Find Best YouTube Videos for Any Topic</div>
    <p style="font-size:13px;color:var(--muted);margin-bottom:14px;">Ask AI to recommend the best YouTube videos for any topic in your SE courses.</p>
    <div style="display:flex;gap:10px;">
        <input type="text" id="aiTopicInput" placeholder="e.g. Binary search trees, SQL joins, TCP/IP, OOP inheritance..." style="flex:1;margin:0;">
        <button class="btn btn-primary" onclick="aiSearchVideos()">🔍 Find Videos</button>
    </div>
    <div class="loading" id="aiSearchLoading" style="margin-top:12px;"><div class="spinner"></div> Finding best videos...</div>
    <div class="ai-video-result" id="aiVideoResult"></div>
</div>

<div style="margin-bottom:16px;">
    <div style="font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:14px;">📚 Browse by Course</div>
    <?php
    $yearGroups = [];
    foreach ($courses as $c) $yearGroups[$c['year']][] = $c;
    foreach ($yearGroups as $yr => $yCourses):
    ?>
    <div class="year-header">Year <?= $yr ?></div>
    <div class="course-grid">
        <?php foreach ($yCourses as $c): ?>
        <a href="/learn?course=<?= $c['id'] ?>" class="course-card <?= $selectedCourseId == $c['id'] ? 'active' : '' ?>">
            <div class="course-code"><?= htmlspecialchars($c['code']) ?></div>
            <div class="course-name"><?= htmlspecialchars($c['name']) ?></div>
            <div class="course-year">Semester <?= $c['semester'] ?></div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($courseData):
    $courseName = $courseData['name'];
    $videos = App\Models\Learn::findCourseVideos($courseName);
?>
<div style="font-family:'Syne',sans-serif;font-size:18px;font-weight:800;margin-bottom:6px;">▶️ <?= htmlspecialchars($courseName) ?></div>
<div style="color:var(--muted);font-size:13px;margin-bottom:20px;"><?= htmlspecialchars($courseData['code']) ?> · Year <?= $courseData['year'] ?> Semester <?= $courseData['semester'] ?></div>

<?php if ($videos): ?>
<div class="grid-2">
    <?php foreach ($videos as $v): ?>
    <div class="video-card">
        <div class="video-thumbnail" onclick="window.open('<?= htmlspecialchars($v[1]) ?>','_blank')">
            <div class="play-btn">▶</div>
            <div class="yt-label">📺 YouTube</div>
        </div>
        <div class="video-body">
            <div class="video-title"><?= htmlspecialchars($v[0]) ?></div>
            <div style="font-size:13px;color:var(--muted);margin-bottom:10px;line-height:1.5;"><?= htmlspecialchars($v[2]) ?></div>
            <div class="video-meta">
                <span class="video-channel">👤 <?= htmlspecialchars($v[3]) ?></span>
                <span>⏱ <?= htmlspecialchars($v[4]) ?></span>
            </div>
            <a href="<?= htmlspecialchars($v[1]) ?>" target="_blank" class="btn btn-primary btn-sm" style="margin-top:12px;width:100%;justify-content:center;">▶ Watch on YouTube</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div style="background:rgba(129,140,248,.06);border:1px solid rgba(129,140,248,.2);border-radius:14px;padding:20px;margin-top:20px;">
    <div style="font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:8px;">🤖 Get More AI-Recommended Videos</div>
    <p style="font-size:13px;color:var(--muted);margin-bottom:14px;">Ask AI for specific subtopics within <?= htmlspecialchars($courseName) ?></p>
    <button class="btn btn-primary" onclick="aiCourseVideos('<?= htmlspecialchars(addslashes($courseName)) ?>')">Find More Videos for <?= htmlspecialchars($courseData['code']) ?></button>
</div>
<?php else: ?>
<div style="text-align:center;padding:32px;background:var(--card);border:1px solid var(--border);border-radius:14px;">
    <div style="font-size:36px;margin-bottom:12px;">🤖</div>
    <div style="font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:8px;">No curated videos yet</div>
    <div style="color:var(--muted);font-size:14px;margin-bottom:16px;">Use AI to find the best YouTube content for this course.</div>
    <button class="btn btn-primary" onclick="aiCourseVideos('<?= htmlspecialchars(addslashes($courseName)) ?>')">🤖 Find Videos with AI</button>
</div>
<?php endif; ?>

<?php else: ?>
<div style="font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:16px;">⭐ Top Picks for SE Students</div>
<div class="grid-2">
    <?php
    $topPicks = [
        ['CS50 — Harvard (Best Intro to CS)', 'https://www.youtube.com/watch?v=8mAITcNt710', 'The most loved CS course on the internet. Uses C and Python.', 'Harvard', 'Full Course'],
        ['Abdul Bari — DSA', 'https://www.youtube.com/watch?v=0IAPZzGSbME', 'Complete DSA course. A must-watch for every SE student.', 'Abdul Bari', '12h'],
        ['3Blue1Brown — Neural Networks', 'https://www.youtube.com/watch?v=aircAruvnKk', 'Visual explanation of neural networks. Beautiful animations.', '3Blue1Brown', '4 episodes'],
        ['Traversy Media — Full Stack Web Dev', 'https://www.youtube.com/watch?v=ysEN5RaKOlA', 'Build real projects with HTML, CSS, JS, PHP, MySQL.', 'Traversy Media', 'Full'],
    ];
    foreach ($topPicks as $v):
    ?>
    <div class="video-card">
        <div class="video-thumbnail" onclick="window.open('<?= htmlspecialchars($v[1]) ?>','_blank')">
            <div class="play-btn">▶</div>
            <div class="yt-label">📺 YouTube</div>
        </div>
        <div class="video-body">
            <div class="video-title"><?= htmlspecialchars($v[0]) ?></div>
            <div style="font-size:13px;color:var(--muted);margin-bottom:10px;"><?= htmlspecialchars($v[2]) ?></div>
            <div class="video-meta">
                <span class="video-channel">👤 <?= htmlspecialchars($v[3]) ?></span>
                <span>⏱ <?= htmlspecialchars($v[4]) ?></span>
            </div>
            <a href="<?= htmlspecialchars($v[1]) ?>" target="_blank" class="btn btn-primary btn-sm" style="margin-top:12px;width:100%;justify-content:center;">▶ Watch on YouTube</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
async function aiSearchVideos() {
    const topic = document.getElementById('aiTopicInput').value.trim();
    if (!topic) { alert('Please enter a topic.'); return; }
    document.getElementById('aiSearchLoading').style.display = 'flex';
    document.getElementById('aiVideoResult').style.display = 'none';

    const prompt = `You are a study resource advisor for Metropolitan University Sylhet, Software Engineering students. Topic: ${topic}. Recommend 5 best YouTube channels/videos.`;

    try {
        const resp = await fetch('/api/ai/chat',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({prompt})});
        const data = await resp.json();
        document.getElementById('aiSearchLoading').style.display = 'none';
        document.getElementById('aiVideoResult').style.display = 'block';
        document.getElementById('aiVideoResult').textContent = data.text || 'Error.';
    } catch(e) {
        document.getElementById('aiSearchLoading').style.display = 'none';
        document.getElementById('aiVideoResult').textContent = 'Failed.';
        document.getElementById('aiVideoResult').style.display = 'block';
    }
}
function aiCourseVideos(name) {
    document.getElementById('aiTopicInput').value = name;
    aiSearchVideos();
}
</script>
