<?php $pageTitle = 'API Settings — EduSync Admin'; ?>

<style>
.form-card-admin{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:24px;max-width:600px;margin:0 auto;}
.form-group-admin{margin-bottom:20px;}
.label-admin{font-family:'Syne',sans-serif;font-size:14px;font-weight:600;margin-bottom:8px;display:block;}
.input-admin{width:100%;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:12px;font-size:14px;color:var(--text);outline:none;}
.input-admin:focus{border-color:var(--accent);}
</style>

<div style="margin-bottom: 20px;">
    <a href="/admin" class="btn btn-outline btn-sm">← Back to Admin Panel</a>
</div>

<div class="form-card-admin">
    <?php if (isset($message) && $message): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group-admin">
            <label class="label-admin" for="claude">Claude API Key</label>
            <input type="text" id="claude" name="claude" class="input-admin" value="<?= htmlspecialchars($api_keys['CLAUDE_API_KEY'] ?? '') ?>" placeholder="Enter Claude API Key">
        </div>
        <div class="form-group-admin">
            <label class="label-admin" for="gemini">Gemini API Key</label>
            <input type="text" id="gemini" name="gemini" class="input-admin" value="<?= htmlspecialchars($api_keys['GEMINI_API_KEY'] ?? '') ?>" placeholder="Enter Gemini API Key">
        </div>
        <div class="form-group-admin">
            <label class="label-admin" for="hf">Hugging Face API Key</label>
            <input type="text" id="hf" name="hf" class="input-admin" value="<?= htmlspecialchars($api_keys['HF_API_KEY'] ?? '') ?>" placeholder="Enter Hugging Face API Key">
        </div>
        <button type="submit" class="btn btn-primary">Save API Keys</button>
    </form>
</div>
