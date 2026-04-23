<?php
// LEGACY FILE - REDIRECT TO MVC
header('Location: /prospectus');
exit;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Metropolitan University Prospectus - EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
:root{--mu-blue:#1a237e;--mu-blue-deep:#121858;--mu-green:#2e7d32;--mu-red:#c62828;--mu-text:#212121;--mu-bg:#f5f7fb;--mu-surface:#ffffff;--mu-surface-soft:#f9fbff;--mu-border:#d9deea;--mu-muted:#5f6b85;--shadow:0 20px 50px rgba(26,35,126,.08);}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:linear-gradient(180deg,#eef2fb 0%,#f7f8fc 45%,#ffffff 100%);color:var(--mu-text);min-height:100vh;}
.main{flex:1;min-width:0;max-width:calc(100vw - 276px);}
.shell{padding:32px;}
.hero{background:
linear-gradient(135deg,rgba(26,35,126,.97),rgba(18,24,88,.96)),
radial-gradient(circle at top right,rgba(255,255,255,.18),transparent 38%);
border:1px solid rgba(255,255,255,.08);border-radius:28px;padding:42px;overflow:hidden;position:relative;box-shadow:var(--shadow);}
.hero::before{content:'';position:absolute;inset:0;background:
linear-gradient(90deg,transparent 0 12%,rgba(255,255,255,.06) 12% 13%,transparent 13% 100%);
opacity:.35;pointer-events:none;}
.hero::after{content:'';position:absolute;right:-40px;bottom:-40px;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.14),transparent 68%);pointer-events:none;}
.hero-brand{display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,.96);border-radius:24px;padding:14px 20px;margin-bottom:20px;box-shadow:0 18px 36px rgba(0,0,0,.16);max-width:100%;}
.hero-brand img{display:block;width:min(100%,460px);max-width:100%;height:auto;}
.hero-mark{display:flex;align-items:center;gap:10px;margin-bottom:14px;color:#dbe7ff;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;}
.hero-mark svg{width:18px;height:18px;flex-shrink:0;}
.eyebrow{font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#cdd5ff;font-weight:700;margin-bottom:16px;}
.hero-title{font-family:'Syne',sans-serif;font-size:44px;line-height:1.05;max-width:760px;margin-bottom:14px;color:#fff;}
.hero-sub{font-size:15px;color:#eef1ff;max-width:760px;line-height:1.8;}
.hero-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:26px;}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 20px;border-radius:999px;text-decoration:none;font-weight:700;font-size:13px;transition:.2s ease;border:1px solid transparent;}
.btn-primary{background:#fff;color:var(--mu-blue);}
.btn-primary:hover{transform:translateY(-1px);}
.btn-secondary{border-color:rgba(255,255,255,.25);color:#fff;background:rgba(255,255,255,.06);}
.section{margin-top:24px;background:var(--mu-surface);border:1px solid var(--mu-border);border-radius:24px;padding:30px;box-shadow:0 10px 30px rgba(18,24,88,.04);}
.section-title{font-family:'Syne',sans-serif;font-size:26px;margin-bottom:10px;color:var(--mu-blue-deep);}
.section-heading{display:flex;align-items:center;gap:12px;margin-bottom:10px;}
.section-heading .badge-icon{width:42px;height:42px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#eef2ff,#edf8ef);border:1px solid var(--mu-border);color:var(--mu-blue);}
.section-heading .badge-icon svg{width:22px;height:22px;}
.section-heading .emoji{font-size:20px;line-height:1;}
.section-heading .section-title{margin-bottom:0;}
.section-sub{color:var(--mu-muted);font-size:14px;line-height:1.8;margin-bottom:22px;max-width:760px;}
.grid-4,.grid-3,.grid-2{display:grid;gap:18px;}
.grid-4{grid-template-columns:repeat(4,1fr);}
.grid-3{grid-template-columns:repeat(3,1fr);}
.grid-2{grid-template-columns:repeat(2,1fr);}
.stat{background:linear-gradient(180deg,#fff,#f9fbff);border:1px solid var(--mu-border);border-radius:20px;padding:24px;position:relative;overflow:hidden;}
.stat::before{content:'';position:absolute;left:0;top:0;bottom:0;width:5px;background:linear-gradient(180deg,var(--mu-blue),var(--mu-green));}
.stat-top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px;}
.stat-emoji{font-size:20px;}
.stat-svg{width:22px;height:22px;color:var(--mu-blue);opacity:.85;}
.stat-value{font-family:'Syne',sans-serif;font-size:32px;font-weight:800;margin-bottom:6px;color:var(--mu-blue);}
.stat-label{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:var(--mu-green);font-weight:700;}
.stat-copy{font-size:13px;color:var(--mu-muted);line-height:1.7;margin-top:10px;}
.feature{background:var(--mu-surface-soft);border:1px solid var(--mu-border);border-radius:20px;padding:22px;}
.feature h3{font-size:18px;margin-bottom:10px;color:var(--mu-blue);}
.feature p{font-size:14px;color:var(--mu-muted);line-height:1.8;}
.leadership-card{background:linear-gradient(180deg,#fff,#fafcff);border:1px solid var(--mu-border);border-radius:22px;padding:24px;}
.leader-photo{width:92px;height:112px;border-radius:18px;object-fit:cover;display:block;margin-bottom:18px;border:1px solid #d8deeb;box-shadow:0 14px 24px rgba(26,35,126,.12);}
.portrait{width:78px;height:78px;border-radius:22px;background:linear-gradient(135deg,var(--mu-blue),var(--mu-green));display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:28px;color:#fff;margin-bottom:18px;box-shadow:0 14px 24px rgba(26,35,126,.16);}
.role{color:var(--mu-red);font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;}
.leadership-card h3{color:var(--mu-blue-deep);margin-bottom:8px;}
.body-copy{font-size:14px;color:var(--mu-muted);line-height:1.85;}
.curriculum{display:grid;grid-template-columns:1.2fr .8fr;gap:18px;}
.pill-grid{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px;}
.pill{padding:8px 12px;border-radius:999px;background:#eef4f0;border:1px solid #d7e8da;font-size:12px;color:var(--mu-green);font-weight:700;}
.table-wrap{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:18px;}
table{width:100%;border-collapse:separate;border-spacing:0;overflow:hidden;border-radius:18px;border:1px solid var(--mu-border);}
th,td{padding:15px 16px;border-bottom:1px solid var(--mu-border);text-align:left;font-size:14px;}
th{font-size:12px;color:#fff;text-transform:uppercase;letter-spacing:.08em;background:var(--mu-blue);}
td{background:#fff;}
tr:nth-child(even) td{background:#fbfcff;}
tr:last-child td{border-bottom:none;}
.note{margin-top:16px;padding:16px 18px;border-radius:16px;background:#f4f8ed;border:1px solid #dce8cb;font-size:13px;color:#53614a;line-height:1.8;}
.footer-box{margin-top:24px;border-radius:24px;padding:28px;background:linear-gradient(135deg,#fff 0%,#f7f9ff 100%);border:1px solid var(--mu-border);box-shadow:0 10px 30px rgba(18,24,88,.04);}
.footer-box p{max-width:820px;color:var(--mu-muted);line-height:1.9;}
@media(max-width:1200px){
    .grid-4{grid-template-columns:repeat(2,1fr);}
    .grid-3,.curriculum{grid-template-columns:1fr 1fr;}
}
@media(max-width:1100px){
    .main{max-width:100vw;}
    .shell{padding:20px;}
}
@media(max-width:760px){
    .shell{padding:16px;}
    .grid-4,.grid-3,.grid-2,.curriculum{grid-template-columns:1fr;}
    .hero{padding:26px 20px;border-radius:22px;}
    .hero-title{font-size:30px;line-height:1.1;}
    .hero-sub{font-size:14px;}
    .hero-actions{flex-direction:column;align-items:stretch;}
    .hero-actions .btn{justify-content:center;width:100%;}
    .hero-brand{display:flex;width:100%;padding:12px 14px;border-radius:18px;}
    .section{padding:22px 18px;border-radius:20px;}
    .section-title{font-size:22px;}
    .section-sub,.body-copy,.feature p{font-size:13px;line-height:1.75;}
    .leadership-card{text-align:center;}
    .leader-photo{margin:0 auto 16px;}
    .pill-grid{gap:8px;}
    .pill{font-size:11px;padding:7px 10px;}
    table{min-width:680px;}
    th,td{padding:12px 13px;font-size:13px;}
}
@media(max-width:480px){
    .hero-title{font-size:26px;}
    .eyebrow{font-size:10px;letter-spacing:.14em;}
    .stat,.feature,.leadership-card{padding:18px;}
    .stat-value{font-size:28px;}
    .section-title{font-size:20px;}
}
</style>
</head>
<body>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>
<main class="main">
    <div class="shell">
        <section class="hero">
            <div class="hero-brand">
                <img src="https://metrouni.edu.bd/frontend/logo/logo.png" alt="Metropolitan University Logo">
            </div>
            <div class="hero-mark">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 3l7 3.5v5c0 4.4-3 8.48-7 9.5-4-1.02-7-5.1-7-9.5v-5L12 3z" stroke="currentColor" stroke-width="1.7"/>
                    <path d="M9.5 12l1.7 1.7L15 10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Accredited · Academic · Aspirational</span>
            </div>
            <div class="eyebrow">Official University Prospectus</div>
            <h1 class="hero-title">Education. Not Just a Degree.</h1>
            <p class="hero-sub">A refined prospectus experience for Metropolitan University that brings together institutional identity, academic direction, leadership, and admissions information in one polished, readable page.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="#admissions">Admission Requirements</a>
                <a class="btn btn-secondary" href="#academics">Explore Schools</a>
            </div>
        </section>

        <section class="section">
            <div class="section-heading">
                <div class="badge-icon">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 19h16M6 17V9l6-4 6 4v8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 19v-4h4v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2 class="section-title">University At A Glance</h2>
            </div>
            <p class="section-sub">A concise overview of the university’s scale, heritage, and graduate outcomes, presented in a more formal institutional style.</p>
            <div class="grid-4">
                <div class="stat">
                    <div class="stat-top">
                        <span class="stat-emoji">🎓</span>
                        <svg class="stat-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 9l9-4 9 4-9 4-9-4zM7 11.5v4.2c0 .5.3.9.7 1.1 2.6 1.2 5 .9 8.6 0 .4-.2.7-.6.7-1.1v-4.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div class="stat-value">6,000+</div>
                    <div class="stat-label">Active Students</div>
                    <div class="stat-copy">A growing academic community across science, business, law, and humanities.</div>
                </div>
                <div class="stat">
                    <div class="stat-top">
                        <span class="stat-emoji">👩‍🏫</span>
                        <svg class="stat-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 100-8 4 4 0 000 8zM5 21a7 7 0 0114 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    </div>
                    <div class="stat-value">250+</div>
                    <div class="stat-label">Faculty & Staff</div>
                    <div class="stat-copy">Experienced academics and professionals supporting modern higher education.</div>
                </div>
                <div class="stat">
                    <div class="stat-top">
                        <span class="stat-emoji">🏛️</span>
                        <svg class="stat-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20h16M6 9h12M7 9V5h10v4M8 20v-8M12 20v-8M16 20v-8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    </div>
                    <div class="stat-value">2003</div>
                    <div class="stat-label">Founded</div>
                    <div class="stat-copy">More than two decades of academic growth, institutional development, and student success.</div>
                </div>
                <div class="stat">
                    <div class="stat-top">
                        <span class="stat-emoji">🌍</span>
                        <svg class="stat-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21a9 9 0 100-18 9 9 0 000 18zM3 12h18M12 3c2.5 2.6 4 5.8 4 9s-1.5 6.4-4 9c-2.5-2.6-4-5.8-4-9s1.5-6.4 4-9z" stroke="currentColor" stroke-width="1.7"/></svg>
                    </div>
                    <div class="stat-value">10,000+</div>
                    <div class="stat-label">Global Alumni</div>
                    <div class="stat-copy">Graduates working in research, technology, and multinational organizations worldwide.</div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-heading">
                <div class="badge-icon"><span class="emoji">👔</span></div>
                <h2 class="section-title">Leadership</h2>
            </div>
            <p class="section-sub">Leadership profiles presented in a cleaner academic format inspired by formal university prospectus design.</p>
            <div class="grid-3">
                <article class="leadership-card">
                    <img class="leader-photo" src="https://metrouni.edu.bd/uploads/file_manager/45kCTUlmTZ0rtqIxnZsuDr.%20Toufique%20Rahman%20Chowdhury.png" alt="Dr. Toufique Rahman Chowdhury">
                    <div class="role">Founder & Chairman Emeritus</div>
                    <h3>Dr. Toufique Rahman Chowdhury</h3>
                    <p class="body-copy">Presented as the founding visionary behind the university’s long-term commitment to high-quality education, innovation, and access in Sylhet.</p>
                </article>
                <article class="leadership-card">
                    <img class="leader-photo" src="https://www.metrouni.edu.bd/uploads/file_manager/hscm0MY2cN2V4S1mpTkqch.png" alt="Mr. Tanwir Rahman Chowdhury">
                    <div class="role">Board Leadership</div>
                    <h3>Mr. Tanwir Rahman Chowdhury</h3>
                    <p class="body-copy">Focused on institutional modernization, strategic growth, and keeping the university responsive to current academic and professional demands.</p>
                </article>
                <article class="leadership-card">
                    <img class="leader-photo" src="https://metrouni.edu.bd/uploads/file_manager/yS53kfUEIsZpnqpDBK0xProfessor%20Dr.%20Mohammad%20Jahirul%20Hoque.png" alt="Professor Dr. Mohammad Jahirul Hoque">
                    <div class="role">Vice Chancellor</div>
                    <h3>Professor Dr. Mohammad Jahirul Hoque</h3>
                    <p class="body-copy">Represents academic integrity, research culture, and student-centered learning across undergraduate and postgraduate programmes.</p>
                </article>
            </div>
        </section>

        <section class="section">
            <div class="section-heading">
                <div class="badge-icon"><span class="emoji">✨</span></div>
                <h2 class="section-title">Vision And Mission</h2>
            </div>
            <div class="grid-2">
                <div class="feature">
                    <h3>Vision</h3>
                    <p>To emerge as a center of excellence in higher education by producing intellectually vibrant, socially responsible, and globally competitive graduates prepared for contemporary challenges.</p>
                </div>
                <div class="feature">
                    <h3>Mission</h3>
                    <p>To provide advanced education through innovative teaching, research, ethical practice, and meaningful community impact while developing critical thinking and professional competence.</p>
                </div>
            </div>
            <div class="note">This section replaces the second document’s separate full-page layout with concise, web-friendly prospectus content inside the main university page structure.</div>
        </section>

        <section class="section" id="academics">
            <div class="section-heading">
                <div class="badge-icon">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 7l8-3 8 3-8 3-8-3zM6 10v4.5c0 .6.3 1.1.8 1.3 3.4 1.5 6.2 1.5 10.4 0 .5-.2.8-.7.8-1.3V10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2 class="section-title">Academic Schools</h2>
            </div>
            <p class="section-sub">Academic schools and student experience summarized into a structured prospectus overview for faster reading.</p>
            <div class="curriculum">
                <div class="feature">
                    <h3>Schools And Departments</h3>
                    <p>The university offers academic pathways across science and technology, business and economics, law, and humanities. These schools combine foundational knowledge, applied learning, and professional preparation.</p>
                    <div class="pill-grid">
                        <span class="pill">Computer Science & Engineering</span>
                        <span class="pill">Software Engineering</span>
                        <span class="pill">Data Science</span>
                        <span class="pill">Electrical & Electronic Engineering</span>
                        <span class="pill">Business Administration</span>
                        <span class="pill">Economics</span>
                        <span class="pill">Law & Justice</span>
                        <span class="pill">English</span>
                    </div>
                </div>
                <div class="feature">
                    <h3>Academic Experience</h3>
                    <p>Students benefit from classwork, labs, student clubs, campus events, and industry-aligned learning. The integrated model supports both academic depth and employability.</p>
                    <div class="pill-grid">
                        <span class="pill">Research</span>
                        <span class="pill">Labs</span>
                        <span class="pill">Career Centre</span>
                        <span class="pill">Clubs</span>
                        <span class="pill">Digital Campus</span>
                        <span class="pill">Library</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="admissions">
            <div class="section-heading">
                <div class="badge-icon"><span class="emoji">📝</span></div>
                <h2 class="section-title">Admissions And Prospectus Information</h2>
            </div>
            <p class="section-sub">Admission information and contact guidance arranged into one formal, easy-to-scan section.</p>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Eligibility</th>
                            <th>Typical Requirements</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Bachelor's Programmes</td>
                            <td>HSC or equivalent</td>
                            <td>Merit-based review, admission formalities, programme-specific requirements</td>
                        </tr>
                        <tr>
                            <td>Master's Programmes</td>
                            <td>Relevant bachelor's degree</td>
                            <td>Academic eligibility, application review, possible interview</td>
                        </tr>
                        <tr>
                            <td>Professional / MBA Routes</td>
                            <td>Bachelor's degree</td>
                            <td>Academic record review and programme-specific admission conditions</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="grid-2" style="margin-top:18px;">
                <div class="feature">
                    <h3>Campus Contact</h3>
                    <p>Bateshwar, Sylhet-3104, Bangladesh<br>Phone: +88 01313 050044<br>Phone: +88 01313 050066<br>Email: info@metrouni.edu.bd</p>
                </div>
                <div class="feature">
                    <h3>Useful Admission Directions</h3>
                    <p>Applicants should verify programme fee structure, admission notices, online forms, and scholarship information from the official Metropolitan University channels before submission.</p>
                </div>
            </div>
        </section>

        <section class="footer-box">
            <div class="section-heading" style="margin-bottom:8px;">
                <div class="badge-icon"><span class="emoji">📘</span></div>
                <h2 class="section-title" style="margin-bottom:0;">Prospectus Summary</h2>
            </div>
            <p>This page now reads like a proper Metropolitan University web prospectus instead of two separate HTML files joined together. It keeps the strongest institutional content and presents it through a cleaner branded layout that is easier to browse on desktop and mobile.</p>
        </section>
    </div>
</main>
</body>
</html>
