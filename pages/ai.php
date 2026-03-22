<?php
// pages/ai.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$currentPage = 'ai';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Assistant — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.ai-layout { display:grid; grid-template-columns:280px 1fr; gap:20px; height:calc(100vh - 120px); }
.tool-list { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px; overflow-y:auto; }
.tool-item { display:flex; align-items:center; gap:10px; padding:11px 14px; border-radius:10px; cursor:pointer; transition:all .2s; margin-bottom:4px; border:1px solid transparent; }
.tool-item:hover { background:rgba(34,211,238,.06); }
.tool-item.active { background:rgba(34,211,238,.1); border-color:rgba(34,211,238,.3); }
.tool-icon { font-size:20px; flex-shrink:0; }
.tool-name { font-size:13px; font-weight:600; }
.tool-desc-short { font-size:11px; color:var(--muted); margin-top:1px; }
.tool-section { font-size:10px; color:var(--muted); text-transform:uppercase; letter-spacing:1px; padding:10px 14px 6px; }
.ai-panel { background:var(--card); border:1px solid var(--border); border-radius:14px; display:flex; flex-direction:column; overflow:hidden; }
.ai-panel-header { padding:20px 24px; border-bottom:1px solid var(--border); }
.ai-panel-title { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; }
.ai-panel-sub { font-size:13px; color:var(--muted); margin-top:4px; }
.ai-panel-body { flex:1; padding:24px; overflow-y:auto; display:flex; flex-direction:column; gap:16px; }
.ai-panel-footer { padding:16px 24px; border-top:1px solid var(--border); }
.input-area { background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:12px; padding:14px; }
.input-area textarea { background:transparent; border:none; outline:none; width:100%; resize:none; color:var(--text); font-size:14px; font-family:inherit; min-height:80px; }
.input-area textarea:focus { box-shadow:none; }
.input-footer { display:flex; align-items:center; justify-content:space-between; margin-top:10px; }
.char-count { font-size:12px; color:var(--muted); }
.ai-result { background:rgba(34,211,238,.05); border:1px solid rgba(34,211,238,.15); border-radius:12px; padding:20px; font-size:14px; line-height:1.9; white-space:pre-wrap; display:none; animation:fadeIn .3s ease; }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
.ai-result-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.ai-result-label { font-size:11px; color:var(--accent); text-transform:uppercase; letter-spacing:.5px; font-weight:700; }
.ai-result-content { font-size:14px; line-height:1.9; white-space:pre-wrap; }
.chat-messages { flex:1; display:flex; flex-direction:column; gap:14px; overflow-y:auto; }
.msg { max-width:80%; padding:14px 18px; border-radius:14px; font-size:14px; line-height:1.7; }
.msg-user { background:linear-gradient(135deg,rgba(34,211,238,.15),rgba(129,140,248,.15)); border:1px solid rgba(34,211,238,.2); align-self:flex-end; }
.msg-ai { background:var(--card2); border:1px solid var(--border); align-self:flex-start; white-space:pre-wrap; }
.msg-thinking { color:var(--accent); font-style:italic; font-size:13px; }
.copy-btn { font-size:11px; background:rgba(34,211,238,.1); border:1px solid rgba(34,211,238,.2); color:var(--accent); padding:4px 10px; border-radius:6px; cursor:pointer; border-style:solid; }
.copy-btn:hover { background:rgba(34,211,238,.2); }
.tool-extra { background:rgba(255,255,255,.02); border:1px solid var(--border); border-radius:10px; padding:14px; }
.tool-extra label { font-size:12px; color:var(--muted); display:block; margin-bottom:6px; }
.tool-extra input, .tool-extra select { background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:8px; padding:9px 12px; color:var(--text); font-size:13px; font-family:inherit; outline:none; width:100%; margin-bottom:10px; }
.tool-extra input:focus,.tool-extra select:focus { border-color:var(--accent); }
.tool-extra select option { background:var(--card); }
@media(max-width:900px){.ai-layout{grid-template-columns:1fr;height:auto;}.tool-list{display:flex;gap:8px;flex-wrap:wrap;padding:12px;}.tool-item{flex-direction:column;text-align:center;padding:10px;min-width:80px;}.tool-desc-short{display:none;}.sidebar{display:none;}.main{margin-left:0;}}
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main" style="padding:24px 32px;">
    <div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;">
        <div>
            <div class="page-title">🤖 AI Study Assistant</div>
            <div style="color:var(--muted);font-size:13px;margin-top:2px;">Powered by Gemini AI — 7 specialized tools for MU Sylhet SE students</div>
        </div>
    </div>

    <div class="ai-layout">
        <!-- Tool Sidebar -->
        <div class="tool-list">
            <div class="tool-section">Study Tools</div>
            <div class="tool-item active" onclick="setTool('chat',this)" data-tool="chat">
                <div class="tool-icon">💬</div>
                <div><div class="tool-name">AI Chat</div><div class="tool-desc-short">Ask anything</div></div>
            </div>
            <div class="tool-item" onclick="setTool('compact',this)" data-tool="compact">
                <div class="tool-icon">📋</div>
                <div><div class="tool-name">Compact Answer</div><div class="tool-desc-short">Exam-ready answers</div></div>
            </div>
            <div class="tool-item" onclick="setTool('flashcard',this)" data-tool="flashcard">
                <div class="tool-icon">🃏</div>
                <div><div class="tool-name">Flashcard Generator</div><div class="tool-desc-short">From your notes</div></div>
            </div>
            <div class="tool-item" onclick="setTool('quiz',this)" data-tool="quiz">
                <div class="tool-icon">❓</div>
                <div><div class="tool-name">Quiz Generator</div><div class="tool-desc-short">Test yourself</div></div>
            </div>
            <div class="tool-section">Planning</div>
            <div class="tool-item" onclick="setTool('plan',this)" data-tool="plan">
                <div class="tool-icon">📅</div>
                <div><div class="tool-name">Study Plan</div><div class="tool-desc-short">Day-by-day schedule</div></div>
            </div>
            <div class="tool-item" onclick="setTool('breakdown',this)" data-tool="breakdown">
                <div class="tool-icon">🔧</div>
                <div><div class="tool-name">Task Breakdown</div><div class="tool-desc-short">Split assignments</div></div>
            </div>
            <div class="tool-section">Content</div>
            <div class="tool-item" onclick="setTool('summarize',this)" data-tool="summarize">
                <div class="tool-icon">📝</div>
                <div><div class="tool-name">Note Summarizer</div><div class="tool-desc-short">Condense long notes</div></div>
            </div>
        </div>

        <!-- AI Panel -->
        <div class="ai-panel">
            <div class="ai-panel-header">
                <div class="ai-panel-title" id="toolTitle">💬 AI Chat Assistant</div>
                <div class="ai-panel-sub" id="toolSub">Ask any academic question about your SE courses at MU Sylhet</div>
            </div>
            <div class="ai-panel-body" id="panelBody">
                <!-- Chat view -->
                <div id="chatView">
                    <div class="chat-messages" id="chatMessages">
                        <div class="msg msg-ai">👋 Hi! I'm your AI study assistant for MU Sylhet Software Engineering. Ask me anything about your courses, get explanations, solve problems, or prepare for exams!</div>
                    </div>
                </div>
                <!-- Other tools view -->
                <div id="toolView" style="display:none;">
                    <div id="toolExtras"></div>
                    <div class="input-area">
                        <textarea id="toolInput" placeholder="Enter your text here..."></textarea>
                        <div class="input-footer">
                            <span class="char-count" id="charCount">0 characters</span>
                        </div>
                    </div>
                    <div class="loading" id="toolLoading" style="margin-top:12px;"><div class="spinner"></div> <span id="loadingText">AI is working...</span></div>
                    <div class="ai-result" id="toolResult">
                        <div class="ai-result-header">
                            <span class="ai-result-label" id="resultLabel">Result</span>
                            <button class="copy-btn" onclick="copyResult()">📋 Copy</button>
                        </div>
                        <div class="ai-result-content" id="resultContent"></div>
                    </div>
                </div>
            </div>
            <div class="ai-panel-footer">
                <!-- Chat footer -->
                <div id="chatFooter">
                    <div class="input-area">
                        <textarea id="chatInput" placeholder="Ask a question..." rows="2" onkeydown="handleChatKey(event)"></textarea>
                        <div class="input-footer">
                            <span class="char-count">Press Enter to send, Shift+Enter for new line</span>
                            <button class="btn btn-primary btn-sm" onclick="sendChat()">Send →</button>
                        </div>
                    </div>
                </div>
                <!-- Tool footer -->
                <div id="toolFooter" style="display:none;">
                    <button class="btn btn-primary" id="runToolBtn" onclick="runTool()" style="width:100%">✨ Generate</button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
