<?php $currentPage = 'question-bank'; ?>

<style>
    .qb-header { margin-bottom: 20px; }
    .qb-header h1 { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 800; }
    .qb-header p { font-size: 13px; color: var(--muted); }
    .qb-layout { display: grid; grid-template-columns: 260px 1fr; gap: 20px; align-items: start; }
    .qb-sidebar { position: sticky; top: 20px; }
    .filter-panel { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 14px; margin-bottom: 14px; }
    .fp-title { font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; }
    .course-btn { display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; border-radius: 8px; cursor: pointer; font-size: 12px; color: var(--muted); text-decoration: none; transition: all .15s; border: none; background: none; width: 100%; text-align: left; font-family: inherit; }
    .course-btn:hover { background: rgba(34, 211, 238, .06); color: var(--text); }
    .course-btn.on { background: rgba(34, 211, 238, .1); color: var(--accent); font-weight: 600; }
    .course-btn .cnt { font-size: 10px; background: var(--card2); padding: 1px 6px; border-radius: 8px; color: var(--muted); }
    .hot-topic { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; padding: 3px 9px; border-radius: 10px; cursor: pointer; text-decoration: none; transition: all .15s; border: none; background: none; font-family: inherit; margin: 2px; }
    .ht-1 { background: rgba(248, 113, 113, .15); color: #f87171; border: 1px solid rgba(248, 113, 113, .2); }
    .ht-2 { background: rgba(251, 191, 36, .12); color: var(--warn); border: 1px solid rgba(251, 191, 36, .15); }
    .ht-3 { background: rgba(34, 211, 238, .1); color: var(--accent); border: 1px solid rgba(34, 211, 238, .15); }
    .hot-topic.on { background: var(--accent) !important; color: #0a0e1a !important; font-weight: 700; }
    .qb-search { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
    .qb-search input { flex: 1; min-width: 200px; background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 9px 14px; color: var(--text); font-size: 13px; outline: none; font-family: inherit; }
    .qb-search input:focus { border-color: var(--accent); }
    .stats-bar { display: flex; gap: 16px; margin-bottom: 16px; flex-wrap: wrap; }
    .stat-pill { background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 8px 14px; font-size: 12px; }
    .stat-pill strong { color: var(--accent); font-size: 16px; display: block; font-family: 'Syne', sans-serif; }
    .q-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 12px; transition: all .2s; }
    .q-card:hover { border-color: rgba(34, 211, 238, .3); box-shadow: 0 4px 12px rgba(0, 0, 0, .15); }
    .qc-top { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px; }
    .qc-badges { display: flex; gap: 6px; flex-wrap: wrap; flex-shrink: 0; }
    .badge { font-size: 10px; padding: 2px 8px; border-radius: 8px; font-weight: 700; white-space: nowrap; }
    .badge-course { background: rgba(34, 211, 238, .12); color: var(--accent); }
    .badge-type { background: rgba(129, 140, 248, .12); color: var(--accent2); }
    .badge-marks { background: rgba(52, 211, 153, .12); color: #34d399; }
    .badge-topic { background: rgba(251, 191, 36, .1); color: var(--warn); }
    .qc-text { font-size: 13px; color: var(--text); line-height: 1.7; white-space: pre-line; flex: 1; }
    .qc-bottom { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; flex-wrap: wrap; gap: 8px; }
    .freq-bar { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--muted); }
    .freq-dots { display: flex; gap: 3px; }
    .freq-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--border); }
    .freq-dot.on { background: var(--warn); }
    .freq-dot.high { background: #f87171; }
    .qc-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .exam-info { background: var(--card2); border: 1px solid var(--border); border-radius: 10px; padding: 12px; margin-bottom: 12px; }
    .exam-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; padding: 4px 0; border-bottom: 1px solid var(--border); font-size: 11px; color: var(--muted); }
    .exam-row:last-child { border-bottom: none; }
    .exam-row strong { color: var(--text); }
    .analysis-panel { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 14px; }
    .analysis-title { font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 12px; font-family: 'Syne', sans-serif; }
    .freq-item { display: flex; align-items: center; gap: 8px; padding: 5px 0; font-size: 12px; }
    .freq-bar-visual { flex: 1; height: 6px; background: var(--border); border-radius: 3px; overflow: hidden; }
    .freq-bar-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--accent), var(--accent2)); }
    @media(max-width:900px) { .qb-layout { grid-template-columns: 1fr; } .qb-sidebar { position: static; } }
