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
                <label>Batch</label>
                <select id="batchSelect" class="form-control">
                    <option value="">Select Batch</option>
                    <?php foreach ($availableBatches as $batch): ?>
                        <option value="<?= htmlspecialchars($batch) ?>">Batch <?= htmlspecialchars($batch) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Semester</label>
                <select id="semesterSelect" class="form-control">
                    <option value="">Select batch first</option>
                </select>
            </div>
            <div class="field">
                <label>Course *</label>
                <select name="course_code" id="courseSelect" class="form-control" required>
                    <option value="">Select semester first</option>
                </select>
            </div>

            <script>
            document.getElementById('batchSelect').addEventListener('change', function() {
                const batch = this.value;
                const semSelect = document.getElementById('semesterSelect');
                const courseSelect = document.getElementById('courseSelect');
                
                semSelect.innerHTML = '<option value="">Loading...</option>';
                courseSelect.innerHTML = '<option value="">Select semester...</option>';

                if (!batch) {
                    semSelect.innerHTML = '<option value="">Select batch first</option>';
                    return;
                }

                fetch(`/api/courses/semesters?batch=${batch}`)
                    .then(res => res.json())
                    .then(data => {
                        semSelect.innerHTML = '<option value="">Select semester</option>';
                        data.forEach(sem => {
                            const opt = document.createElement('option');
                            opt.value = sem;
                            opt.textContent = `Semester ${sem}`;
                            semSelect.appendChild(opt);
                        });
                    });
            });

            document.getElementById('semesterSelect').addEventListener('change', function() {
                const batch = document.getElementById('batchSelect').value;
                const semester = this.value;
                const courseSelect = document.getElementById('courseSelect');

                if (!batch || !semester) {
                    courseSelect.innerHTML = '<option value="">Select semester first</option>';
                    return;
                }

                courseSelect.innerHTML = '<option value="">Loading courses...</option>';

                fetch(`/api/courses/filter?batch=${batch}&semester=${semester}`)
                    .then(res => res.json())
                    .then(data => {
                        courseSelect.innerHTML = '<option value="">Select course</option>';
                        data.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.code;
                            opt.textContent = `${c.code} — ${c.name}`;
                            courseSelect.appendChild(opt);
                        });
                    });
            });
            </script>
            <div class="field">
                <label>Question Text (Optional if image is provided)</label>
                <textarea name="question_text" id="question_text" rows="6" placeholder="Type the question or use AI to extract text from an image..."></textarea>
            </div>

            <div class="field">
                <label>Image (Paste from clipboard or drag & drop)</label>
                <div id="imagePasteArea" style="border: 2px dashed var(--border); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.2s; background: rgba(255,255,255,0.02);">
                    <div id="pastePlaceholder">
                        <i data-lucide="image" style="width: 32px; height: 32px; color: var(--muted); margin-bottom: 8px;"></i>
                        <p style="font-size: 13px; color: var(--muted);">Click here and paste (Ctrl+V) an image</p>
                    </div>
                    <div id="imagePreview" class="hidden" style="position: relative;">
                        <img id="previewImg" src="" style="max-width: 100%; border-radius: 8px; border: 1px solid var(--border);">
                        <button type="button" id="removeImage" style="position: absolute; top: -10px; right: -10px; background: #f87171; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: bold; box-shadow: 0 2px 10px rgba(0,0,0,0.5);">×</button>
                    </div>
                </div>
                
                <div id="ocrAction" class="hidden" style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
                    <button type="button" id="ocrBtn" class="btn btn-outline btn-sm" style="background: rgba(34, 211, 238, 0.1); border-color: rgba(34, 211, 238, 0.3); color: #22d3ee;">
                        <i data-lucide="wand-2" style="width: 14px; height: 14px; margin-right: 4px;"></i> Read Text using AI
                    </button>
                    <span id="ocrStatus" style="font-size: 11px; color: #94a3b8;"></span>
                </div>
                
                <input type="hidden" name="question_image" id="question_image">
            </div>

            <div style="display:flex;gap:12px;margin-top:10px;">
                <button type="submit" class="btn btn-primary">Submit Question</button>
                <a href="/question-bank" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    const pasteArea = document.getElementById('imagePasteArea');
    const imageInput = document.getElementById('question_image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const placeholder = document.getElementById('pastePlaceholder');
    const removeBtn = document.getElementById('removeImage');
    const ocrAction = document.getElementById('ocrAction');
    const ocrBtn = document.getElementById('ocrBtn');
    const ocrStatus = document.getElementById('ocrStatus');
    const qTextArea = document.getElementById('question_text');

    function handleImageData(base64) {
        imageInput.value = base64;
        previewImg.src = base64;
        imagePreview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        ocrAction.classList.remove('hidden');
        ocrAction.style.display = 'flex';
    }

    window.addEventListener('paste', (e) => {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (const item of items) {
            if (item.type.indexOf('image') !== -1) {
                const blob = item.getAsFile();
                const reader = new FileReader();
                reader.onload = (event) => {
                    handleImageData(event.target.result);
                };
                reader.readAsDataURL(blob);
            }
        }
    });

    ocrBtn.addEventListener('click', () => {
        const base64 = imageInput.value;
        if (!base64) return;

        ocrBtn.disabled = true;
        ocrStatus.textContent = 'AI is reading...';
        
        fetch('/api/ai/ocr', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image: base64 })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.text) {
                qTextArea.value = data.text;
                ocrStatus.textContent = 'Success!';
            } else {
                ocrStatus.textContent = 'Failed to extract text.';
            }
        })
        .catch(() => {
            ocrStatus.textContent = 'Error connecting to AI.';
        })
        .finally(() => {
            ocrBtn.disabled = false;
        });
    });

    removeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        imageInput.value = '';
        previewImg.src = '';
        imagePreview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        ocrAction.classList.add('hidden');
        ocrAction.style.display = 'none';
    });

    // Handle Drag & Drop
    pasteArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        pasteArea.style.borderColor = '#22d3ee';
    });

    pasteArea.addEventListener('dragleave', () => {
        pasteArea.style.borderColor = 'var(--border)';
    });

    pasteArea.addEventListener('drop', (e) => {
        e.preventDefault();
        pasteArea.style.borderColor = 'var(--border)';
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (event) => {
                handleImageData(event.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    lucide.createIcons();
</script>
