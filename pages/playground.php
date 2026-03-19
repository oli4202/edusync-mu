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
        <!-- Left Sidebar Panel -->
        <div class="lang-panel">
            <div class="lang-section">Languages</div>
            <div class="lang-item active" onclick="setLang('javascript',this)"><span class="lang-icon">🟨</span>JavaScript<span class="lang-badge">Native</span></div>
            <div class="lang-item" onclick="setLang('python',this)"><span class="lang-icon">🐍</span>Python</div>
            <div class="lang-item" onclick="setLang('java',this)"><span class="lang-icon">☕</span>Java</div>
            <div class="lang-item" onclick="setLang('cpp',this)"><span class="lang-icon">⚙️</span>C++</div>
            <div class="lang-item" onclick="setLang('c',this)"><span class="lang-icon">⚙️</span>C</div>
            <div class="lang-item" onclick="setLang('php',this)"><span class="lang-icon">🐘</span>PHP</div>
            <div class="lang-item" onclick="setLang('sql',this)"><span class="lang-icon">🗄️</span>SQL</div>
            
            <div style="height:10px;"></div>
            <div class="lang-section" style="color:var(--accent);">Practice Tracks</div>
            
            <!-- SP Lab -->
            <div class="lang-section" style="cursor:pointer;background:rgba(255,255,255,.05);padding:8px 16px;" onclick="toggleCourse('sp-lab')">🔽 SP Lab (Phase 1)</div>
            <div id="sp-lab" style="display:none;background:rgba(0,0,0,.1);">
                <div class="lang-item" onclick="loadPractice('sp','loop',this)">⮑ Basic Loop</div>
                <div class="lang-item" onclick="loadPractice('sp','string',this)">⮑ String</div>
                <div class="lang-item" onclick="loadPractice('sp','array',this)">⮑ Array</div>
                <div class="lang-item" onclick="loadPractice('sp','2darray',this)">⮑ 2D Array</div>
                <div class="lang-item" onclick="loadPractice('sp','pattern',this)">⮑ Pattern Printing</div>
                <div class="lang-item" onclick="loadPractice('sp','factorial',this)">⮑ Factorial</div>
                <div class="lang-item" onclick="loadPractice('sp','armstrong',this)">⮑ Armstrong Number</div>
                <div class="lang-item" onclick="loadPractice('sp','pointer',this)">⮑ Basic Pointer</div>
                <div class="lang-item" onclick="loadPractice('sp','functions',this)">⮑ Functions</div>
            </div>

            <!-- DS Lab -->
            <div class="lang-section" style="cursor:pointer;background:rgba(255,255,255,.05);padding:8px 16px;margin-top:2px;" onclick="toggleCourse('ds-lab')">🔽 DS Lab</div>
            <div id="ds-lab" style="display:none;background:rgba(0,0,0,.1);">
                <div class="lang-item" onclick="loadPractice('ds','pointer_recap',this)">⮑ Pointer Recap</div>
                <div class="lang-item" onclick="loadPractice('ds','linkedlist',this)">⮑ Linked List</div>
                <div class="lang-item" onclick="loadPractice('ds','stack',this)">⮑ Stack</div>
                <div class="lang-item" onclick="loadPractice('ds','queue',this)">⮑ Queue</div>
                <div class="lang-item" onclick="loadPractice('ds','sorts',this)">⮑ Select/Bubble/Insert Sort</div>
                <div class="lang-item" onclick="loadPractice('ds','binary_search',this)">⮑ Binary Search</div>
                <div class="lang-item" onclick="loadPractice('ds','bounds',this)">⮑ Lower/Upper Bound</div>
                <div class="lang-item" onclick="loadPractice('ds','occurrences',this)">⮑ Occurrences & Sqrt</div>
                <div class="lang-item" onclick="loadPractice('ds','bfs_dfs',this)">⮑ BFS & DFS</div>
            </div>

            <!-- Algo Lab -->
            <div class="lang-section" style="cursor:pointer;background:rgba(255,255,255,.05);padding:8px 16px;margin-top:2px;" onclick="toggleCourse('algo-lab')">🔽 Algo Lab (SWE221)</div>
            <div id="algo-lab" style="display:none;background:rgba(0,0,0,.1);">
                <div class="lang-item" onclick="loadPractice('algo','intro',this)">⮑ Mod 1: Intro to Algo</div>
                <div class="lang-item" onclick="loadPractice('algo','analysis',this)">⮑ Mod 2: Analysis</div>
                <div class="lang-item" onclick="loadPractice('algo','sort_search',this)">⮑ Mod 3: Sort & Search</div>
                <div class="lang-item" onclick="loadPractice('algo','graphs',this)">⮑ Mod 4: Graph Algos</div>
                <div class="lang-item" onclick="loadPractice('algo','greedy',this)">⮑ Mod 5: Greedy Algos</div>
                <div class="lang-item" onclick="loadPractice('algo','dp',this)">⮑ Mod 6: Dynamic Prog</div>
                <div class="lang-item" onclick="loadPractice('algo','backtracking',this)">⮑ Mod 7: Backtracking</div>
            </div>

            <!-- SADP -->
            <div class="lang-section" style="cursor:pointer;background:rgba(255,255,255,.05);padding:8px 16px;margin-top:2px;" onclick="toggleCourse('sadp-lab')">🔽 SADP (Design Patterns)</div>
            <div id="sadp-lab" style="display:none;background:rgba(0,0,0,.1);">
                <div class="lang-item" onclick="loadPractice('sadp','strategy',this)">⮑ Strategy</div>
                <div class="lang-item" onclick="loadPractice('sadp','observer',this)">⮑ Observer</div>
                <div class="lang-item" onclick="loadPractice('sadp','factory',this)">⮑ Factory</div>
                <div class="lang-item" onclick="loadPractice('sadp','singleton',this)">⮑ Singleton</div>
                <div class="lang-item" onclick="loadPractice('sadp','command',this)">⮑ Command</div>
                <div class="lang-item" onclick="loadPractice('sadp','adapter',this)">⮑ Adapter</div>
                <div class="lang-item" onclick="loadPractice('sadp','facade',this)">⮑ Facade</div>
                <div class="lang-item" onclick="loadPractice('sadp','template',this)">⮑ Template Method</div>
                <div class="lang-item" onclick="loadPractice('sadp','iterator',this)">⮑ Iterator</div>
                <div class="lang-item" onclick="loadPractice('sadp','composite',this)">⮑ Composite</div>
                <div class="lang-item" onclick="loadPractice('sadp','state',this)">⮑ State</div>
                <div class="lang-item" onclick="loadPractice('sadp','proxy',this)">⮑ Proxy</div>
                <div class="lang-item" onclick="loadPractice('sadp','compound',this)">⮑ Compound</div>
            </div>

            <div style="height:10px;"></div>
            <div class="lang-item" onclick="showTab('snippets')"><span class="lang-icon">📋</span>All Snippets</div>
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
                <div class="output-tab" onclick="showTab('problem',this)">📖 Problem/Notes</div>
                <div class="output-tab" onclick="showTab('ai',this)">🤖 AI Code Assistant</div>
            </div>
            <div class="output-body">
                <!-- Output Tab -->
                <div id="tab-output">
                    <div style="color:var(--muted);font-size:12px;margin-bottom:12px;">Press ▶ Run Code to see output here</div>
                    <div class="output-console" id="consoleOutput"></div>
                </div>
                <!-- Problem Tab -->
                <div id="tab-problem" style="display:none;">
                    <div style="font-size:16px;font-weight:700;color:var(--accent);margin-bottom:8px;" id="problemTitle">Welcome to Code Playground</div>
                    <div style="font-size:14px;line-height:1.6;color:var(--text);white-space:pre-wrap;" id="problemDesc">Select a topic from the **Practice Tracks** menu on the left (SP Lab, DS Lab, Algo Lab, SADP) to view the problem statement and starter code. 