</style>

<div class="qb-header">
    <h1>MU SWE Question Bank</h1>
    <p><?= count($courses) ?> courses · <?= $totalQuestionCount ?> total questions in the bank<?= !empty($usingDb) ? ' (database-backed)' : ' (sample fallback)' ?></p>
</div>

<div class="qb-layout">
    <div class="qb-sidebar">
        <div class="filter-panel">
            <div class="fp-title">Batches</div>
            <a href="/question-bank" class="course-btn <?= !$filters['batch'] ? 'on' : '' ?>">All Batches</a>
            <?php foreach ($availableBatches as $batch): ?>
                <a href="/question-bank?batch=<?= urlencode($batch) ?>" class="course-btn <?= $filters['batch'] == $batch ? 'on' : '' ?>">
                    Batch <?= htmlspecialchars($batch) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="filter-panel">
            <div class="fp-title">Courses</div>
            <a href="/question-bank" class="course-btn <?= !$filters['course'] ? 'on' : '' ?>">
                All Courses <span class="cnt"><?= $totalQuestionCount ?></span>
            </a>
            <?php foreach ($courses as $code => $course): ?>
                <a href="/question-bank?course=<?= urlencode($code) ?>" class="course-btn <?= $filters['course'] === $code ? 'on' : '' ?>">
                    <span><?= htmlspecialchars($code) ?><br><span style="font-size:10px;font-weight:400"><?= htmlspecialchars(mb_substr($course['name'], 0, 22)) ?></span></span>
                    <span class="cnt"><?= (int) $course['question_count'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="analysis-panel">
            <div class="analysis-title">Most Frequent Topics</div>
            <?php
            $maxFreq = !empty($hotTopics) ? max(array_map(static fn ($topic) => $topic['freq_sum'], $hotTopics)) : 1;
            foreach ($hotTopics as $topic => $data): ?>
                <div class="freq-item">
                    <div style="font-size:10px;width:110px;color:var(--muted);line-height:1.3;"><?= htmlspecialchars($topic) ?></div>
                    <div class="freq-bar-visual"><div class="freq-bar-fill" style="width:<?= round($data['freq_sum'] / $maxFreq * 100) ?>%"></div></div>
                    <div style="font-size:10px;color:var(--warn);font-weight:700;width:20px;"><?= (int) $data['freq_sum'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="filter-panel">
            <div class="fp-title">Question Types</div>
            <a href="/question-bank" class="course-btn <?= !$filters['type'] ? 'on' : '' ?>">All Types</a>
            <?php foreach ($allTypes as $type => $count): ?>
                <a href="/question-bank?type=<?= urlencode($type) ?>" class="course-btn <?= $filters['type'] === $type ? 'on' : '' ?>">
                    <?= htmlspecialchars($type) ?> <span class="cnt"><?= (int) $count ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div>
        <form method="GET" action="/question-bank" class="qb-search">
            <?php if ($filters['course']): ?><input type="hidden" name="course" value="<?= htmlspecialchars($filters['course']) ?>"><?php endif; ?>
            <input type="text" name="q" placeholder="Search questions, topics, theorems..." value="<?= htmlspecialchars($filters['q']) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($filters['q'] || $filters['topic']): ?><a href="/question-bank<?= $filters['course'] ? '?course=' . urlencode($filters['course']) : '' ?>" class="btn btn-outline">Clear</a><?php endif; ?>
        </form>

        <div style="margin-bottom:14px;">
            <?php $topicIndex = 0; foreach ($hotTopics as $topic => $data): $topicIndex++; $class = $topicIndex <= 3 ? 'ht-1' : ($topicIndex <= 6 ? 'ht-2' : 'ht-3'); ?>
                <a href="/question-bank?topic=<?= urlencode($topic) ?><?= $filters['course'] ? '&course=' . urlencode($filters['course']) : '' ?>" class="hot-topic <?= $class ?> <?= $filters['topic'] === $topic ? 'on' : '' ?>">
                    <?= htmlspecialchars($topic) ?> <span style="opacity:.6"><?= (int) $data['count'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="stats-bar">
            <div class="stat-pill"><strong><?= count($filteredQs) ?></strong> Questions shown</div>
            <div class="stat-pill"><strong><?= count($courses) ?></strong> Courses</div>
            <div class="stat-pill" style="background:rgba(248,113,113,.08);border-color:rgba(248,113,113,.2);"><strong style="color:#f87171;"><?= !empty($usingDb) ? 'Live DB' : 'Fallback' ?></strong> <?= !empty($usingDb) ? 'Approved records only' : 'Static catalog active' ?></div>
        </div>

        <?php if ($filters['course'] && !empty($examHistory)): ?>
            <div class="exam-info">
                <div style="font-size:12px;font-weight:700;color:var(--accent);margin-bottom:8px;"><?= htmlspecialchars($filters['course']) ?> - Exam History</div>
                <?php foreach ($examHistory as $exam): ?>
                    <div class="exam-row">
                        <?php if (!empty($usingDb)): ?>
                            <span class="badge badge-course"><?= htmlspecialchars($exam['exam_semester'] ?: 'Unknown Semester') ?></span>
                            <strong><?= htmlspecialchars((string) ($exam['exam_year'] ?: 'Unknown Year')) ?></strong>
                            <span>Questions <?= (int) $exam['question_count'] ?></span>
                            <span>· Top marks <?= (int) $exam['top_marks'] ?></span>
                        <?php else: ?>
                            <span class="badge badge-course"><?= htmlspecialchars($exam['type']) ?></span>
                            <strong><?= htmlspecialchars($exam['term']) ?></strong>
                            <span>Batch <?= htmlspecialchars($exam['batch']) ?></span>
                            <span>· <?= htmlspecialchars((string) $exam['marks']) ?> marks</span>
                            <span>· <?= htmlspecialchars($exam['time']) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($filteredQs)): ?>
            <div style="text-align:center;padding:48px;color:var(--muted);">
                <div style="font-size:40px;margin-bottom:12px;">Search</div>
                <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px;">No questions found</div>
                <div style="font-size:13px;">Try a different search or remove filters.</div>
            </div>
        <?php else: ?>
            <?php foreach ($filteredQs as $q): $freqLevel = $q['freq'] >= 4 ? 'high' : 'on'; ?>
                <div class="q-card">
                    <div class="qc-top"><div class="qc-text"><?= nl2br(htmlspecialchars($q['q'])) ?></div></div>
                    <div class="qc-badges">
                        <span class="badge badge-course"><?= htmlspecialchars($q['course_code']) ?></span>
                        <span class="badge badge-type"><?= htmlspecialchars($q['type']) ?></span>
                        <span class="badge badge-marks"><?= (int) $q['marks'] ?> marks</span>
                        <?php if (!empty($q['topic'])): ?>
                            <span class="badge badge-topic"><?= htmlspecialchars($q['topic']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="qc-bottom">
                        <div class="freq-bar">
                            <span>Frequency:</span>
                            <div class="freq-dots">
                                <?php for ($dot = 1; $dot <= 5; $dot++): ?>
                                    <div class="freq-dot <?= $dot <= $q['freq'] ? $freqLevel : '' ?>"></div>
                                <?php endfor; ?>
                            </div>
                            <span><?= $q['freq'] >= 4 ? 'Very High' : ($q['freq'] >= 3 ? 'High' : 'Medium') ?></span>
                        </div>
                        <div class="qc-actions">
                            <?php if (!empty($q['id'])): ?>
                                <a href="/question-bank/<?= (int) $q['id'] ?>" class="btn btn-outline btn-sm">View Details</a>
                            <?php endif; ?>
                            <a href="/suggestions?course=<?= urlencode($q['course_code']) ?>" class="btn btn-outline btn-sm">AI Solve</a>
                            <a href="/ai" class="btn btn-primary btn-sm">Ask AI</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div style="background:rgba(34,211,238,.05);border:1px dashed rgba(34,211,238,.2);border-radius:12px;padding:20px;text-align:center;margin-top:20px;">
            <div style="font-size:14px;font-weight:600;margin-bottom:6px;color:var(--text);">Have more exam questions?</div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:12px;">Help your batchmates by submitting questions from exams not yet in the bank.</div>
            <a href="/question-bank/submit" class="btn btn-primary">Submit a Question</a>
        </div>
    </div>
</div>
