<?php $pageTitle = 'University Prospectus — Metropolitan University'; ?>

<style>
  :root {
    --mu-navy: #1a2560;
    --mu-red: #c8102e;
    --mu-pink: #e91e8c;
    --mu-gold: #f0a500;
    --mu-green: #4caf50;
    --mu-light: #f4f6fb;
    --mu-white: #ffffff;
    --mu-gray: #6b7280;
    --mu-dark: #111827;
  }

  .prospectus-body {
    font-family: 'Source Sans 3', sans-serif;
    background: var(--mu-light);
    color: var(--mu-dark);
    margin: -24px; /* Offset the main container padding */
  }

  /* ── COVER ── */
  .cover {
    background: linear-gradient(160deg, var(--mu-navy) 0%, #0d1640 60%, #1a3a6e 100%);
    min-height: 80vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    position: relative;
    overflow: hidden;
    padding: 60px 40px;
  }

  .cover::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 70% 30%, rgba(200,16,46,0.15) 0%, transparent 60%),
                radial-gradient(ellipse at 20% 80%, rgba(26,37,96,0.4) 0%, transparent 50%);
  }

  .cover-logo-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 50px;
    z-index: 1;
  }

  .cover-logo-icon {
    width: 64px; height: 64px;
    background: var(--mu-red);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Playfair Display', serif;
    font-size: 32px; font-weight: 900;
    color: #fff;
    clip-path: polygon(0 0, 80% 0, 100% 20%, 100% 100%, 0 100%);
  }

  .cover-logo-text { text-align: left; }
  .cover-logo-text .big { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #fff; letter-spacing: 1px; }
  .cover-logo-text .small { font-size: 12px; color: rgba(255,255,255,0.6); letter-spacing: 3px; text-transform: uppercase; }

  .cover-badge {
    background: var(--mu-navy);
    border: 2px solid #fff;
    color: #fff;
    padding: 20px 40px;
    z-index: 1;
    margin-bottom: 40px;
  }

  .cover-badge h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(42px, 8vw, 72px);
    font-weight: 900;
    letter-spacing: 6px;
    text-transform: uppercase;
    line-height: 1;
  }

  .cover-badge p {
    font-size: 13px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.75);
    margin-top: 6px;
  }

  /* ── SECTION HEADERS ── */
  .section-header {
    background: var(--mu-navy);
    color: #fff;
    padding: 28px 48px;
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .section-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 26px;
    font-weight: 700;
  }

  .section-header .accent-bar {
    width: 6px; height: 40px;
    background: var(--mu-red);
    flex-shrink: 0;
  }

  /* ── LEADERSHIP ── */
  .leadership-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
  }

  .leader-card {
    padding: 48px;
    border-bottom: 1px solid #e5e7eb;
    background: #fff;
  }

  .leader-card:nth-child(odd) { border-right: 1px solid #e5e7eb; }

  .leader-role {
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--mu-red);
    font-weight: 600;
    margin-bottom: 8px;
  }

  .leader-name {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--mu-navy);
    margin-bottom: 16px;
    line-height: 1.3;
  }

  .leader-message {
    font-size: 14px;
    line-height: 1.75;
    color: #374151;
  }

  /* ── DEPARTMENTS ── */
  .dept-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 0;
    border-top: 1px solid #e5e7eb;
  }

  .dept-card {
    padding: 40px 36px;
    background: #fff;
    border-right: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    position: relative;
    overflow: hidden;
    transition: background 0.2s;
  }

  .dept-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 4px;
  }

  .dept-card.cse::before { background: var(--mu-green); }
  .dept-card.eee::before { background: var(--mu-gold); }
  .dept-card.swe::before { background: var(--mu-green); }
  .dept-card.bba::before { background: var(--mu-navy); }

  .dept-card:hover { background: var(--mu-light); }

  .dept-tag {
    font-size: 10px;
    letter-spacing: 3px;
    text-transform: uppercase;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--mu-red);
  }

  .dept-card h3 {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 700;
    color: var(--mu-navy);
    margin-bottom: 12px;
  }

  .dept-programs {
    margin-top: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }

  .prog-pill {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 2px;
    background: var(--mu-light);
    border: 1px solid #d1d5db;
    color: var(--mu-dark);
  }

  /* ── COURSE TABLE ── */
  .courses-section {
    padding: 48px;
    background: #fff;
  }

  .course-category { margin-bottom: 32px; }

  .cat-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--mu-red);
    padding: 6px 0;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 12px;
  }

  .course-row {
    display: flex;
    gap: 16px;
    padding: 8px 0;
    border-bottom: 1px dotted #e5e7eb;
    font-size: 13px;
  }

  .course-code { font-weight: 700; color: var(--mu-navy); min-width: 90px; font-family: monospace; }

  /* divider */
  .mu-divider {
    height: 4px;
    background: linear-gradient(90deg, var(--mu-red), var(--mu-navy), transparent);
    margin: 0;
  }

  @media (max-width: 768px) {
    .leadership-grid { grid-template-columns: 1fr; }
    .leader-card:nth-child(odd) { border-right: none; }
    .courses-grid { grid-template-columns: 1fr !important; }
  }
