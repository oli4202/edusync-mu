<?php
// pages/playground.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$currentPage = 'playground';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Code Playground — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/material-ocean.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
:root {
    --bg: #020617;
    --card: rgba(15, 23, 42, 0.4);
    --card2: rgba(30, 41, 59, 0.6);
    --border: rgba(255, 255, 255, 0.08);
    --accent: #22d3ee;
    --accent2: #818cf8;
    --text: #f8fafc;
    --muted: #94a3b8;
    --glass: rgba(15, 23, 42, 0.4);
}
body { 
    overflow: hidden; 
    font-family: 'Outfit', sans-serif;
    background: var(--bg);
}
#three-bg {
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    z-index: -1;
    pointer-events: none;
}
.main { 
    margin-left: 240px; 
    background: transparent !important;
    backdrop-filter: blur(10px);
}
.playground-layout { 
    display: grid; 
    grid-template-columns: 240px 1fr 340px; 
    height: calc(100vh - 64px); 
    gap: 0; 
}
.lang-panel { 
    background: var(--glass); 
    border-right: 1px solid var(--border); 
    padding: 16px 0; 
    overflow-y: auto; 
    backdrop-filter: blur(15px);
}
.lang-item { 
    display: flex; 
    align-items: center; 
    gap: 12px; 
    padding: 12px 20px; 
    cursor: pointer; 
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 14px; 
    color: var(--muted);
    border-radius: 12px;
    margin: 4px 12px;
}
.lang-item:hover, .lang-item.active { 
    color: var(--text); 
    background: rgba(34, 211, 238, 0.1); 
    transform: perspective(1000px) translate3d(5px, 0, 10px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}
.lang-item.active {
    border-left: 3px solid var(--accent);
    color: var(--accent);
}
.editor-panel { 
    display: flex; 
    flex-direction: column; 
    background: rgba(0, 0, 0, 0.2); 
    border-right: 1px solid var(--border);
}
.editor-topbar { 
    display: flex; 
    align-items: center; 
    justify-content: space-between; 
    padding: 12px 20px; 
    background: rgba(15, 23, 42, 0.8); 
    border-bottom: 1px solid var(--border); 
}
.run-btn { 
    background: linear-gradient(135deg, var(--accent), var(--accent2)); 
    border: none; 
    border-radius: 10px; 
    padding: 10px 24px; 
    color: #0f172a; 
    font-weight: 800; 
    font-family: 'Syne', sans-serif; 
    font-size: 14px; 
    cursor: pointer; 
    transition: all 0.3s; 
    display: flex; 
    align-items: center; 
    gap: 8px;
    box-shadow: 0 4px 15px rgba(34, 211, 238, 0.3);
}
.run-btn:hover { 
    transform: scale(1.05) translateY(-2px); 
    box-shadow: 0 8px 25px rgba(34, 211, 238, 0.5); 
}
.CodeMirror { 
    height: 100% !important; 
    font-size: 15px !important; 
    background: #0f172a !important; /* Ensure a dark but readable base */
    color: #f8fafc !important;
}
.editor-wrap {
    flex: 1;
    overflow: hidden;
    border: 1px solid var(--border);
    margin: 10px;
    border-radius: 12px;
    background: #0f172a;
    box-shadow: inset 0 2px 10px rgba(0,0,0,0.3);
}

.output-panel { 
    background: var(--glass); 
    backdrop-filter: blur(20px);
    display: flex; 
    flex-direction: column; 
}
.output-tab { 
    padding: 14px 20px; 
    font-size: 13px; 
    font-weight: 700; 
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.glass-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 16px;
    transition: transform 0.4s;
}
.glass-card:hover {
    transform: perspective(1000px) rotateX(2deg) rotateY(-2deg);
    background: rgba(255, 255, 255, 0.06);
}
.sql-table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; background: rgba(0,0,0,0.2); }
.sql-table th { background: rgba(34, 211, 238, 0.1); color: var(--accent); text-align: left; padding: 6px 10px; border: 1px solid var(--border); }
.sql-table td { padding: 6px 10px; border: 1px solid var(--border); color: var(--text); }
.output-line-err { color: #ff5555; font-family: monospace; white-space: pre-wrap; margin-bottom: 4px; }
.output-line-info { color: #8be9fd; font-style: italic; margin-bottom: 4px; }
.output-line-ok { color: #50fa7b; font-weight: 600; margin-bottom: 4px; }
</style>
</head>
<body>
<canvas id="three-bg"></canvas>
<?php include '../includes/sidebar.php'; ?>
<main class="main" style="padding:0;margin-left:240px;height:100vh;display:flex;flex-direction:column;">
    <div style="padding:14px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);background:var(--card2);">
        <div style="font-family:'Syne',sans-serif;font-size:18px;font-weight:800;">⚡ Code Playground</div>
        <div style="font-size:12px;color:var(--muted);">Run code in browser — Python, JS, C, Java & more</div>
    </div>

    <div class="playground-layout" style="flex:1;">
        <!-- Left Sidebar Panel -->
        <div class="lang-panel">
            <div class="lang-section" style="color:var(--accent);">External Tools</div>
            <div class="lang-item" onclick="loadExternalCompiler('https://www.programiz.com/c-programming/online-compiler/', this)" style="cursor:pointer;"><span class="lang-icon">🌐</span>Programiz C Compiler</div>
            <div class="lang-item" onclick="loadExternalCompiler('https://www.jdoodle.com/online-java-compiler/', this)" style="cursor:pointer;"><span class="lang-icon">🌐</span>JDoodle Java Compiler</div>
            <div class="lang-item" onclick="loadExternalCompiler('https://www.onlinegdb.com/', this)" style="cursor:pointer;"><span class="lang-icon">🌐</span>OnlineGDB</div>
        </div>

        <!-- Iframe Panel for External Compilers -->
        <div id="iframePanel" style="grid-column: 2 / 4; background:rgba(0,0,0,0.3); flex-direction:column; display:none; border-left:1px solid var(--border);">
            <div style="padding:10px 16px; background:var(--card2); border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:13px; font-weight:600; color:var(--text);"><span class="lang-icon">🌐</span> External Tool</span>
                <button class="btn btn-outline" style="padding:4px 12px; font-size:12px;" onclick="document.getElementById('iframePanel').style.display='none'">Close</button>
            </div>
            <iframe id="extCompiler" src="" style="width:100%; height:100%; flex:1; border:none; background:white;"></iframe>
        </div>

<script>
function loadExternalCompiler(url, el) {
    document.querySelectorAll('.lang-item').forEach(i => i.classList.remove('active'));
    if (el) el.classList.add('active');
    
    const iframePanel = document.getElementById('iframePanel');
    const iframe = document.getElementById('extCompiler');
    if (iframePanel && iframe) {
        iframe.src = url;
        iframePanel.style.display = 'flex';
    }
}
</script>
</body>
</html>
