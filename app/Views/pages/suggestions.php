<?php $currentPage = 'ai'; ?>

<style>
.suggest-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; margin-bottom:24px; transition:all .2s; }
.suggest-card:hover { border-color:var(--accent); }
.suggest-title { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; margin-bottom:12px; display:flex; align-items:center; gap:10px; }
.suggest-content { font-size:14px; color:#cbd5e1; line-height:1.8; white-space:pre-wrap; }
.topic-pills { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:24px; }
.topic-pill { background:var(--card2); border:1px solid var(--border); padding:8px 16px; border-radius:20px; font-size:13px; cursor:pointer; transition:all .2s; }
.topic-pill:hover { border-color:var(--accent); color:var(--accent); }
</style>

<div class="topbar">
    <div>
        <div class="page-title">💡 AI Study Suggestions</div>
        <div class="page-sub">Personalized study plans and exam preparation tips</div>
    </div>
    <button class="btn btn-primary" onclick="generateGeneralTips()">✨ Generate Fresh Tips</button>
</div>

<div class="topic-pills">
    <div class="topic-pill" onclick="getTopicTips('Last Minute Exam Prep')">📝 Last Minute Exam Prep</div>
    <div class="topic-pill" onclick="getTopicTips('How to master Data Structures')">🌳 Data Structures Mastery</div>
    <div class="topic-pill" onclick="getTopicTips('Internship Preparation for SE')">💼 Internship Prep</div>
    <div class="topic-pill" onclick="getTopicTips('Time Management for University')">⏰ Time Management</div>
    <div class="topic-pill" onclick="getTopicTips('Modern Tech Stack for 2026')">🛠️ Modern Tech Stack</div>
</div>

<div id="suggestionsList">
    <div class="suggest-card">
        <div class="suggest-title">⚡ High-Impact Study Tip</div>
        <div class="suggest-content" id="mainSuggest">Click a topic above to get personalized AI study suggestions for your Software Engineering journey at Metropolitan University.</div>
    </div>
</div>

<script>
async function getTopicTips(topic) {
    const main = document.getElementById('mainSuggest');
    main.innerHTML = '<div class="spinner-sm"></div> AI is thinking...';
    
    try {
        const resp = await fetch('/api/ai/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                prompt: `Topic: ${topic}. Give 5 specific, actionable study tips for a SE student at Metropolitan University.`,
                system: "You are an academic coach. Be concise and practical."
            })
        });
        const data = await resp.json();
        main.textContent = data.text || 'Error.';
    } catch (e) {
        main.textContent = 'Failed to connect to AI.';
    }
}

function generateGeneralTips() {
    getTopicTips('General Academic Success');
}
</script>
