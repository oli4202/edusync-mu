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
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body { overflow: hidden; }
.playground-layout { display:grid; grid-template-columns:200px 1fr 340px; height:calc(100vh - 80px); gap:0; }
.lang-panel { background:var(--card2); border-right:1px solid var(--border); padding:16px 0; overflow-y:auto; }
.lang-item { display:flex; align-items:center; gap:10px; padding:10px 16px; cursor:pointer; transition:all .2s; border-left:2px solid transparent; font-size:13px; color:var(--muted); }
.lang-item:hover,.lang-item.active { color:var(--text); background:rgba(34,211,238,.06); border-left-color:var(--accent); }
.lang-icon { font-size:18px; width:24px; text-align:center; }
.lang-badge { font-size:10px; padding:1px 6px; border-radius:10px; background:rgba(34,211,238,.1); color:var(--accent); margin-left:auto; }
.editor-panel { display:flex; flex-direction:column; background:#282a36; }
.editor-topbar { display:flex; align-items:center; justify-content:space-between; padding:10px 16px; background:#1e1f2e; border-bottom:1px solid rgba(255,255,255,.08); }
.file-name { font-family:monospace; font-size:13px; color:#f8f8f2; }
.run-btn { background:linear-gradient(135deg,#50fa7b,#00b894); border:none; border-radius:8px; padding:8px 20px; color:#1e1f2e; font-weight:700; font-family:'Syne',sans-serif; font-size:13px; cursor:pointer; transition:all .2s; display:flex; align-items:center; gap:6px; }
.run-btn:hover { opacity:.9; transform:translateY(-1px); }
.run-btn:disabled { opacity:.5; cursor:not-allowed; }
.editor-wrap { flex:1; overflow:hidden; }
.CodeMirror { height:100% !important; font-size:14px !important; font-family:'Fira Code','Cascadia Code',monospace !important; line-height:1.6 !important; }
.output-panel { background:var(--card2); border-left:1px solid var(--border); display:flex; flex-direction:column; }
.output-tabs { display:flex; border-bottom:1px solid var(--border); }
.output-tab { padding:10px 16px; font-size:12px; font-weight:600; cursor:pointer; color:var(--muted); border-bottom:2px solid transparent; transition:all .2s; }
.output-tab.active { color:var(--accent); border-bottom-color:var(--accent); }
.output-body { flex:1; overflow-y:auto; padding:16px; }
.output-console { font-family:monospace; font-size:13px; line-height:1.7; white-space:pre-wrap; }
.output-line-out { color:#f8f8f2; }
.output-line-err { color:#ff5555; }
.output-line-info { color:#8be9fd; }
.output-line-ok { color:#50fa7b; }
.ai-explain-box { background:rgba(34,211,238,.05); border:1px solid rgba(34,211,238,.15); border-radius:10px; padding:14px; font-size:13px; line-height:1.8; white-space:pre-wrap; margin-top:12px; }
.snippets-list { display:flex; flex-direction:column; gap:8px; }
.snippet-card { background:var(--card); border:1px solid var(--border); border-radius:8px; padding:12px; cursor:pointer; transition:all .2s; }
.snippet-card:hover { border-color:var(--accent); }
.snippet-title { font-size:13px; font-weight:600; margin-bottom:4px; }
.snippet-desc { font-size:11px; color:var(--muted); }
.lang-section { font-size:10px; color:var(--muted); text-transform:uppercase; letter-spacing:1px; padding:10px 16px 4px; margin-top:8px; }
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main" style="padding:0;margin-left:240px;height:100vh;display:flex;flex-direction:column;">
    <div style="padding:14px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);background:var(--card2);">
        <div style="font-family:'Syne',sans-serif;font-size:18px;font-weight:800;">⚡ Code Playground</div>
        <div style="font-size:12px;color:var(--muted);">Run code in browser — Python, JS, C, Java & more</div>
    </div>

    <div class="playground-layout" style="flex:1;">
        <!-- Language Panel -->
        <div class="lang-panel">
            <div class="lang-section">Web</div>
            <div class="lang-item active" onclick="setLang('javascript',this)"><span class="lang-icon">🟨</span>JavaScript<span class="lang-badge">Native</span></div>
            <div class="lang-item" onclick="setLang('html',this)"><span class="lang-icon">🌐</span>HTML/CSS</div>
            <div class="lang-section">Backend</div>
            <div class="lang-item" onclick="setLang('python',this)"><span class="lang-icon">🐍</span>Python<span class="lang-badge">API</span></div>
            <div class="lang-item" onclick="setLang('php',this)"><span class="lang-icon">🐘</span>PHP<span class="lang-badge">API</span></div>
            <div class="lang-section">System</div>
            <div class="lang-item" onclick="setLang('c',this)"><span class="lang-icon">⚙️</span>C</div>
            <div class="lang-item" onclick="setLang('cpp',this)"><span class="lang-icon">⚙️</span>C++</div>
            <div class="lang-item" onclick="setLang('java',this)"><span class="lang-icon">☕</span>Java</div>
            <div class="lang-section">SE Courses</div>
            <div class="lang-item" onclick="setLang('sql',this)"><span class="lang-icon">🗄️</span>SQL</div>
            <div class="lang-section">Snippets</div>
            <div class="lang-item" onclick="showTab('snippets')"><span class="lang-icon">📋</span>Code Templates</div>
        </div>

        <!-- Editor Panel -->
        <div class="editor-panel">
            <div class="editor-topbar">
                <span class="file-name" id="fileName">main.js</span>
                <div style="display:flex;gap:10px;align-items:center;">
                    <span id="runNote" style="font-size:11px;color:#8be9fd;"></span>
                    <button class="run-btn" id="runBtn" onclick="runCode()">▶ Run Code</button>
                </div>
            </div>
            <div class="editor-wrap">
                <textarea id="codeEditor"></textarea>
            </div>
        </div>

        <!-- Output Panel -->
        <div class="output-panel">
            <div class="output-tabs">
                <div class="output-tab active" onclick="showTab('output',this)">Output</div>
                <div class="output-tab" onclick="showTab('ai',this)">🤖 AI Explain</div>
                <div class="output-tab" onclick="showTab('snippets',this)">📋 Snippets</div>
            </div>
            <div class="output-body">
                <!-- Output Tab -->
                <div id="tab-output">
                    <div style="color:var(--muted);font-size:12px;margin-bottom:12px;">Press ▶ Run Code to see output here</div>
                    <div class="output-console" id="consoleOutput"></div>
                </div>
                <!-- AI Explain Tab -->
                <div id="tab-ai" style="display:none;">
                    <div style="margin-bottom:12px;">
                        <div style="font-size:13px;font-weight:600;margin-bottom:8px;">🤖 AI Code Assistant</div>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <button class="btn btn-outline btn-sm" onclick="aiAction('explain')">💡 Explain this code</button>
                            <button class="btn btn-outline btn-sm" onclick="aiAction('debug')">🐛 Find bugs & fix</button>
                            <button class="btn btn-outline btn-sm" onclick="aiAction('optimize')">⚡ Optimize code</button>
                            <button class="btn btn-outline btn-sm" onclick="aiAction('complexity')">📊 Time & space complexity</button>
                            <button class="btn btn-outline btn-sm" onclick="aiAction('convert')">🔄 Convert to another language</button>
                        </div>
                    </div>
                    <div class="loading" id="aiLoading"><div class="spinner"></div> AI is analyzing...</div>
                    <div class="ai-explain-box" id="aiResult" style="display:none;"></div>
                </div>
                <!-- Snippets Tab -->
                <div id="tab-snippets" style="display:none;">
                    <div style="font-size:13px;font-weight:600;margin-bottom:12px;">📋 MU SE Code Templates</div>
                    <div class="snippets-list" id="snippetsList"></div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/python/python.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/clike/clike.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/php/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/sql/sql.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js"></script>
<script>
let editor, currentLang = 'javascript';

const starters = {
    javascript:`// JavaScript — Hello World
console.log("Hello from EduSync MU!");

// Array operations
const marks = [85, 72, 91, 68, 95];
const avg = marks.reduce((a,b) => a+b, 0) / marks.length;
console.log("Average marks:", avg.toFixed(2));

// Arrow function
const greet = name => \`Welcome, \${name}! You are a MU SE student.\`;
console.log(greet("Student"));`,

    python:`# Python — Hello World
print("Hello from EduSync MU!")

# List operations
marks = [85, 72, 91, 68, 95]
avg = sum(marks) / len(marks)
print(f"Average marks: {avg:.2f}")

# Function
def factorial(n):
    return 1 if n <= 1 else n * factorial(n - 1)

for i in range(1, 6):
    print(f"{i}! = {factorial(i)}")`,

    c:`#include <stdio.h>

// C Program — Hello World
int main() {
    printf("Hello from EduSync MU!\\n");
    
    // Array and loop
    int marks[] = {85, 72, 91, 68, 95};
    int n = 5, sum = 0;
    for(int i = 0; i < n; i++) sum += marks[i];
    printf("Average: %.2f\\n", (float)sum/n);
    
    return 0;
}`,

    cpp:`#include <iostream>
#include <vector>
using namespace std;

int main() {
    cout << "Hello from EduSync MU!" << endl;
    
    vector<int> marks = {85, 72, 91, 68, 95};
    int sum = 0;
    for(int m : marks) sum += m;
    cout << "Average: " << (float)sum/marks.size() << endl;
    
    return 0;
}`,

    java:`public class Main {
    public static void main(String[] args) {
        System.out.println("Hello from EduSync MU!");
        
        int[] marks = {85, 72, 91, 68, 95};
        int sum = 0;
        for(int m : marks) sum += m;
        System.out.printf("Average: %.2f%n", (double)sum/marks.length);
    }
}`,

    php:`<?php echo "<?php"; ?>

echo "Hello from EduSync MU!\\n";

$marks = [85, 72, 91, 68, 95];
$avg = array_sum($marks) / count($marks);
echo "Average: " . number_format($avg, 2) . "\\n";

// Associative array
$student = ["name" => "Ali", "id" => "2021-SE-001", "gpa" => 3.75];
foreach($student as $key => $val) {
    echo "$key: $val\\n";
}`,

    sql:`-- SQL Queries for practice
-- Create and query sample tables

CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    gpa DECIMAL(3,2),
    semester INT
);

INSERT INTO students VALUES 
(1, 'Rahim', 3.75, 5),
(2, 'Karim', 3.50, 5),
(3, 'Fatima', 3.90, 6);

SELECT name, gpa 
FROM students 
WHERE gpa >= 3.5 
ORDER BY gpa DESC;`,

    html:`<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial; background: #0a0e1a; color: #e2e8f0; padding: 20px; }
        h1 { color: #22d3ee; }
        .card { background: #111827; border: 1px solid #1e2d45; border-radius: 10px; padding: 16px; margin: 10px 0; }
        button { background: #22d3ee; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>EduSync MU</h1>
    <div class="card">
        <p>Hello from the Code Playground!</p>
        <button onclick="alert('Button clicked!')">Click Me</button>
    </div>
</body>
</html>`
};

const snippets = {
    javascript:[
        {title:'Bubble Sort',desc:'Classic sorting algorithm',code:`function bubbleSort(arr) {
    for(let i=0; i<arr.length-1; i++)
        for(let j=0; j<arr.length-i-1; j++)
            if(arr[j]>arr[j+1]) [arr[j],arr[j+1]]=[arr[j+1],arr[j]];
    return arr;
}
console.log(bubbleSort([64,34,25,12,22,11,90]));`},
        {title:'Binary Search',desc:'Efficient search in sorted array',code:`function binarySearch(arr, target) {
    let lo=0, hi=arr.length-1;
    while(lo<=hi) {
        let mid=Math.floor((lo+hi)/2);
        if(arr[mid]===target) return mid;
        arr[mid]<target ? lo=mid+1 : hi=mid-1;
    }
    return -1;
}
const arr=[1,3,5,7,9,11,13,15];
console.log("Found at index:", binarySearch(arr, 7));`},
        {title:'Linked List',desc:'Basic linked list implementation',code:`class Node { constructor(v){this.val=v;this.next=null;} }
class LinkedList {
    constructor(){this.head=null;}
    push(v){let n=new Node(v);if(!this.head){this.head=n;return;}let c=this.head;while(c.next)c=c.next;c.next=n;}
    print(){let r=[],c=this.head;while(c){r.push(c.val);c=c.next;}console.log(r.join(' -> '));}
}
const ll=new LinkedList();
[1,2,3,4,5].forEach(v=>ll.push(v));
ll.print();`},
    ],
    python:[
        {title:'Fibonacci',desc:'Recursive & iterative',code:`def fib_recursive(n):
    if n <= 1: return n
    return fib_recursive(n-1) + fib_recursive(n-2)

def fib_iterative(n):
    a, b = 0, 1
    for _ in range(n): a, b = b, a+b
    return a

for i in range(10):
    print(f"fib({i}) = {fib_iterative(i)}")`},
        {title:'Stack using list',desc:'Stack data structure',code:`class Stack:
    def __init__(self): self.items = []
    def push(self, item): self.items.append(item)
    def pop(self): return self.items.pop() if self.items else None
    def peek(self): return self.items[-1] if self.items else None
    def is_empty(self): return len(self.items) == 0

s = Stack()
for i in [1,2,3,4,5]: s.push(i)
while not s.is_empty(): print(s.pop(), end=' ')`},
    ]
};

const fileNames = {javascript:'main.js',python:'main.py',c:'main.c',cpp:'main.cpp',java:'Main.java',php:'index.php',sql:'query.sql',html:'index.html'};
const cmModes   = {javascript:'javascript',python:'python',c:'text/x-csrc',cpp:'text/x-c++src',java:'text/x-java',php:'application/x-httpd-php',sql:'text/x-sql',html:'htmlmixed'};
const apiLangs  = {python:true,php:true,c:true,cpp:true,java:true,sql:true};
const runNotes  = {python:'Runs via Piston API',php:'Runs via Piston API',c:'Runs via Piston API',cpp:'Runs via Piston API',java:'Runs via Piston API',sql:'Simulated — SQLite in memory',javascript:'Runs natively in browser',html:'Renders in preview'};
const pistonLangs={python:'python',php:'php',c:'c',cpp:'cpp',java:'java'};

editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'),{
    theme:'dracula',lineNumbers:true,autoCloseBrackets:true,
    indentUnit:4,tabSize:4,indentWithTabs:false,
    extraKeys:{'Ctrl-Enter':runCode,'Cmd-Enter':runCode}
});
editor.setValue(starters.javascript);
editor.setOption('mode','javascript');
document.getElementById('runNote').textContent = runNotes.javascript;

function setLang(lang, el) {
    currentLang = lang;
    document.querySelectorAll('.lang-item').forEach(i=>i.classList.remove('active'));
    el?.classList.add('active');
    editor.setValue(starters[lang] || `// ${lang} code here`);
    editor.setOption('mode', cmModes[lang] || lang);
    document.getElementById('fileName').textContent = fileNames[lang] || 'main.txt';
    document.getElementById('runNote').textContent = runNotes[lang] || '';
    clearOutput();
    loadSnippets(lang);
}

function clearOutput() {
    document.getElementById('consoleOutput').innerHTML = '';
}

function addOutput(text, type='out') {
    const div = document.createElement('div');
    div.className = 'output-line-'+type;
    div.textContent = text;
    document.getElementById('consoleOutput').appendChild(div);
}

async function runCode() {
    const code = editor.getValue();
    const btn  = document.getElementById('runBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Running...';
    clearOutput();
    showTab('output');

    try {
        if (currentLang === 'javascript') {
            runJavaScript(code);
        } else if (currentLang === 'html') {
            runHTML(code);
        } else if (currentLang === 'sql') {
            runSQL(code);
        } else if (apiLangs[currentLang]) {
            await runViaPiston(code, currentLang);
        }
    } catch(e) {
        addOutput('Error: ' + e.message, 'err');
    }
    btn.disabled = false;
    btn.textContent = '▶ Run Code';
}

function runJavaScript(code) {
    const logs = [];
    const origLog = console.log, origErr = console.error, origWarn = console.warn;
    console.log   = (...a) => { logs.push({t:'out',v:a.map(String).join(' ')}); };
    console.error = (...a) => { logs.push({t:'err',v:a.map(String).join(' ')}); };
    console.warn  = (...a) => { logs.push({t:'info',v:a.map(String).join(' ')}); };
    try {
        // eslint-disable-next-line no-new-func
        new Function(code)();
        logs.forEach(l => addOutput(l.v, l.t));
        if (!logs.length) addOutput('✓ Code executed with no output.', 'ok');
    } catch(e) {
        logs.forEach(l => addOutput(l.v, l.t));
        addOutput('Error: ' + e.message, 'err');
    }
    console.log = origLog; console.error = origErr; console.warn = origWarn;
}

function runHTML(code) {
    const win = window.open('','_blank','width=800,height=600');
    win.document.write(code);
    win.document.close();
    addOutput('✓ HTML opened in new window', 'ok');
}

function runSQL(code) {
    addOutput('-- SQL Execution (simulated)', 'info');
    addOutput('-- Note: In production, connect to your MySQL database.', 'info');
    const stmts = code.split(';').map(s=>s.trim()).filter(Boolean);
    stmts.forEach(stmt => {
        if (stmt.toUpperCase().startsWith('SELECT'))
            addOutput('→ SELECT executed. Results would appear from your DB.', 'out');
        else if (stmt.toUpperCase().startsWith('INSERT'))
            addOutput('→ INSERT executed. Row added.', 'ok');
        else if (stmt.toUpperCase().startsWith('CREATE'))
            addOutput('→ CREATE TABLE executed.', 'ok');
        else
            addOutput('→ Statement executed: ' + stmt.substring(0,50)+'...', 'out');
    });
}

async function runViaPiston(code, lang) {
    addOutput(`Running ${lang} via Piston API...`, 'info');
    try {
        const resp = await fetch('https://emkc.org/api/v2/piston/execute', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({
                language: pistonLangs[lang] || lang,
                version: '*',
                files:[{name: fileNames[lang], content: code}]
            })
        });
        const data = await resp.json();
        if (data.run) {
            if (data.run.stdout) data.run.stdout.split('\n').forEach(l => addOutput(l, 'out'));
            if (data.run.stderr) data.run.stderr.split('\n').filter(Boolean).forEach(l => addOutput(l, 'err'));
            if (!data.run.stdout && !data.run.stderr) addOutput('✓ Code ran with no output.', 'ok');
        } else {
            addOutput('API error: ' + JSON.stringify(data), 'err');
        }
    } catch(e) {
        addOutput('Could not connect to execution API. Check your internet connection.', 'err');
    }
}

async function aiAction(action) {
    const code = editor.getValue();
    const prompts = {
        explain: `Explain this ${currentLang} code clearly for a MU Sylhet SE student:\n\n${code}`,
        debug:   `Find all bugs in this ${currentLang} code and provide the corrected version:\n\n${code}`,
        optimize:`Optimize this ${currentLang} code for better performance. Explain each improvement:\n\n${code}`,
        complexity:`Analyze the time and space complexity (Big O notation) of this ${currentLang} code:\n\n${code}`,
        convert: `Convert this ${currentLang} code to Python (if not Python) or JavaScript. Show the converted code:\n\n${code}`,
    };
    showTab('ai');
    document.getElementById('aiLoading').style.display = 'flex';
    document.getElementById('aiResult').style.display = 'none';
    try {
        const resp = await fetch('../ajax/ai-suggest.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({prompt:prompts[action]})});

        const data = await resp.json();
        document.getElementById('aiLoading').style.display = 'none';
        document.getElementById('aiResult').style.display = 'block';
        document.getElementById('aiResult').textContent = data.text || 'Could not analyze. Check your API key.';
    } catch(e) {
        document.getElementById('aiLoading').style.display = 'none';
        document.getElementById('aiResult').textContent = 'Request failed.';
        document.getElementById('aiResult').style.display = 'block';
    }
}

function showTab(tab, el) {
    ['output','ai','snippets'].forEach(t => {
        document.getElementById('tab-'+t).style.display = t===tab ? 'block' : 'none';
    });
    document.querySelectorAll('.output-tab').forEach(t=>t.classList.remove('active'));
    if (el) el.classList.add('active');
    else document.querySelectorAll('.output-tab')[['output','ai','snippets'].indexOf(tab)]?.classList.add('active');
}

function loadSnippets(lang) {
    const list = document.getElementById('snippetsList');
    const snips = snippets[lang] || [];
    list.innerHTML = snips.length ? snips.map(s=>`
        <div class="snippet-card" onclick="loadSnippet(${JSON.stringify(s.code).replace(/"/g,'&quot;')})">
            <div class="snippet-title">${s.title}</div>
            <div class="snippet-desc">${s.desc}</div>
        </div>`).join('') : '<div style="color:var(--muted);font-size:13px;">No snippets for this language yet.</div>';
}

function loadSnippet(code) {
    editor.setValue(code);
    showTab('output');
}

loadSnippets('javascript');
</script>
</body>
</html>
