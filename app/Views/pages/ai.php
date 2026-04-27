<?php $currentPage = 'ai'; ?>

<div x-data="{ 
    currentTool: 'chat',
    chatHistory: [
        { role: 'ai', text: '👋 Hi! I\'m your AI study assistant for MU Sylhet Software Engineering. Ask me anything about your courses, get explanations, solve problems, or prepare for exams!' }
    ],
    toolInput: '',
    toolResult: '',
    isThinking: false,
    selectedQuizCount: '10',
    planDays: '14',
    planHours: '4',
    extraAns: '',

    tools: {
        chat: { title: 'AI Chat Assistant', sub: 'Ask any academic question about your SE courses at MU Sylhet', icon: 'message-square', color: 'text-accent-cyan', bg: 'bg-accent-cyan/10' },
        compact: { title: 'Compact Answer', sub: 'Paste a question → get a concise, exam-ready answer', icon: 'clipboard-list', color: 'text-accent-emerald', bg: 'bg-accent-emerald/10' },
        flashcard: { title: 'Flashcard Gen', sub: 'Paste your notes → AI generates Q&A flashcards', icon: 'layers', color: 'text-accent-purple', bg: 'bg-accent-purple/10' },
        quiz: { title: 'Quiz Generator', sub: 'Paste notes → AI creates a mini exam to test yourself', icon: 'help-circle', color: 'text-orange-400', bg: 'bg-orange-400/10' },
        plan: { title: 'Study Plan', sub: 'Enter subjects & exam date → get a day-by-day plan', icon: 'calendar', color: 'text-blue-400', bg: 'bg-blue-400/10' },
        breakdown: { title: 'Task Breakdown', sub: 'Split assignments into actionable subtasks', icon: 'list-checks', color: 'text-rose-400', bg: 'bg-rose-400/10' },
        summarize: { title: 'Summarizer', sub: 'Paste long notes → AI creates a concise summary', icon: 'file-text', color: 'text-amber-400', bg: 'bg-amber-400/10' }
    },

    async sendChat() {
        const text = this.toolInput.trim();
        if (!text || this.isThinking) return;
        
        this.chatHistory.push({ role: 'user', text: text });
        this.toolInput = '';
        this.isThinking = true;
        
        this.$nextTick(() => {
            const msgs = this.$refs.chatMessages;
            msgs.scrollTop = msgs.scrollHeight;
        });

        try {
            const historyText = this.chatHistory.map(m => m.role + ': ' + m.text).join('\n');
            const resp = await fetch('/api/ai/suggest', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    prompt: 'Conversation:\n' + historyText + '\n\nAssistant:',
                    system: 'You are a helpful academic assistant for Metropolitan University Sylhet, Bangladesh, Software Engineering department students.'
                })
            });
            const raw = await resp.text();
            let data = {};
            try { data = JSON.parse(raw); } catch (_) {}

            if (!resp.ok) {
                const message = data.text || data.error || `AI request failed (HTTP ${resp.status}).`;
                this.chatHistory.push({ role: 'ai', text: message });
                return;
            }

            if (!data.text) {
                this.chatHistory.push({ role: 'ai', text: 'AI service returned an unexpected response format.' });
                return;
            }

            this.chatHistory.push({ role: 'ai', text: data.text });
        } catch (e) {
            this.chatHistory.push({ role: 'ai', text: `Failed to connect to AI server: ${e?.message || 'Unknown error'}` });
        } finally {
            this.isThinking = false;
            this.$nextTick(() => {
                const msgs = this.$refs.chatMessages;
                msgs.scrollTop = msgs.scrollHeight;
            });
        }
    },

    async runTool() {
        if (!this.toolInput.trim() || this.isThinking) return;
        this.isThinking = true;
        this.toolResult = '';

        let prompt = '';
        const mu = 'Metropolitan University Sylhet, Software Engineering department';

        if (this.currentTool === 'compact') {
            prompt = `You are an exam assistant for ${mu}.\nQuestion: ${this.toolInput}\n${this.extraAns ? 'Full Answer: ' + this.extraAns + '\n\n' : ''}\nWrite a COMPACT exam-ready answer in max 10 lines. Use bullet points. Focus only on what an examiner wants.`;
        } else if (this.currentTool === 'flashcard') {
            prompt = `Create 8-10 Q&A flashcards for a ${mu} student from these notes:\n\n${this.toolInput}\n\nFormat as:\nQ: [question]\nA: [concise answer]`;
        } else if (this.currentTool === 'quiz') {
            prompt = `Create a ${this.selectedQuizCount}-question quiz for a ${mu} student based on:\n\n${this.toolInput}\n\nFormat with questions and options, clearly marking the correct answer with a brief explanation.`;
        } else if (this.currentTool === 'plan') {
            prompt = `Create a ${this.planDays}-day study plan for a ${mu} student.\nSubjects: ${this.toolInput}\nDaily hours available: ${this.planHours}\n\nMake a realistic day-by-day plan.`;
        } else if (this.currentTool === 'breakdown') {
            prompt = `Break down this assignment for a ${mu} student into clear, actionable subtasks:\n\n${this.toolInput}\n\nFormat as numbered steps with estimated time.`;
        } else if (this.currentTool === 'summarize') {
            prompt = `Summarize these notes for a ${mu} student:\n\n${this.toolInput}\n\nFocus on Key Points, Core Concepts, and Quick Facts.`;
        }

        try {
            const resp = await fetch('/api/ai/suggest', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt })
            });
            const raw = await resp.text();
            let data = {};
            try { data = JSON.parse(raw); } catch (_) {}

            if (!resp.ok) {
                this.toolResult = data.text || data.error || `AI request failed (HTTP ${resp.status}).`;
                return;
            }

            this.toolResult = data.text || 'AI service returned an unexpected response format.';
        } catch (e) {
            this.toolResult = `Error: Failed to connect to AI service (${e?.message || 'Unknown error'}).`;
        } finally {
            this.isThinking = false;
        }
    },

    copyResult() {
        navigator.clipboard.writeText(this.toolResult);
        alert('Copied to clipboard!');
    }
}" class="h-[calc(100vh-160px)] flex flex-col md:flex-row gap-6">

    <!-- Sidebar Tools -->
    <div class="w-full md:w-80 flex flex-col gap-6 h-full">
        <div class="glass-card p-4 flex flex-col h-full overflow-hidden">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-2 mb-4">Study Tools</div>
            <nav class="flex-1 space-y-1 overflow-y-auto custom-scrollbar pr-2">
                <template x-for="(tool, id) in tools" :key="id">
                    <button 
                        @click="currentTool = id; toolResult = ''; toolInput = ''"
                        class="w-full flex items-center gap-3 p-3 rounded-xl transition-all duration-200 group text-left"
                        :class="currentTool === id ? 'bg-white/10 text-white border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200 border border-transparent'"
                    >
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors" :class="tool.bg + ' ' + tool.color">
                            <i :data-lucide="tool.icon" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold truncate" x-text="tool.title"></div>
                            <div class="text-[10px] opacity-60 truncate" x-text="tool.sub"></div>
                        </div>
                    </button>
                </template>
            </nav>
        </div>
    </div>

    <!-- Main AI Panel -->
    <div class="flex-1 glass-card flex flex-col h-full overflow-hidden relative">
        <!-- Header -->
        <div class="p-6 border-b border-white/5 flex items-center justify-between bg-white/5 backdrop-blur-md">
            <div>
                <h2 class="font-syne text-xl font-bold text-white flex items-center gap-2">
                    <template x-if="tools[currentTool]">
                        <div class="flex items-center gap-2">
                            <i :data-lucide="tools[currentTool].icon" class="w-5 h-5" :class="tools[currentTool].color"></i>
                            <span x-text="tools[currentTool].title"></span>
                        </div>
                    </template>
                </h2>
                <p class="text-xs text-slate-500 mt-1" x-text="tools[currentTool]?.sub"></p>
            </div>
            <div x-show="isThinking" class="flex items-center gap-2 text-accent-cyan animate-pulse">
                <div class="w-2 h-2 rounded-full bg-current"></div>
                <span class="text-[10px] font-bold uppercase tracking-widest">AI is thinking</span>
            </div>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar" x-ref="chatMessages">
            <!-- Chat View -->
            <template x-if="currentTool === 'chat'">
                <div class="space-y-6">
                    <template x-for="(msg, index) in chatHistory" :key="index">
                        <div class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[85%] p-4 rounded-2xl text-sm leading-relaxed whitespace-pre-wrap shadow-sm"
                                 :class="msg.role === 'user' 
                                    ? 'bg-gradient-to-br from-accent-cyan/20 to-accent-purple/20 border border-accent-cyan/20 text-white rounded-tr-none' 
                                    : 'bg-white/5 border border-white/10 text-slate-300 rounded-tl-none'">
                                <span x-text="msg.text"></span>
                            </div>
                        </div>
                    </template>
                    <div x-show="isThinking" class="flex justify-start">
                        <div class="bg-white/5 border border-white/10 p-4 rounded-2xl rounded-tl-none flex gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-accent-cyan animate-bounce [animation-delay:-0.3s]"></div>
                            <div class="w-1.5 h-1.5 rounded-full bg-accent-cyan animate-bounce [animation-delay:-0.15s]"></div>
                            <div class="w-1.5 h-1.5 rounded-full bg-accent-cyan animate-bounce"></div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Tool View -->
            <template x-if="currentTool !== 'chat'">
                <div class="space-y-6">
                    <!-- Tool Specific Options -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-if="currentTool === 'quiz'">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Questions</label>
                                <select x-model="selectedQuizCount" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-accent-cyan">
                                    <option value="5" class="bg-dark-card">5 Questions</option>
                                    <option value="10" class="bg-dark-card">10 Questions</option>
                                    <option value="20" class="bg-dark-card">20 Questions</option>
                                </select>
                            </div>
                        </template>
                        <template x-if="currentTool === 'plan'">
                            <div class="contents">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Days Until Exam</label>
                                    <input type="number" x-model="planDays" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-accent-cyan">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Daily Hours</label>
                                    <input type="number" x-model="planHours" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-accent-cyan">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Input Content</label>
                        <textarea 
                            x-model="toolInput"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-sm text-white focus:outline-none focus:border-accent-cyan min-h-[160px] custom-scrollbar"
                            :placeholder="'Paste your ' + (currentTool === 'compact' ? 'exam question' : 'notes') + ' here...'"
                        ></textarea>
                    </div>

                    <template x-if="currentTool === 'compact'">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Full Answer (Optional)</label>
                            <textarea x-model="extraAns" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-sm text-white focus:outline-none focus:border-accent-cyan min-h-[100px]" placeholder="Paste full text for AI to summarize..."></textarea>
                        </div>
                    </template>

                    <!-- Result Area -->
                    <div x-show="toolResult" x-transition class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[10px] font-bold text-accent-cyan uppercase tracking-widest px-1">AI Generated Result</h3>
                            <button @click="copyResult()" class="text-[10px] font-bold text-slate-500 hover:text-white flex items-center gap-1 transition-colors uppercase tracking-widest">
                                <i data-lucide="copy" class="w-3 h-3"></i> Copy Result
                            </button>
                        </div>
                        <div class="p-6 rounded-2xl bg-white/5 border border-white/10 text-sm leading-relaxed text-slate-300 whitespace-pre-wrap" x-text="toolResult"></div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-white/5 bg-white/5 backdrop-blur-md">
            <div class="flex items-end gap-4">
                <template x-if="currentTool === 'chat'">
                    <div class="flex-1 relative group">
                        <textarea 
                            x-model="toolInput"
                            @keydown.enter.prevent="if(!$event.shiftKey) sendChat()"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 pr-12 text-sm text-white focus:outline-none focus:border-accent-cyan focus:ring-1 focus:ring-accent-cyan transition-all resize-none max-h-32 custom-scrollbar"
                            placeholder="Ask anything about your courses..."
                            rows="2"
                        ></textarea>
                        <button 
                            @click="sendChat()"
                            :disabled="!toolInput.trim() || isThinking"
                            class="absolute right-3 bottom-3 p-2 rounded-xl bg-accent-cyan text-dark-bg disabled:opacity-50 disabled:grayscale transition-all hover:scale-105 active:scale-95 shadow-lg shadow-accent-cyan/20"
                        >
                            <i data-lucide="send" class="w-4 h-4"></i>
                        </button>
                    </div>
                </template>
                <template x-if="currentTool !== 'chat'">
                    <button 
                        @click="runTool()"
                        :disabled="!toolInput.trim() || isThinking"
                        class="w-full py-4 bg-gradient-to-r from-accent-cyan to-accent-purple text-dark-bg font-bold rounded-2xl shadow-lg shadow-accent-cyan/20 hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 disabled:grayscale transition-all flex items-center justify-center gap-2 uppercase tracking-widest"
                    >
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                        Generate with AI
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        lucide.createIcons();
    });
</script>
