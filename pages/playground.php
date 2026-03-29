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
<title>Code Playground - EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
:root {
    --bg: #020617;
    --card: rgba(15, 23, 42, 0.72);
    --card2: rgba(30, 41, 59, 0.72);
    --border: rgba(255, 255, 255, 0.08);
    --accent: #22d3ee;
    --accent2: #818cf8;
    --text: #f8fafc;
    --muted: #94a3b8;
}
body { background: radial-gradient(circle at top left, rgba(34,211,238,.12), transparent 35%), linear-gradient(135deg, #020617, #0f172a); font-family:'Outfit', sans-serif; }
.main { background: transparent !important; }
.playground-layout { display:grid; grid-template-columns:260px 1fr 320px; gap:18px; min-height:calc(100vh - 130px); }
.side-panel, .viewer-panel, .info-panel { background:var(--card); border:1px solid var(--border); border-radius:18px; backdrop-filter:blur(18px); overflow:hidden; }
.side-panel { padding:18px 0; }
.section-title { color:var(--accent); padding:8px 20px; font-size:11px; text-transform:uppercase; font-weight:700; letter-spacing:1px; }
.tool-item { display:flex; align-items:center; gap:10px; padding:12px 18px; margin:4px 12px; border-radius:12px; cursor:pointer; transition:all .2s; color:var(--muted); }
.tool-item:hover, .tool-item.active { background:rgba(34,211,238,.1); color:var(--text); border-left:3px solid var(--accent); }
.viewer-topbar { display:flex; justify-content:space-between; align-items:center; padding:16px 18px; border-bottom:1px solid var(--border); gap:16px; }
.viewer-title { font-family:'Syne', sans-serif; font-size:16px; font-weight:800; }
.viewer-url { font-size:12px; color:var(--muted); word-break:break-all; }
.viewer-frame { width:100%; height:calc(100% - 78px); min-height:620px; border:0; background:#fff; }
.viewer-state { padding:24px; min-height:620px; display:flex; flex-direction:column; gap:14px; background:rgba(2,6,23,.35); }
.state-card { background:var(--card2); border:1px solid var(--border); border-radius:14px; padding:18px; width:100%; }
.state-title { font-family:'Syne', sans-serif; font-size:15px; font-weight:700; margin-bottom:8px; }
.state-text { font-size:13px; color:var(--muted); line-height:1.7; }
.state-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.resource-card { background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:14px; padding:16px; }
.resource-card-title { font-weight:700; margin-bottom:8px; }
.resource-card-text { font-size:13px; color:var(--muted); line-height:1.6; margin-bottom:12px; }
.resource-actions { display:flex; gap:8px; flex-wrap:wrap; }
.info-panel { padding:18px; }
.tip-card { background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:14px; padding:16px; margin-bottom:14px; }
.tip-title { font-family:'Syne', sans-serif; font-size:14px; font-weight:700; margin-bottom:8px; }
.tip-list { font-size:13px; color:var(--muted); line-height:1.7; }
@media(max-width:1100px){ .playground-layout { grid-template-columns:1fr; } .viewer-frame,.viewer-state{min-height:420px;} .state-grid{grid-template-columns:1fr;} }
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main" style="padding:24px 32px;">
    <div class="topbar">
        <div>
            <div class="page-title">Code Playground</div>
            <div class="page-sub">Blocked sites are replaced with working in-app study panels instead of dead links</div>
        </div>
    </div>

    <div class="playground-layout">
        <div class="side-panel">
            <div class="section-title">Compilers</div>
            <div class="tool-item active" onclick="loadTool('programiz-c', this)">Programiz C</div>
            <div class="tool-item" onclick="loadTool('jdoodle-java', this)">JDoodle Java</div>
            <div class="tool-item" onclick="loadTool('onlinegdb', this)">OnlineGDB</div>
            <div class="tool-item" onclick="loadTool('replit-python', this)">Replit Python</div>
            <div class="tool-item" onclick="loadTool('jsfiddle', this)">JSFiddle</div>

            <div class="section-title">Learning</div>
            <div class="tool-item" onclick="loadTool('geeksforgeeks', this)">GeeksforGeeks</div>
            <div class="tool-item" onclick="loadTool('mdn', this)">MDN Web Docs</div>
            <div class="tool-item" onclick="loadTool('w3schools', this)">W3Schools</div>
            <div class="tool-item" onclick="loadTool('stackoverflow', this)">Stack Overflow</div>

            <div class="section-title">UI / UX</div>
            <div class="tool-item" onclick="loadTool('figma', this)">Figma</div>
            <div class="tool-item" onclick="loadTool('excalidraw', this)">Excalidraw</div>
            <div class="tool-item" onclick="loadTool('codepen', this)">CodePen</div>

            <div class="section-title">Digital Logic</div>
            <div class="tool-item" onclick="loadTool('digital-logic', this)">Digital Logic</div>
            <div class="tool-item" onclick="loadTool('circuitverse', this)">CircuitVerse</div>
            <div class="tool-item" onclick="loadTool('logicly', this)">Logicly Demo</div>
        </div>

        <div class="viewer-panel">
            <div class="viewer-topbar">
                <div>
                    <div class="viewer-title" id="viewerTitle">Programiz C</div>
                    <div class="viewer-url" id="viewerUrl">https://www.programiz.com/c-programming/online-compiler/</div>
                </div>
                <a id="viewerExternal" href="https://www.programiz.com/c-programming/online-compiler/" target="_blank" class="btn btn-outline btn-sm">Open In New Tab</a>
            </div>
            <iframe id="toolFrame" class="viewer-frame" src="https://www.programiz.com/c-programming/online-compiler/" title="Embedded tool"></iframe>
            <div id="toolPanel" class="viewer-state" style="display:none;"></div>
        </div>

        <div class="info-panel">
            <div class="tip-card">
                <div class="tip-title">What Changed</div>
                <div class="tip-list">
                    The broken items you listed now load as in-app resource panels instead of trying to open blocked embeds.
                    <br>That means the page still works even when a site refuses iframe access.
                </div>
            </div>
            <div class="tip-card">
                <div class="tip-title">Best For Courses</div>
                <div class="tip-list">
                    UI/UX: Figma, Excalidraw, CodePen
                    <br>Digital Logic: Digital Logic panel, CircuitVerse panel, Logicly panel
                    <br>Programming: Programiz, OnlineGDB, JSFiddle
                </div>
            </div>
            <div class="tip-card">
                <div class="tip-title">Next Step</div>
                <div class="tip-list">
                    If you want, I can also add local templates inside this playground for C, Python, UI/UX wireframes, and digital logic lab notes.
                </div>
            </div>
        </div>
    </div>
</main>

<script>
const toolFrame = document.getElementById('toolFrame');
const toolPanel = document.getElementById('toolPanel');
const viewerTitle = document.getElementById('viewerTitle');
const viewerUrl = document.getElementById('viewerUrl');
const viewerExternal = document.getElementById('viewerExternal');

const tools = {
    'programiz-c': {
        title: 'Programiz C',
        url: 'https://www.programiz.com/c-programming/online-compiler/',
        mode: 'embed'
    },
    'jdoodle-java': {
        title: 'JDoodle Java',
        url: 'https://www.jdoodle.com/online-java-compiler/',
        mode: 'embed'
    },
    'onlinegdb': {
        title: 'OnlineGDB',
        url: 'https://www.onlinegdb.com/',
        mode: 'embed'
    },
    'jsfiddle': {
        title: 'JSFiddle',
        url: 'https://jsfiddle.net/',
        mode: 'embed'
    },
    'replit-python': {
        title: 'Python Practice Hub',
        url: 'https://replit.com/languages/python3',
        mode: 'panel',
        html: `
            <div class="state-card">
                <div class="state-title">Replit Python replaced with a working in-app panel</div>
                <div class="state-text">Replit commonly blocks embedding here. Use these Python-friendly alternatives from inside EduSync.</div>
            </div>
            <div class="state-grid">
                <div class="resource-card">
                    <div class="resource-card-title">Programiz Python</div>
                    <div class="resource-card-text">Simple online Python editor for quick class tasks and small scripts.</div>
                    <div class="resource-actions">
                        <button class="btn btn-primary btn-sm" onclick="loadEmbeddedUrl('Programiz Python','https://www.programiz.com/python-programming/online-compiler/')">Open Here</button>
                    </div>
                </div>
                <div class="resource-card">
                    <div class="resource-card-title">OnlineGDB Python</div>
                    <div class="resource-card-text">Better when you need input, output, and debugging for lab practice.</div>
                    <div class="resource-actions">
                        <button class="btn btn-primary btn-sm" onclick="loadEmbeddedUrl('OnlineGDB','https://www.onlinegdb.com/')">Open Here</button>
                    </div>
                </div>
            </div>
        `
    },
    'geeksforgeeks': {
        title: 'GeeksforGeeks Hub',
        url: 'https://www.geeksforgeeks.org/',
        mode: 'panel',
        html: `
            <div class="state-card">
                <div class="state-title">GeeksforGeeks is now a working in-app resource hub</div>
                <div class="state-text">Direct embedding is unreliable, so this panel gives you quick paths for common SE topics.</div>
            </div>
            <div class="state-grid">
                <div class="resource-card">
                    <div class="resource-card-title">DSA Practice</div>
                    <div class="resource-card-text">Arrays, linked lists, trees, graphs, sorting, and dynamic programming.</div>
                    <div class="resource-actions"><a href="https://www.geeksforgeeks.org/data-structures/" target="_blank" class="btn btn-outline btn-sm">Open Topic</a></div>
                </div>
                <div class="resource-card">
                    <div class="resource-card-title">DBMS Notes</div>
                    <div class="resource-card-text">Normalization, joins, transactions, indexing, and SQL interview-style examples.</div>
                    <div class="resource-actions"><a href="https://www.geeksforgeeks.org/dbms/" target="_blank" class="btn btn-outline btn-sm">Open Topic</a></div>
                </div>
            </div>
        `
    },
    'mdn': {
        title: 'MDN Web Docs Hub',
        url: 'https://developer.mozilla.org/',
        mode: 'panel',
        html: `
            <div class="state-card">
                <div class="state-title">MDN Web Docs replaced with an in-app web reference panel</div>
                <div class="state-text">Use this for Web Programming Practice Lab, UI/UX front-end work, and JavaScript revision.</div>
            </div>
            <div class="state-grid">
                <div class="resource-card">
                    <div class="resource-card-title">HTML Reference</div>
                    <div class="resource-card-text">Elements, forms, semantics, and accessibility basics.</div>
                    <div class="resource-actions"><a href="https://developer.mozilla.org/en-US/docs/Web/HTML" target="_blank" class="btn btn-outline btn-sm">Open HTML</a></div>
                </div>
                <div class="resource-card">
                    <div class="resource-card-title">CSS Reference</div>
                    <div class="resource-card-text">Layout, flexbox, grid, responsive design, and UI styling.</div>
                    <div class="resource-actions"><a href="https://developer.mozilla.org/en-US/docs/Web/CSS" target="_blank" class="btn btn-outline btn-sm">Open CSS</a></div>
                </div>
                <div class="resource-card">
                    <div class="resource-card-title">JavaScript Guide</div>
                    <div class="resource-card-text">Syntax, DOM work, async code, and browser APIs.</div>
                    <div class="resource-actions"><a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript" target="_blank" class="btn btn-outline btn-sm">Open JS</a></div>
                </div>
            </div>
        `
    },
    'w3schools': {
        title: 'W3Schools Quick Practice',
        url: 'https://www.w3schools.com/',
        mode: 'panel',
        html: `
            <div class="state-card">
                <div class="state-title">W3Schools replaced with quick learning shortcuts</div>
                <div class="state-text">Good for fast beginner refreshers when you need examples quickly before class or lab.</div>
            </div>
            <div class="state-grid">
                <div class="resource-card">
                    <div class="resource-card-title">HTML / CSS</div>
                    <div class="resource-card-text">Useful for page structure, forms, colors, spacing, and layouts.</div>
                    <div class="resource-actions"><a href="https://www.w3schools.com/html/" target="_blank" class="btn btn-outline btn-sm">Open HTML</a></div>
                </div>
                <div class="resource-card">
                    <div class="resource-card-title">JavaScript</div>
                    <div class="resource-card-text">Use for DOM basics, events, loops, conditions, and examples.</div>
                    <div class="resource-actions"><a href="https://www.w3schools.com/js/" target="_blank" class="btn btn-outline btn-sm">Open JS</a></div>
                </div>
                <div class="resource-card">
                    <div class="resource-card-title">SQL</div>
                    <div class="resource-card-text">Helpful for quick query examples during DBMS work.</div>
                    <div class="resource-actions"><a href="https://www.w3schools.com/sql/" target="_blank" class="btn btn-outline btn-sm">Open SQL</a></div>
                </div>
            </div>
        `
    },
    'stackoverflow': {
        title: 'Stack Overflow Debugging Hub',
        url: 'https://stackoverflow.com/',
        mode: 'panel',
        html: `
            <div class="state-card">
                <div class="state-title">Stack Overflow replaced with a safer debugging panel</div>
                <div class="state-text">Instead of opening a broken embed, use this checklist before searching externally.</div>
            </div>
            <div class="resource-card">
                <div class="resource-card-title">Debug First</div>
                <div class="resource-card-text">1. Read the exact error message.
                <br>2. Check line number and variable names.
                <br>3. Reduce the code to the smallest failing example.
                <br>4. Search the exact error plus language and library name.</div>
                <div class="resource-actions">
                    <a href="https://stackoverflow.com/questions" target="_blank" class="btn btn-outline btn-sm">Search Questions</a>
                </div>
            </div>
        `
    },
    'codepen': {
        title: 'CodePen UI Sandbox',
        url: 'https://codepen.io/',
        mode: 'panel',
        html: `
            <div class="state-card">
                <div class="state-title">CodePen replaced with a working UI sandbox panel</div>
                <div class="state-text">Use the in-app links below for UI practice and fast front-end experimentation.</div>
            </div>
            <div class="state-grid">
                <div class="resource-card">
                    <div class="resource-card-title">JSFiddle</div>
                    <div class="resource-card-text">Quick HTML, CSS, and JS testing for class exercises.</div>
                    <div class="resource-actions"><button class="btn btn-primary btn-sm" onclick="loadEmbeddedUrl('JSFiddle','https://jsfiddle.net/')">Open Here</button></div>
                </div>
                <div class="resource-card">
                    <div class="resource-card-title">Excalidraw</div>
                    <div class="resource-card-text">Sketch low-fidelity UI and user flows before coding.</div>
                    <div class="resource-actions"><a href="https://excalidraw.com/" target="_blank" class="btn btn-outline btn-sm">Open Tool</a></div>
                </div>
            </div>
        `
    },
    'figma': {
        title: 'Figma',
        url: 'https://www.figma.com/',
        mode: 'panel',
        html: `
            <div class="state-card">
                <div class="state-title">Figma for UI/UX course work</div>
                <div class="state-text">Best for wireframes, screens, components, flows, and clickable prototypes.</div>
            </div>
            <div class="resource-actions">
                <a href="https://www.figma.com/" target="_blank" class="btn btn-outline btn-sm">Open Figma</a>
            </div>
        `
    },
    'excalidraw': {
        title: 'Excalidraw',
        url: 'https://excalidraw.com/',
        mode: 'panel',
        html: `
            <div class="state-card">
                <div class="state-title">Excalidraw for quick whiteboarding</div>
                <div class="state-text">Great for user flows, architecture sketches, low-fidelity UI, and brainstorming.</div>
            </div>
            <div class="resource-actions">
                <a href="https://excalidraw.com/" target="_blank" class="btn btn-outline btn-sm">Open Excalidraw</a>
            </div>
        `
    },
    'digital-logic': {
        title: 'Digital Logic Lab Panel',
        url: '',
        mode: 'panel',
        html: `
            <div class="state-card">
                <div class="state-title">Digital Logic inside EduSync</div>
                <div class="state-text">Use this panel for common digital logic lab directions and quick revision.</div>
            </div>
            <div class="state-grid">
                <div class="resource-card">
                    <div class="resource-card-title">Core Topics</div>
                    <div class="resource-card-text">Logic gates, truth tables, K-maps, combinational circuits, sequential circuits, flip-flops, counters, and registers.</div>
                </div>
                <div class="resource-card">
                    <div class="resource-card-title">Lab Flow</div>
                    <div class="resource-card-text">1. Draw truth table.
                    <br>2. Simplify with K-map.
                    <br>3. Build the circuit.
                    <br>4. Test all inputs.
                    <br>5. Write observation and conclusion.</div>
                </div>
            </div>
        `
    },
    'circuitverse': {
        title: 'CircuitVerse Panel',
        url: 'https://circuitverse.org/simulator',
        mode: 'panel',
        html: `
            <div class="state-card">
                <div class="state-title">CircuitVerse replaced with a working panel</div>
                <div class="state-text">CircuitVerse often blocks embedding. Use this panel to jump there only if needed.</div>
            </div>
            <div class="resource-card">
                <div class="resource-card-title">Best For</div>
                <div class="resource-card-text">Logic gates, MUX/DEMUX, adders, decoders, counters, and sequential circuit practice.</div>
                <div class="resource-actions">
                    <a href="https://circuitverse.org/simulator" target="_blank" class="btn btn-outline btn-sm">Open CircuitVerse</a>
                </div>
            </div>
        `
    },
    'logicly': {
        title: 'Logicly Demo Panel',
        url: 'https://logic.ly/demo/',
        mode: 'panel',
        html: `
            <div class="state-card">
                <div class="state-title">Logicly Demo replaced with a working panel</div>
                <div class="state-text">Logicly is useful for visual logic simulation, but direct embedding is unreliable here.</div>
            </div>
            <div class="resource-card">
                <div class="resource-card-title">Use For</div>
                <div class="resource-card-text">Visualizing gate combinations, outputs, and circuit behavior for digital logic labs.</div>
                <div class="resource-actions">
                    <a href="https://logic.ly/demo/" target="_blank" class="btn btn-outline btn-sm">Open Logicly Demo</a>
                </div>
            </div>
        `
    }
};

function showPanel(tool) {
    toolFrame.style.display = 'none';
    toolPanel.style.display = 'flex';
    toolPanel.innerHTML = tool.html || '<div class="state-card"><div class="state-title">No panel available</div></div>';
}

function showFrame(tool) {
    toolPanel.style.display = 'none';
    toolFrame.style.display = 'block';
    toolFrame.src = tool.url;
}

function loadTool(key, el) {
    const tool = tools[key];
    document.querySelectorAll('.tool-item').forEach(item => item.classList.remove('active'));
    if (el) el.classList.add('active');
    viewerTitle.textContent = tool.title;
    viewerUrl.textContent = tool.url || 'In-app working panel';
    viewerExternal.href = tool.url || '#';
    viewerExternal.style.visibility = tool.url ? 'visible' : 'hidden';

    if (tool.mode === 'panel') {
        showPanel(tool);
        return;
    }

    showFrame(tool);
}

function loadEmbeddedUrl(title, url) {
    viewerTitle.textContent = title;
    viewerUrl.textContent = url;
    viewerExternal.href = url;
    viewerExternal.style.visibility = 'visible';
    toolPanel.style.display = 'none';
    toolFrame.style.display = 'block';
    toolFrame.src = url;
}
</script>
</body>
</html>