Or, simply write and test any code using the Language menu!</div>
                </div>
                <!-- AI Explain Tab -->
                <div id="tab-ai" style="display:none;">
                    <div style="margin-bottom:12px;">
                        <div style="font-size:13px;font-weight:600;margin-bottom:8px;">🤖 AI Assistant Tasks</div>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <button class="btn btn-outline btn-sm" onclick="aiAction('explain')">💡 Explain this code</button>
                            <button class="btn btn-outline btn-sm" onclick="aiAction('debug')">🐛 Find bugs & fix</button>
                            <button class="btn btn-outline btn-sm" onclick="aiAction('optimize')">⚡ Optimize code</button>
                            <button class="btn btn-outline btn-sm" onclick="aiAction('complexity')">📊 Analyze complexity</button>
                        </div>
                    </div>
                    <div class="loading" id="aiLoading"><div class="spinner"></div> Processing...</div>
                    <div class="ai-explain-box" id="aiResult" style="display:none;"></div>
                </div>
                <!-- Snippets Tab -->
                <div id="tab-snippets" style="display:none;">
                    <div style="font-size:13px;font-weight:600;margin-bottom:12px;">📋 All Code Templates</div>
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
        {title:'Bubble Sort',desc:'Classic sorting algorithm',code:`function bubbleSort(arr) {\n    for(let i=0; i<arr.length-1; i++)\n        for(let j=0; j<arr.length-i-1; j++)\n            if(arr[j]>arr[j+1]) [arr[j],arr[j+1]]=[arr[j+1],arr[j]];\n    return arr;\n}\nconsole.log(bubbleSort([64,34,25,12,22,11,90]));`},
        {title:'Binary Search',desc:'Efficient search in sorted array',code:`function binarySearch(arr, target) {\n    let lo=0, hi=arr.length-1;\n    while(lo<=hi) {\n        let mid=Math.floor((lo+hi)/2);\n        if(arr[mid]===target) return mid;\n        arr[mid]<target ? lo=mid+1 : hi=mid-1;\n    }\n    return -1;\n}\nconst arr=[1,3,5,7,9,11,13,15];\nconsole.log("Found at index:", binarySearch(arr, 7));`},
    ],
    python:[
        {title:'Fibonacci',desc:'Recursive & iterative',code:`def fib_iterative(n):\n    a, b = 0, 1\n    for _ in range(n): a, b = b, a+b\n    return a\nfor i in range(10):\n    print(f"fib({i}) = {fib_iterative(i)}")`},
    ]
};

