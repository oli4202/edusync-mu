<?php $pageTitle = 'AI Study Assistant — EduSync'; ?>

<style>
:root {
    --glass: rgba(255, 255, 255, 0.03);
    --glass-border: rgba(255, 255, 255, 0.08);
}

.study-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.hero-section {
    text-align: center;
    margin-bottom: 40px;
    padding: 60px 20px;
    background: radial-gradient(circle at center, rgba(34, 211, 238, 0.1) 0%, transparent 70%);
    border-radius: 24px;
}

.drop-zone {
    background: var(--glass);
    border: 2px dashed var(--glass-border);
    border-radius: 20px;
    padding: 60px 40px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.drop-zone:hover, .drop-zone.drag-over {
    border-color: var(--accent);
    background: rgba(34, 211, 238, 0.05);
    transform: translateY(-2px);
}

.drop-zone i {
    font-size: 48px;
    color: var(--accent);
    margin-bottom: 20px;
    display: block;
}

.drop-zone p {
    color: var(--text-muted);
    font-size: 16px;
    margin-bottom: 10px;
}

.drop-zone .browse-btn {
    color: var(--accent);
    font-weight: 600;
    text-decoration: underline;
}

.analysis-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 30px;
    margin-top: 30px;
    display: none;
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.scanning-bar {
    height: 4px;
    background: var(--accent);
    position: absolute;
    top: 0;
    left: 0;
    width: 0;
    transition: width 0.3s ease;
    box-shadow: 0 0 15px var(--accent);
}

.file-preview {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: rgba(255,255,255,0.02);
    border-radius: 12px;
    margin-bottom: 20px;
}

.chat-input-container {
    position: relative;
    margin-top: 30px;
}

.chat-input {
    width: 100%;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 15px 60px 15px 20px;
    color: white;
    outline: none;
}

.chat-input:focus { border-color: var(--accent); }

.send-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--accent);
    color: black;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.markdown-content h3 { color: var(--accent); margin-top: 20px; margin-bottom: 10px; }
.markdown-content ul { padding-left: 20px; margin-bottom: 15px; }
.markdown-content li { margin-bottom: 8px; color: #cbd5e1; line-height: 1.6; }
</style>

<div class="study-container">
    <div class="hero-section">
        <h1 class="font-syne text-4xl font-bold mb-4 text-white">AI Study Assistant</h1>
        <p class="text-slate-400 text-lg">Upload any Image or PDF and let AI explain it to you in seconds.</p>
    </div>

    <div id="dropZone" class="drop-zone">
        <div id="scanningBar" class="scanning-bar"></div>
        <i class="fas fa-file-pdf"></i>
        <p>Drag & drop your files here or <span class="browse-btn">browse</span></p>
        <p class="text-xs">Supports: JPG, PNG, PDF (Max 10MB)</p>
        <input type="file" id="fileInput" hidden accept=".pdf, .jpg, .jpeg, .png">
    </div>

    <div id="analysisCard" class="analysis-card">
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-syne text-xl font-bold text-white">AI Analysis</h2>
            <button onclick="resetTool()" class="btn btn-outline btn-sm">Upload New</button>
        </div>

        <div id="fileInfo" class="file-preview">
            <i class="fas fa-file-alt text-accent text-2xl"></i>
            <div>
                <p id="fileName" class="text-white font-medium mb-0"></p>
                <p id="fileSize" class="text-slate-500 text-xs mb-0"></p>
            </div>
        </div>

        <div id="analysisResult" class="markdown-content text-slate-300 leading-relaxed">
            <!-- AI Output will appear here -->
        </div>

        <div class="chat-input-container">
            <input type="text" id="followUpInput" class="chat-input" placeholder="Ask a follow-up question about this document...">
            <button onclick="askFollowUp()" class="send-btn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const analysisCard = document.getElementById('analysisCard');
const analysisResult = document.getElementById('analysisResult');
const scanningBar = document.getElementById('scanningBar');

let currentFileData = null;
let currentFileName = null;

dropZone.onclick = () => fileInput.click();

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('drag-over');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('drag-over');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) handleFile(file);
});

fileInput.onchange = (e) => {
    const file = e.target.files[0];
    if (file) handleFile(file);
};

async function handleFile(file) {
    if (file.size > 10 * 1024 * 1024) {
        alert('File size exceeds 10MB limit.');
        return;
    }

    currentFileName = file.name;
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';

    const reader = new FileReader();
    reader.onload = async (e) => {
        currentFileData = e.target.result;
        startAnalysis();
    };
    reader.readAsDataURL(file);
}

async function startAnalysis(prompt = '') {
    dropZone.style.pointerEvents = 'none';
    scanningBar.style.width = '100%';
    
    analysisResult.innerHTML = '<div class="flex items-center gap-3 text-accent"><i class="fas fa-spinner fa-spin"></i> AI is studying your document...</div>';
    analysisCard.style.display = 'block';
    
    // Smooth scroll to results
    analysisCard.scrollIntoView({ behavior: 'smooth' });

    try {
        const response = await fetch('/api/ai/analyze-file', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                file: currentFileData,
                name: currentFileName,
                prompt: prompt
            })
        });

        const data = await response.json();
        
        if (data.success) {
            analysisResult.innerHTML = marked.parse(data.text);
            dropZone.style.display = 'none';
        } else {
            analysisResult.innerHTML = `<div class="text-red-400">❌ Error: ${data.message}</div>`;
        }
    } catch (err) {
        analysisResult.innerHTML = `<div class="text-red-400">❌ Failed to connect to AI server.</div>`;
    } finally {
        scanningBar.style.width = '0';
        dropZone.style.pointerEvents = 'auto';
    }
}

async function askFollowUp() {
    const input = document.getElementById('followUpInput');
    const prompt = input.value.trim();
    if (!prompt) return;

    input.value = '';
    const originalContent = analysisResult.innerHTML;
    analysisResult.innerHTML += `<div class="mt-8 pt-8 border-t border-slate-800"><p class="text-accent mb-2"><strong>Follow-up:</strong> ${prompt}</p><div id="followUpLoading" class="flex items-center gap-3 text-slate-500"><i class="fas fa-spinner fa-spin"></i> Thinking...</div></div>`;
    
    try {
        const response = await fetch('/api/ai/analyze-file', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                file: currentFileData,
                name: currentFileName,
                prompt: `About this document: ${prompt}`
            })
        });

        const data = await response.json();
        document.getElementById('followUpLoading').remove();
        
        if (data.success) {
            analysisResult.innerHTML += `<div class="bg-slate-900/50 p-4 rounded-lg mt-2 text-slate-300">${marked.parse(data.text)}</div>`;
        } else {
            analysisResult.innerHTML += `<div class="text-red-400 mt-2">❌ ${data.message}</div>`;
        }
    } catch (err) {
        document.getElementById('followUpLoading').innerHTML = '❌ Error';
    }
}

function resetTool() {
    analysisCard.style.display = 'none';
    dropZone.style.display = 'block';
    fileInput.value = '';
    currentFileData = null;
    currentFileName = null;
}

// Support Enter key for follow-up
document.getElementById('followUpInput').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') askFollowUp();
});
</script>