let currentTool = 'chat';
let chatHistory = [];

const tools = {
    chat:      { title:'💬 AI Chat Assistant',      sub:'Ask any academic question about your SE courses at MU Sylhet', placeholder:'Ask a question about your course...', loadText:'Thinking...', label:'Answer', extras:'' },
    compact:   { title:'📋 Compact Answer Generator', sub:'Paste a question → get a concise, exam-ready answer in 10 lines max', placeholder:'Paste your exam question here...', loadText:'Writing compact answer...', label:'📋 Compact Answer',
        extras:`<div class="tool-extra" style="margin-bottom:16px;"><label>Full Answer (optional — paste for better results)</label><textarea id="extraAns" placeholder="Paste your full answer here for AI to compact..." style="background:transparent;border:none;outline:none;width:100%;resize:vertical;color:var(--text);font-size:13px;font-family:inherit;min-height:60px;"></textarea></div>` },
    flashcard: { title:'🃏 Flashcard Generator',     sub:'Paste your notes → AI generates Q&A flashcards instantly', placeholder:'Paste your lecture notes or textbook content here...', loadText:'Generating flashcards...', label:'🃏 Flashcards' },
    quiz:      { title:'❓ Quiz Generator',           sub:'Paste notes → AI creates a mini exam to test yourself', placeholder:'Paste the topic or notes to generate a quiz from...', loadText:'Creating quiz questions...', label:'❓ Quiz',
        extras:`<div class="tool-extra" style="margin-bottom:16px;"><label>Number of Questions</label><select id="quizCount"><option value="5">5 questions</option><option value="10" selected>10 questions</option><option value="15">15 questions</option></select></div>` },
    plan:      { title:'📅 Study Plan Generator',    sub:'Enter subjects & exam date → get a personalized day-by-day plan', placeholder:'List your subjects (e.g. DSA, OOP, DBMS, OS, Networks)...', loadText:'Building your study plan...', label:'📅 Study Plan',
        extras:`<div class="tool-extra" style="margin-bottom:16px;"><div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"><div><label>Days Until Exam</label><input type="number" id="planDays" placeholder="e.g. 14" min="1" max="90"></div><div><label>Daily Hours</label><input type="number" id="planHours" placeholder="e.g. 4" min="1" max="12"></div></div></div>` },
    breakdown: { title:'🔧 Task Breakdown',          sub:'Paste an assignment description → AI splits it into actionable subtasks', placeholder:'Paste your assignment or project description here...', loadText:'Breaking down the task...', label:'🔧 Subtasks' },
    summarize: { title:'📝 Note Summarizer',         sub:'Paste long notes → AI creates a concise summary with key points', placeholder:'Paste your lecture notes, textbook chapter, or any long content...', loadText:'Summarizing your notes...', label:'📝 Summary' },
};

