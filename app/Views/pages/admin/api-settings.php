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
    <div style="margin-bottom: 24px; text-align: center;">
        <h2 class="font-syne text-xl font-bold text-white">AI Configuration</h2>
        <p class="text-xs text-slate-500 mt-1">Configure your API keys to power all AI features</p>
    </div>

    <?php if (isset($message) && $message): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">✅ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group-admin">
            <label class="label-admin" for="groq">Groq API Key (Main Text Engine)</label>
            <input type="text" id="groq" name="groq" class="input-admin" value="<?= htmlspecialchars($api_keys['GROQ_API_KEY'] ?? '') ?>" placeholder="gsk_...">
            <small style="color: var(--muted); font-size: 11px; margin-top: 8px; display: block; line-height: 1.4;">
                Get your free API key from the <a href="https://console.groq.com/keys" target="_blank" style="color: var(--accent);">Groq Console</a>. 
            </small>
        </div>

        <div class="form-group-admin">
            <label class="label-admin" for="gemini">Gemini API Key (Backup Engine)</label>
            <input type="text" id="gemini" name="gemini" class="input-admin" value="<?= htmlspecialchars($api_keys['GEMINI_API_KEY'] ?? '') ?>" placeholder="AIzaSy...">
            <small style="color: var(--muted); font-size: 11px; margin-top: 8px; display: block; line-height: 1.4;">
                Get your free Gemini API key from <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color: var(--accent);">Google AI Studio</a>.
            </small>
        </div>

        <div class="form-group-admin">
            <label class="label-admin">Preferred Vision Model (for Reading Images)</label>
            <div style="display: flex; gap: 20px; margin-top: 10px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="radio" name="preferred_vision" value="groq" <?= ($api_keys['PREFERRED_VISION_MODEL'] ?? 'groq') === 'groq' ? 'checked' : '' ?>>
                    <span style="font-size: 14px; color: white;">Groq (Fastest)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="radio" name="preferred_vision" value="gemini" <?= ($api_keys['PREFERRED_VISION_MODEL'] ?? 'groq') === 'gemini' ? 'checked' : '' ?>>
                    <span style="font-size: 14px; color: white;">Gemini (Most Accurate)</span>
                </label>
            </div>
            <small style="color: var(--muted); font-size: 11px; margin-top: 12px; display: block; line-height: 1.4;">
                This model will be used when you click "Read Aloud" on an image question.
            </small>
        </div>
        
        <button type="submit" class="btn btn-primary w-full" style="margin-top: 20px;">
            Save & Update Engine
        </button>
    </form>
</div>