const practiceData = {
    sp: {
        loop: { title: "Basic Loop", lang: "c", code: `#include<stdio.h>\n\nint main() {\n    for(int i=1;i<=10;i++) {\n        printf("%d ", i);\n    }\n    return 0;\n}`, desc: "Write a C program using a basic loop to print numbers from 1 to 10." },
        string: { title: "String Manipulation", lang: "c", code: `#include<stdio.h>\n#include<string.h>\n\nint main() {\n    char str[] = "EduSync";\n    printf("Length: %zu\\n", strlen(str));\n    return 0;\n}`, desc: "Practice basic string operations like finding length or concatenation." },
        array: { title: "Array Operations", lang: "c", code: `#include<stdio.h>\n\nint main() {\n    int arr[5] = {1, 2, 3, 4, 5};\n    for(int i=0; i<5; i++) printf("%d ", arr[i]);\n    return 0;\n}`, desc: "Initialize a 1D array and print its elements." },
        '2darray': { title: "2D Array", lang: "c", code: `#include<stdio.h>\n\nint main() {\n    int mat[2][2] = {{1,2},{3,4}};\n    for(int i=0; i<2; i++) {\n        for(int j=0; j<2; j++) {\n            printf("%d ", mat[i][j]);\n        }\n        printf("\\n");\n    }\n    return 0;\n}`, desc: "Declare a 2D array and print it as a matrix." },
        pattern: { title: "Pattern Printing", lang: "c", code: `#include<stdio.h>\n\nint main() {\n    int rows=5;\n    for(int i=1; i<=rows; i++) {\n        for(int j=1; j<=i; j++) printf("* ");\n        printf("\\n");\n    }\n    return 0;\n}`, desc: "Print a right-angled triangle pattern of stars using nested loops." },
        factorial: { title: "Factorial", lang: "c", code: `#include<stdio.h>\n\nint main() {\n    int n=5, fact=1;\n    for(int i=1; i<=n; i++) fact *= i;\n    printf("Factorial of %d is %d\\n", n, fact);\n    return 0;\n}`, desc: "Write a program to find the factorial of a given number." },
        armstrong: { title: "Armstrong Number", lang: "c", code: `#include<stdio.h>\n\nint main() {\n    int num=153, original, remainder, result=0;\n    original = num;\n    while(original != 0) {\n        remainder = original % 10;\n        result += remainder * remainder * remainder;\n        original /= 10;\n    }\n    if(result == num) printf("%d is an Armstrong number.\\n", num);\n    else printf("%d is not an Armstrong number.\\n", num);\n    return 0;\n}`, desc: "Check whether a given number is an Armstrong number or not (e.g. 153)." },
        pointer: { title: "Basic Pointer", lang: "c", code: `#include<stdio.h>\n\nint main() {\n    int a=10;\n    int* p=&a;\n    printf("Value: %d, Address: %p\\n", *p, (void*)p);\n    return 0;\n}`, desc: "Declare a pointer and print the address and value of a variable." },
        functions: { title: "Functions", lang: "c", code: `#include<stdio.h>\n\nint add(int a, int b) {\n    return a+b;\n}\n\nint main() {\n    printf("Sum: %d\\n", add(5,10));\n    return 0;\n}`, desc: "Write a program to add two numbers using a separate function." }
    },
    ds: {
        pointer_recap: { title: "Pointer Recap", lang: "cpp", code: `#include<iostream>\nusing namespace std;\n\nvoid increment(int* p) {\n    (*p)++;\n}\n\nint main() {\n    int x = 42;\n    increment(&x);\n    cout << "Value: " << x << endl;\n    return 0;\n}`, desc: "Revise pointers, pass by reference, and dynamic memory allocation in C++." },
        linkedlist: { title: "Linked List", lang: "cpp", code: `#include<iostream>\nusing namespace std;\n\nstruct Node { int data; Node* next; };\n\nint main() {\n    // Create a linked list node\n    Node* head = new Node{10, nullptr};\n    cout << "Head points to: " << head->data << endl;\n    return 0;\n}`, desc: "Implement basic operations for a Singly Linked List (insert, delete, print)." },
        stack: { title: "Stack", lang: "cpp", code: `#include<iostream>\n#include<stack>\nusing namespace std;\n\nint main() {\n    stack<int> s;\n    s.push(10);\n    s.push(20);\n    cout << "Top: " << s.top() << endl;\n    return 0;\n}`, desc: "Implement a Stack using arrays/linked list or use STL stack." },
        queue: { title: "Queue", lang: "cpp", code: `#include<iostream>\n#include<queue>\nusing namespace std;\n\nint main() {\n    queue<int> q;\n    q.push(10);\n    q.push(20);\n    cout << "Front: " << q.front() << endl;\n    return 0;\n}`, desc: "Implement a Queue using arrays/linked list or use STL queue." },
        sorts: { title: "Selection, Bubble, Insertion Sort", lang: "cpp", code: `#include<iostream>\n#include<vector>\nusing namespace std;\n\nint main() {\n    vector<int> arr = {64, 25, 12, 22, 11};\n    // Implement your favorite elementary sort here\n    \n    for(int x: arr) cout << x << " ";\n    return 0;\n}`, desc: "Implement Selection Sort, Bubble Sort, and Insertion Sort." },
        binary_search: { title: "Binary Search", lang: "cpp", code: `#include<iostream>\n#include<vector>\nusing namespace std;\n\nint binarySearch(vector<int>& arr, int target) {\n    int lo=0, hi=arr.size()-1;\n    while(lo<=hi){\n        int mid=lo+(hi-lo)/2;\n        if(arr[mid]==target) return mid;\n        if(arr[mid]<target) lo=mid+1;\n        else hi=mid-1;\n    }\n    return -1;\n}\n\nint main() {\n    vector<int> arr = {2, 5, 8, 12, 16, 23, 38, 56, 72, 91};\n    cout << "Found at index: " << binarySearch(arr, 23) << endl;\n    return 0;\n}`, desc: "Implement binary search to find a target element in a sorted array." },
        bounds: { title: "Finding Lower / Upper Bound", lang: "cpp", code: `#include<iostream>\n#include<algorithm>\n#include<vector>\nusing namespace std;\n\nint main() {\n    vector<int> v = {10, 20, 30, 30, 20, 10};\n    sort(v.begin(), v.end());\n    auto low = lower_bound(v.begin(), v.end(), 20);\n    cout << "Lower bound of 20 at index: " << (low - v.begin()) << endl;\n    return 0;\n}`, desc: "Find the lower bound and upper bound of elements in a sorted array using standard library or custom binary search." },
        occurrences: { title: "Item Occurrences & Square Roots", lang: "cpp", code: `#include<iostream>\nusing namespace std;\n\n// Find square root using binary search\nint mySqrt(int x) {\n    if(x==0) return 0;\n    int left=1, right=x, ans;\n    while(left<=right){\n        int mid=left+(right-left)/2;\n        if(mid<=x/mid){ ans=mid; left=mid+1; }\n        else{ right=mid-1; }\n    }\n    return ans;\n}\n\nint main() {\n    cout << "Square root of 8 is: " << mySqrt(8) << endl;\n    return 0;\n}`, desc: "Use binary search variants to find the number of occurrences of an item, or to compute integer square roots." },
        bfs_dfs: { title: "Graph: BFS & DFS", lang: "cpp", code: `#include<iostream>\n#include<vector>\n#include<queue>\nusing namespace std;\n\nint main() {\n    cout << "Implement BFS and DFS using adjacency list." << endl;\n    return 0;\n}`, desc: "Implement basic Breadth-First Search and Depth-First Search traversals for a graph represented dynamically." }
    },
    algo: {
        intro: { title: "Module 1: Intro to Algorithms", lang: "cpp", code: `// Divide and Conquer, Greedy, Dynamic Programming\n#include<iostream>\nusing namespace std;\n\nint main(){\n    cout << "Intro to Algorithmic Problem Solving!" << endl;\n    return 0;\n}`, desc: "Understand algorithmic problem-solving: Divide and Conquer, Greedy, DP, Backtracking, and Brute Force methods." },
        analysis: { title: "Module 2: Analysis of Algorithms", lang: "cpp", code: `// Time Complexity: Big-O, Omega, Theta\\n// Space Complexity Trade-offs\n#include<iostream>\nusing namespace std;\n\nint main(){\n    cout << "Big-O Notation." << endl;\n    return 0;\n}`, desc: "Analyze Time Complexity (Big-O, Omega, Theta) & Space Complexity. Trade-offs between them and asymptotic analysis." },
        sort_search: { title: "Module 3: Sorting & Searching", lang: "cpp", code: `#include<iostream>\n#include<vector>\nusing namespace std;\n\n// Efficient Sorting: Merge Sort, QuickSort, Heap Sort\n// Non-comparison: Counting Sort\n\nint main() {\n    cout << "Implement advanced sorting algorithms here!" << endl;\n    return 0;\n}`, desc: "Implement Merge Sort, QuickSort, Heap Sort, and Counting Sort. Review Linear and Binary Search." },
        graphs: { title: "Module 4: Graph Algorithms", lang: "cpp", code: `#include<iostream>\n#include<vector>\nusing namespace std;\n\n// Shortest Path Algorithms & MST\nint main() {\n    cout << "Dijkstra, Bellman-Ford, Floyd-Warshall\\nKruskal, Prim" << endl;\n    return 0;\n}`, desc: "Implement Shortest Path (Dijkstra, Bellman-Ford, Floyd-Warshall) and Minimum Spanning Tree (Kruskal, Prim)." },
        greedy: { title: "Module 5: Greedy Algorithms", lang: "cpp", code: `#include<iostream>\nusing namespace std;\n\n// Activity Selection, Fractional Knapsack, Huffman\nint main() {\n    cout << "Greedy Pattern" << endl;\n    return 0;\n}`, desc: "Implement Greedy strategies: Activity Selection Problem, Fractional Knapsack, Huffman Encoding." },
        dp: { title: "Module 6: Dynamic Programming", lang: "cpp", code: `#include<iostream>\n#include<vector>\nusing namespace std;\n\n// Fibonacci, LCS, 0/1 Knapsack, LIS, Matrix Chain\nint main() {\n    cout << "Dynamic Programming" << endl;\n    return 0;\n}`, desc: "Implement DP Problems: Fibonacci Sequence, Longest Common Subsequence (LCS), 0/1 Knapsack, Matrix Chain Multiplication, LIS." },
        backtracking: { title: "Module 7: Backtracking", lang: "cpp", code: `#include<iostream>\n#include<vector>\nusing namespace std;\n\n// N-Queens Problem\nint main() {\n    cout << "N-Queens using Backtracking" << endl;\n    return 0;\n}`, desc: "Understand recursion principles and implement the N-Queens problem using backtracking." }
    },
    sadp: {
        strategy: { title: "Strategy Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Strategy Design Pattern");\n    }\n}`, desc: "Implement the Strategy Pattern. Define a family of algorithms, encapsulate each one, and make them interchangeable." },
        observer: { title: "Observer Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Observer Design Pattern");\n    }\n}`, desc: "Define a one-to-many dependency between objects so that when one object changes state, all its dependents are notified." },
        factory: { title: "Factory Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Factory Design Pattern");\n    }\n}`, desc: "Create objects without exposing the instantiation logic to the client and use a common interface." },
        singleton: { title: "Singleton Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Singleton Design Pattern");\n    }\n}`, desc: "Ensure a class only has one instance and provide a global point of access to it." },
        command: { title: "Command Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Command Pattern");\n    }\n}`, desc: "Encapsulate a request as an object, thereby letting you parameterize clients with different requests." },
        adapter: { title: "Adapter Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Adapter Pattern");\n    }\n}`, desc: "Convert the interface of a class into another interface clients expect." },
        facade: { title: "Facade Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Facade Pattern");\n    }\n}`, desc: "Provide a unified interface to a set of interfaces in a subsystem." },
        template: { title: "Template Method Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Template Method Pattern");\n    }\n}`, desc: "Define the skeleton of an algorithm in an operation, deferring some steps to subclasses." },
        iterator: { title: "Iterator Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Iterator Pattern");\n    }\n}`, desc: "Provide a way to access the elements of an aggregate object sequentially without exposing its underlying representation." },
        composite: { title: "Composite Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Composite Pattern");\n    }\n}`, desc: "Compose objects into tree structures to represent part-whole hierarchies." },
        state: { title: "State Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("State Pattern");\n    }\n}`, desc: "Allow an object to alter its behavior when its internal state changes." },
        proxy: { title: "Proxy Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Proxy Pattern");\n    }\n}`, desc: "Provide a surrogate or placeholder for another object to control access to it." },
        compound: { title: "Compound Pattern", lang: "java", code: `public class Main {\n    public static void main(String[] args) {\n        System.out.println("Compound Pattern");\n    }\n}`, desc: "Combine two or more patterns into a solution that solves a recurring or general problem." }
    }
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
    ['output','problem','ai','snippets'].forEach(t => {
        const pane = document.getElementById('tab-'+t);
        if(pane) pane.style.display = t===tab ? 'block' : 'none';
    });
    document.querySelectorAll('.output-tab').forEach(t=>t.classList.remove('active'));
    
    // Simple way to handle tab activation safely
    if (el) {
        el.classList.add('active');
    } else {
        // Find tab by text content if el not provided
        document.querySelectorAll('.output-tab').forEach(t=>{
            if(t.textContent.toLowerCase().includes(tab)) t.classList.add('active');
        });
    }
}

function toggleCourse(id) {
    const el = document.getElementById(id);
    if(el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function loadPractice(course, topic, el) {
    const data = practiceData[course][topic];
    if(data) {
        // Temporarily reset language highlight
        setLang(data.lang); 
        
        // Remove active class from all left menu items to show the practice item is active
        document.querySelectorAll('.lang-item').forEach(i=>i.classList.remove('active'));
        if(el) el.classList.add('active');
        
        editor.setValue(data.code);
        
        document.getElementById('problemTitle').textContent = data.title;
        document.getElementById('problemDesc').textContent = data.desc;
        showTab('problem');
    }
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