function setTool(tool, el) {
    currentTool = tool;
    document.querySelectorAll('.tool-item').forEach(t=>t.classList.remove('active'));
    el.classList.add('active');
    const t = tools[tool];
    document.getElementById('toolTitle').textContent = t.title;
    document.getElementById('toolSub').textContent   = t.sub;

    if (tool === 'chat') {
        document.getElementById('chatView').style.display  = 'flex';
        document.getElementById('toolView').style.display  = 'none';
        document.getElementById('chatFooter').style.display = 'block';
        document.getElementById('toolFooter').style.display = 'none';
    } else {
        document.getElementById('chatView').style.display  = 'none';
        document.getElementById('toolView').style.display  = 'block';
        document.getElementById('chatFooter').style.display = 'none';
        document.getElementById('toolFooter').style.display = 'block';
        document.getElementById('toolInput').placeholder = t.placeholder;
        document.getElementById('toolExtras').innerHTML   = t.extras || '';
        document.getElementById('toolResult').style.display = 'none';
        document.getElementById('loadingText').textContent = t.loadText;
        document.getElementById('resultLabel').textContent  = t.label;
        document.getElementById('toolInput').value = '';
        document.getElementById('charCount').textContent = '0 characters';
    }
}

document.getElementById('toolInput')?.addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length + ' characters';
});

async function callAI(prompt) {
    const resp = await fetch('../admin/ajax/ai-suggest.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body:JSON.stringify({prompt})
    });
    return await resp.json();
}

