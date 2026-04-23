<?php $currentPage = 'ai'; ?>

<div x-data="{ 
    code: 'def hello_world():\n    print(\'Hello from EduSync AI Playground!\')\n\nhello_world()',
    selectedLang: 'python',
    output: 'Output will appear here after running...',
    outputColor: 'text-accent-emerald',
    isAiRunning: false,
    aiQuery: '',

    async runCode() {
        this.output = '🚀 Initializing execution...';
        this.outputColor = 'text-accent-cyan';

        if (this.selectedLang === 'python') {
            try {
                const resp = await fetch('/api/playground/run-python', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code: this.code, input: '' })
                });
                const data = await resp.json();
                
                if (!resp.ok || data.success === false) {
                    this.outputColor = 'text-rose-400';
                    this.output = data.error || 'Python execution failed.';
                    return;
                }

                this.outputColor = 'text-accent-emerald';
                const renderedOutput = data.output && data.output.trim() !== '' ? data.output : '[No output]';
                this.output = `${renderedOutput}\n\n[Process completed with exit code ${data.returnCode}]`;
            } catch (e) {
                this.outputColor = 'text-rose-400';
                this.output = 'Failed to connect to execution server.';
            }
        } else {
            setTimeout(() => {
                this.outputColor = 'text-slate-400 italic';
                this.output = `The ${this.selectedLang} runner is currently in development. AI can still analyze this code!`;
            }, 500);
        }
    },

    async optimizeCode() {
        if (this.isAiRunning) return;
        this.isAiRunning = true;
        const prompt = `Optimize and improve this ${this.selectedLang} code. Maintain functionality but make it more efficient/idiomatic. Return ONLY the improved code block:\n\n${this.code}`;

        try {
            const resp = await fetch('/api/ai/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt, system: 'You are an expert code optimizer. Return ONLY the improved code block, no explanations or markdown blocks.' })
            });
            const data = await resp.json();
            if (data.text) {
                this.code = data.text.replace(/```[a-z]*\n/g, '').replace(/```/g, '').trim();
            }
        } finally {
            this.isAiRunning = false;
        }
    },

    async askAboutCode() {
        if (!this.aiQuery.trim() || this.isAiRunning) return;
        this.isAiRunning = true;
        this.outputColor = 'text-accent-purple';
        this.output = 'AI is analyzing your code...';

        try {
            const resp = await fetch('/api/ai/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt: `Code:\n${this.code}\n\nQuestion: ${this.aiQuery}` })
            });
            const data = await resp.json();
            this.output = data.text || 'AI could not analyze this code.';
        } catch (e) {
            this.outputColor = 'text-rose-400';
            this.output = 'Analysis failed.';
        } finally {
            this.isAiRunning = false;
            this.aiQuery = '';
        }
    }
}" class="space-y-8 h-[calc(100vh-160px)] flex flex-col">

    <!-- Top Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-syne text-3xl font-bold text-white tracking-tight flex items-center gap-3">
                <i data-lucide="terminal" class="w-8 h-8 text-accent-emerald"></i>
                AI Code Playground
            </h1>
            <p class="text-slate-500 text-sm mt-1 font-medium">Write, run and optimize code with real-time AI assistance.</p>
        </div>
        <div class="flex items-center gap-3">
            <select x-model="selectedLang" class="bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-300 focus:outline-none focus:border-accent-emerald transition-all cursor-pointer">
                <option value="python" class="bg-dark-card">Python</option>
                <option value="javascript" class="bg-dark-card">JavaScript</option>
                <option value="sql" class="bg-dark-card">SQL</option>
                <option value="c" class="bg-dark-card">C (Preview)</option>
            </select>
            <button @click="runCode()" class="px-6 py-2.5 bg-accent-emerald text-dark-bg font-bold rounded-xl shadow-lg shadow-accent-emerald/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2 text-xs uppercase tracking-widest">
                <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                Run Code
            </button>
        </div>
    </div>

    <!-- Playground Layout -->
    <div class="flex-1 flex flex-col lg:flex-row gap-6 min-h-0">
        <!-- Editor Area -->
        <div class="flex-1 glass-card flex flex-col overflow-hidden group border-white/5 focus-within:border-accent-emerald/30 transition-colors">
            <div class="px-6 py-3 border-b border-white/5 bg-white/5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500/50"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500/50"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500/50"></div>
                    </div>
                    <span class="ml-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Editor.v1</span>
                </div>
                <button 
                    @click="optimizeCode()" 
                    :disabled="isAiRunning"
                    class="text-[10px] font-bold text-accent-cyan hover:text-white flex items-center gap-1.5 transition-colors uppercase tracking-widest disabled:opacity-50"
                >
                    <i data-lucide="sparkles" class="w-3 h-3"></i>
                    AI Optimize
                </button>
            </div>
            <textarea 
                x-model="code"
                spellcheck="false"
                class="flex-1 w-full bg-[#0a0e1a] p-6 text-sm font-mono text-slate-300 leading-relaxed focus:outline-none custom-scrollbar resize-none selection:bg-accent-emerald/20"
            ></textarea>
        </div>

        <!-- Sidebar / Output Area -->
        <div class="w-full lg:w-[420px] flex flex-col gap-6">
            <!-- Output Console -->
            <div class="flex-1 glass-card p-6 flex flex-col overflow-hidden relative group">
                <div class="flex items-center gap-2 mb-4">
                    <i data-lucide="terminal" class="w-4 h-4 text-slate-500"></i>
                    <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Execution Output</h3>
                </div>
                <div 
                    class="flex-1 bg-[#050810] border border-white/5 rounded-xl p-4 font-mono text-[13px] overflow-y-auto custom-scrollbar whitespace-pre-wrap leading-relaxed shadow-inner"
                    :class="outputColor"
                    x-text="output"
                ></div>
                
                <!-- AI Query Sub-panel -->
                <div class="mt-6 pt-6 border-t border-white/5">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="help-circle" class="w-4 h-4 text-accent-purple"></i>
                        <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">AI Context Assistant</h3>
                    </div>
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="aiQuery"
                            @keydown.enter="askAboutCode()"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 pr-12 text-xs text-white focus:outline-none focus:border-accent-purple transition-all"
                            placeholder="What does this code do?"
                        >
                        <button 
                            @click="askAboutCode()"
                            :disabled="isAiRunning || !aiQuery.trim()"
                            class="absolute right-2 top-2 p-1.5 rounded-lg bg-accent-purple text-dark-bg disabled:opacity-50 transition-all shadow-lg shadow-accent-purple/20"
                        >
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-600 mt-2 px-1">Ask AI to explain logic, find bugs, or suggest improvements.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        lucide.createIcons();
    });
</script>
