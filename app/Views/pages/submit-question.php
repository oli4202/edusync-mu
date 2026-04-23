<?php $currentPage = 'question-bank'; ?>

<style>
    .submit-container { max-width: 600px; margin: 0 auto; padding: 40px 20px; }
    .submit-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 32px; }
    .submit-title { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800; margin-bottom: 8px; }
    .submit-sub { font-size: 14px; color: var(--muted); margin-bottom: 24px; line-height: 1.6; }
    .field { margin-bottom: 20px; }
</style>

<div class="submit-container">
    <div class="submit-card">
        <div class="submit-title">Submit Exam Question</div>
        <p class="submit-sub">Help build the MU SWE question bank. Submit questions from mid-terms, finals, or class tests.</p>

        <?php if (!empty($error)): ?>
            <div style="margin-bottom:16px;padding:12px 14px;border-radius:12px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="/question-bank/submit" method="POST">
            <div class="field">
                <label>Course Code *</label>
                <input type="text" name="course_code" list="courseCodeList" placeholder="e.g. SWE-123" value="<?= htmlspecialchars($old['course_code'] ?? '') ?>" required>
                <datalist id="courseCodeList">
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course['code']) ?>"><?= htmlspecialchars($course['name']) ?></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="field">
                <label>Question Text *</label>
                <textarea name="question_text" rows="6" placeholder="Type the question exactly as it appeared in the exam paper..." required><?= htmlspecialchars($old['question_text'] ?? '') ?></textarea>
            </div>
            <div style="display:flex;gap:12px;margin-top:10px;">
                <button type="submit" class="btn btn-primary">Submit Question</button>
                <a href="/question-bank" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
