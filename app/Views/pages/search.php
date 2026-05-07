<?php $pageTitle = 'Search Results — EduSync'; ?>

<style>
    .search-container { max-width: 1200px; margin: 0 auto; }
    .search-bar { display: flex; gap: 10px; margin-bottom: 30px; }
    .search-input { flex: 1; background: rgba(15,23,42,0.8); border: 1px solid rgba(148,163,184,0.3); border-radius: 12px; padding: 14px 18px; color: #fff; font-size: 15px; outline: none; }
    .search-input:focus { border-color: #22d3ee; box-shadow: 0 0 0 3px rgba(34,211,238,0.1); }
    .search-btn { padding: 14px 30px; background: rgba(34,211,238,0.2); border: 1px solid #22d3ee; color: #22d3ee; border-radius: 12px; cursor: pointer; font-weight: 600; transition: all 0.2s; }
    .search-btn:hover { background: rgba(34,211,238,0.3); }

    .filter-tabs { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
    .filter-tab { padding: 10px 18px; background: rgba(15,23,42,0.6); border: 1px solid rgba(148,163,184,0.2); color: #94a3b8; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s; }
    .filter-tab.active { background: rgba(34,211,238,0.2); border-color: #22d3ee; color: #22d3ee; }
    .filter-tab:hover { border-color: rgba(34,211,238,0.5); }

    .result-section { margin-bottom: 40px; }
    .section-header { font-size: 13px; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .section-count { background: rgba(34,211,238,0.1); color: #22d3ee; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }

    .result-item { background: rgba(15,23,42,0.6); border: 1px solid rgba(148,163,184,0.1); border-radius: 12px; padding: 18px; margin-bottom: 12px; transition: all 0.2s; }
    .result-item:hover { border-color: rgba(34,211,238,0.3); background: rgba(15,23,42,0.8); }

    .result-title { font-size: 15px; font-weight: 600; color: #fff; margin-bottom: 8px; }
    .result-title a { color: #22d3ee; text-decoration: none; }
    .result-title a:hover { text-decoration: underline; }

    .result-meta { font-size: 12px; color: #94a3b8; margin-bottom: 10px; display: flex; gap: 15px; flex-wrap: wrap; }
    .result-meta span { display: flex; align-items: center; gap: 5px; }

    .result-snippet { font-size: 13px; color: #cbd5e1; line-height: 1.6; margin-bottom: 12px; }
    .search-highlight { background: rgba(34,211,238,0.2); color: #22d3ee; font-weight: 600; padding: 2px 4px; border-radius: 3px; }

    .question-result { border-left: 3px solid #3b82f6; }
    .user-result { border-left: 3px solid #8b5cf6; }
    .group-result { border-left: 3px solid #10b981; }
    .job-result { border-left: 3px solid #f59e0b; }

    .empty-state { text-align: center; padding: 80px 20px; color: #94a3b8; }
    .empty-state-icon { font-size: 48px; opacity: 0.3; margin-bottom: 20px; }

    .pagination { display: flex; gap: 5px; justify-content: center; margin-top: 30px; flex-wrap: wrap; }
    .pagination a, .pagination span { padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(148,163,184,0.2); color: #94a3b8; text-decoration: none; font-size: 12px; }
    .pagination a:hover { border-color: #22d3ee; color: #22d3ee; background: rgba(34,211,238,0.05); }
    .pagination .active { background: rgba(34,211,238,0.2); border-color: #22d3ee; color: #22d3ee; font-weight: 600; }
    .pagination .disabled { opacity: 0.5; cursor: not-allowed; }
</style>

<div class="search-container">
    <!-- Search Header -->
    <div style="margin-bottom: 40px;">
        <h1 class="text-3xl font-bold text-white font-syne mb-4">Search Results</h1>
        
        <form method="GET" action="/search" class="search-bar">
            <input type="text" name="q" class="search-input" placeholder="Search questions, people, groups, jobs..." value="<?= htmlspecialchars($query) ?>" required autofocus>
            <button type="submit" class="search-btn">🔍 Search</button>
        </form>

        <?php if ($query): ?>
            <p class="text-sm text-slate-400">
                Searching for: <span class="text-accent-cyan font-semibold"><?= htmlspecialchars($query) ?></span>
            </p>
        <?php endif; ?>
    </div>

    <!-- Filter Tabs -->
    <?php if ($query): ?>
        <div class="filter-tabs">
            <a href="/search?q=<?= urlencode($query) ?>&type=all" class="filter-tab <?= $type === 'all' ? 'active' : '' ?>">All</a>
            <a href="/search?q=<?= urlencode($query) ?>&type=questions" class="filter-tab <?= $type === 'questions' ? 'active' : '' ?>">Questions</a>
            <a href="/search?q=<?= urlencode($query) ?>&type=users" class="filter-tab <?= $type === 'users' ? 'active' : '' ?>">People</a>
            <a href="/search?q=<?= urlencode($query) ?>&type=groups" class="filter-tab <?= $type === 'groups' ? 'active' : '' ?>">Study Groups</a>
            <a href="/search?q=<?= urlencode($query) ?>&type=jobs" class="filter-tab <?= $type === 'jobs' ? 'active' : '' ?>">Jobs</a>
        </div>
    <?php endif; ?>

    <!-- Results -->
    <?php if (empty($query) || (empty($results['questions']) && empty($results['users']) && empty($results['groups']) && empty($results['jobs']))): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <h2 class="text-xl font-semibold text-white mb-2">
                <?= empty($query) ? 'Search EduSync' : 'No results found' ?>
            </h2>
            <p><?= empty($query) ? 'Enter a search term to find questions, people, groups, and jobs' : 'Try different keywords or filters' ?></p>
        </div>
    <?php else: ?>
        <!-- Questions -->
        <?php if (!empty($results['questions'])): ?>
            <div class="result-section">
                <div class="section-header">
                    <i data-lucide="help-circle" style="width: 16px; height: 16px;"></i>
                    Questions
                    <span class="section-count"><?= count($results['questions']) ?></span>
                </div>
                <?php foreach ($results['questions'] as $q): ?>
                    <div class="result-item question-result">
                        <div class="result-title">
                            <a href="/question-bank/<?= (int)$q['id'] ?>">
                                <?= htmlspecialchars($q['title']) ?>
                            </a>
                        </div>
                        <div class="result-meta">
                            <span>👤 <?= htmlspecialchars($q['author_name'] ?? 'Anonymous') ?></span>
                            <span>📅 <?= timeAgo($q['created_at']) ?></span>
                        </div>
                        <?php if ($q['description']): ?>
                            <div class="result-snippet">
                                <?= htmlspecialchars(substr($q['description'], 0, 150)) ?>...
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($pagination['questions']->hasNext()): ?>
                    <div class="pagination">
                        <a href="/search?q=<?= urlencode($query) ?>&type=questions&page=<?= $pagination['questions']->getNextPage() ?>">Load More →</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Users -->
        <?php if (!empty($results['users'])): ?>
            <div class="result-section">
                <div class="section-header">
                    <i data-lucide="users" style="width: 16px; height: 16px;"></i>
                    People
                    <span class="section-count"><?= count($results['users']) ?></span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
                    <?php foreach ($results['users'] as $u): ?>
                        <div class="result-item user-result">
                            <div style="display: flex; gap: 15px;">
                                <img src="<?= avatarUrl($u['avatar'] ?? '', $u['name']) ?>" alt="<?= htmlspecialchars($u['name']) ?>" style="width: 50px; height: 50px; border-radius: 8px;">
                                <div style="flex: 1;">
                                    <div class="result-title" style="margin-bottom: 5px;">
                                        <a href="/profile/<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name']) ?></a>
                                    </div>
                                    <div class="result-meta" style="margin-bottom: 0;">
                                        <span><?= htmlspecialchars($u['student_id'] ?? 'N/A') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Study Groups -->
        <?php if (!empty($results['groups'])): ?>
            <div class="result-section">
                <div class="section-header">
                    <i data-lucide="users-square" style="width: 16px; height: 16px;"></i>
                    Study Groups
                    <span class="section-count"><?= count($results['groups']) ?></span>
                </div>
                <?php foreach ($results['groups'] as $g): ?>
                    <div class="result-item group-result">
                        <div class="result-title">
                            <a href="/groups/<?= (int)$g['id'] ?>">
                                <?= htmlspecialchars($g['name']) ?>
                            </a>
                        </div>
                        <div class="result-meta">
                            <span>👥 <?= (int)$g['member_count'] ?> / <?= (int)$g['max_members'] ?> members</span>
                        </div>
                        <?php if ($g['description']): ?>
                            <div class="result-snippet">
                                <?= htmlspecialchars(substr($g['description'], 0, 200)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Jobs -->
        <?php if (!empty($results['jobs'])): ?>
            <div class="result-section">
                <div class="section-header">
                    <i data-lucide="briefcase" style="width: 16px; height: 16px;"></i>
                    Jobs & Internships
                    <span class="section-count"><?= count($results['jobs']) ?></span>
                </div>
                <?php foreach ($results['jobs'] as $j): ?>
                    <div class="result-item job-result">
                        <div class="result-title">
                            <a href="/jobs/<?= (int)$j['id'] ?>">
                                <?= htmlspecialchars($j['title']) ?>
                            </a>
                        </div>
                        <div class="result-meta">
                            <span>🏢 <?= htmlspecialchars($j['company']) ?></span>
                            <span>📌 <?= htmlspecialchars($j['job_type']) ?></span>
                            <span>📅 <?= timeAgo($j['posted_date']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