</style>

<div class="prospectus-body">
    <!-- ═══ COVER ═══ -->
    <div class="cover">
      <div class="cover-logo-row">
        <img src="/assets/images/logo.png" alt="Metropolitan University" style="max-width: 300px; height: auto;">
      </div>

      <div class="cover-badge">
        <h1>Prospectus</h1>
        <p>Academic Excellence · 20+ Years Legacy</p>
      </div>
    </div>

    <div class="mu-divider"></div>

    <!-- ═══ LEADERSHIP ═══ -->
    <div class="section-header">
        <div class="accent-bar"></div>
        <h2>Leadership Messages</h2>
    </div>

    <div class="leadership-grid">
        <div class="leader-card">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="w-32 h-40 bg-slate-200 rounded-xl overflow-hidden flex-shrink-0 border-2 border-mu-navy/10">
                    <img src="/assets/images/founder.png" alt="Dr. Toufique Rahman Chowdhury" class="w-full h-full object-cover">
                </div>
                <div>
                    <div class="leader-role">Founder &amp; Chairman</div>
                    <div class="leader-name">Dr. Toufique Rahman Chowdhury</div>
                    <p class="leader-message">Metropolitan University has traversed a length of twenty years since its inception. We have been able to make our presence felt, growing as we have planned, working here with devotion and a vision for a bright tomorrow.</p>
                </div>
            </div>
        </div>

        <div class="leader-card">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="w-32 h-40 bg-slate-200 rounded-xl overflow-hidden flex-shrink-0 border-2 border-mu-navy/10">
                    <img src="/assets/images/vc.png" alt="Prof. Dr. Mohammad Jahirul Hoque" class="w-full h-full object-cover">
                </div>
                <div>
                    <div class="leader-role">Vice Chancellor</div>
                    <div class="leader-name">Prof. Dr. Mohammad Jahirul Hoque</div>
                    <p class="leader-message">Our mission is providing quality tertiary education at an affordable cost. We are engaged in shaping future citizens, grooming them as human beings imbued with values and roots.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mu-divider"></div>

    <!-- ═══ DEPARTMENTS ═══ -->
    <div class="section-header">
        <div class="accent-bar"></div>
        <h2>Academic Departments</h2>
    </div>

    <div class="dept-grid">
        <div class="dept-card cse">
            <div class="dept-tag">Engineering</div>
            <h3>Computer Science &amp; Engineering</h3>
            <p>Focuses on computing fundamentals, problem-solving, and AI. Graduates have secured positions at Google, Amazon, and Facebook.</p>
            <div class="dept-programs"><span class="prog-pill">B.Sc. (Engg.) in CSE</span></div>
        </div>

        <div class="dept-card swe">
            <div class="dept-tag">Engineering</div>
            <h3>Software Engineering</h3>
            <p>Focuses on modern software development, programming, and embedded systems. Preparing competent software professionals.</p>
            <div class="dept-programs"><span class="prog-pill">B.Sc. (Hons.) in SWE</span></div>
        </div>

        <div class="dept-card bba">
            <div class="dept-tag">Business</div>
            <h3>Business Administration</h3>
            <p>Nurturing wisdom and business intelligence. Developing critical thinking and communication competency.</p>
            <div class="dept-programs"><span class="prog-pill">BBA</span><span class="prog-pill">MBA</span></div>
        </div>
    </div>

    <div class="mu-divider"></div>

    <!-- ═══ SAMPLE COURSES ═══ -->
    <div class="section-header" style="background:var(--mu-green);">
        <div class="accent-bar" style="background:#fff;"></div>
        <h2>Sample Courses – SWE Programme</h2>
    </div>

    <div class="courses-section">
        <div class="courses-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:32px;">
            <div class="course-category">
                <div class="cat-label">Core Courses</div>
                <div class="course-row"><span class="course-code">SWE 121</span>Structured Programming</div>
                <div class="course-row"><span class="course-code">SWE 221</span>Object Oriented Programming</div>
                <div class="course-row"><span class="course-code">SWE 311</span>Software Architecture</div>
                <div class="course-row"><span class="course-code">SWE 435</span>Final Year Thesis</div>
            </div>
            <div class="course-category">
                <div class="cat-label">Science & Math</div>
                <div class="course-row"><span class="course-code">MAT 112</span>Differential Calculus</div>
                <div class="course-row"><span class="course-code">MAT 235</span>Numerical Methods</div>
                <div class="course-row"><span class="course-code">PHY 111</span>Physics I</div>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