async function sendChat() {
    const input = document.getElementById('chatInput');
    const text  = input.value.trim();
    if (!text) return;
    input.value = '';

    const msgs = document.getElementById('chatMessages');
    msgs.innerHTML += `<div class="msg msg-user">${escHtml(text)}</div>`;
    const thinking = document.createElement('div');
    thinking.className = 'msg msg-ai msg-thinking';
    thinking.textContent = '⏳ Thinking...';
    msgs.appendChild(thinking);
    msgs.scrollTop = msgs.scrollHeight;

    chatHistory.push({role:'user', content:text});

    const systemPrompt = `You are a helpful academic assistant for Metropolitan University Sylhet, Bangladesh, Software Engineering department students. 
    Help with coursework, exam preparation, programming problems, algorithm explanations, and general study advice. 
    Be concise, clear, and use examples when helpful. Reference MU Sylhet SE curriculum when relevant.`;

    const historyFormatted = chatHistory.map(m=>m.role+': '+m.content).join('\n');
    const data = await callAI(systemPrompt + '\n\nConversation:\n' + historyFormatted);

    thinking.remove();
    const aiText = data.text || data.error || 'Sorry, I could not process that. Please try again.';
    chatHistory.push({role:'assistant', content:aiText});

    msgs.innerHTML += `<div class="msg msg-ai">${escHtml(aiText)}</div>`;
    msgs.scrollTop = msgs.scrollHeight;
}

function handleChatKey(e) {
    if (e.key==='Enter' && !e.shiftKey) { e.preventDefault(); sendChat(); }
}

async function runTool() {
    const text = document.getElementById('toolInput').value.trim();
    const t    = tools[currentTool];
    if (!text) { alert('Please enter some text first.'); return; }

    document.getElementById('toolLoading').style.display  = 'flex';
    document.getElementById('toolResult').style.display   = 'none';
    document.getElementById('runToolBtn').disabled = true;

    let prompt = '';
    const mu = 'Metropolitan University Sylhet, Software Engineering department';

    if (currentTool === 'compact') {
        const extra = document.getElementById('extraAns')?.value.trim() || '';
        prompt = `You are an exam assistant for ${mu}.\nQuestion: ${text}\n${extra?'Full Answer: '+extra+'\n\n':''}\nWrite a COMPACT exam-ready answer in max 10 lines. Use bullet points. Focus only on what an examiner wants.`;
    } else if (currentTool === 'flashcard') {
        prompt = `Create 8-10 Q&A flashcards for a ${mu} student from these notes:\n\n${text}\n\nFormat as:\nQ: [question]\nA: [concise answer]\n\n(repeat for each card)`;
    } else if (currentTool === 'quiz') {
        const count = document.getElementById('quizCount')?.value || 10;
        prompt = `Create a ${count}-question quiz for a ${mu} student based on:\n\n${text}\n\nFormat:\n1. [Question]\n   a) [option]\n   b) [option]\n   c) [option]\n   d) [option]\n   ✅ Answer: [letter] — [brief explanation]\n\n(for theoretical questions, use short answer format instead of MCQ)`;
    } else if (currentTool === 'plan') {
        const days  = document.getElementById('planDays')?.value || '14';
        const hours = document.getElementById('planHours')?.value || '4';
        prompt = `Create a ${days}-day study plan for a ${mu} student.\nSubjects: ${text}\nDaily hours available: ${hours}\n\nMake a day-by-day plan with specific topics per day. Include revision days at the end. Be realistic and specific.`;
    } else if (currentTool === 'breakdown') {
        prompt = `Break down this assignment for a ${mu} student into clear, actionable subtasks:\n\n${text}\n\nFormat as numbered steps with estimated time for each. Include any important tips or warnings.`;
    } else if (currentTool === 'summarize') {
        prompt = `Summarize these notes for a ${mu} student:\n\n${text}\n\nFormat as:\n📌 Key Points (bullet list)\n🧠 Core Concepts\n⚡ Quick Facts to Remember\n\nBe concise but complete.`;
    }

    const data = await callAI(prompt);
    document.getElementById('toolLoading').style.display  = 'none';
    document.getElementById('runToolBtn').disabled = false;

    const resultDiv = document.getElementById('toolResult');
    document.getElementById('resultContent').textContent = data.text || 'Could not generate. Check your API key in config/database.php.';
    resultDiv.style.display = 'block';
}

function copyResult() {
    const text = document.getElementById('resultContent').textContent;
    navigator.clipboard.writeText(text).then(()=>{ 
        const btn = document.querySelector('.copy-btn');
        btn.textContent = '✅ Copied!'; 
        setTimeout(()=>btn.textContent='📋 Copy', 2000);
    });
}

function escHtml(t) {
    return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
}
</script>
</body>
</html>
