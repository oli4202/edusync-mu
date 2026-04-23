<?php $currentPage = 'result-lookup'; ?>

<style>
.result-hero { background:linear-gradient(135deg,rgba(34,211,238,.08),rgba(129,140,248,.08)); border:1px solid rgba(34,211,238,.2); border-radius:20px; padding:32px; text-align:center; margin-bottom:28px; }
.result-hero h2 { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; margin-bottom:8px; }
.mu-logo { font-size:40px; margin-bottom:12px; }
.search-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; max-width:700px; margin:0 auto 28px; }
.search-title { font-family:'Syne',sans-serif; font-size:16px; font-weight:700; margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid var(--border); }
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.field { margin-bottom:4px; }
.field label { font-size:12px; color:var(--muted); display:block; margin-bottom:6px; text-transform:uppercase; letter-spacing:.4px; }
.result-table-wrap { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; max-width:900px; margin:0 auto; display:none; }
.result-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.result-title-text { font-family:'Syne',sans-serif; font-size:16px; font-weight:700; }
.result-info { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:20px; padding:16px; background:rgba(34,211,238,.04); border:1px solid rgba(34,211,238,.1); border-radius:12px; }
.info-item label { font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; }
.info-item span { font-size:14px; font-weight:600; display:block; margin-top:3px; }
.gpa-highlight { font-family:'Syne',sans-serif; font-size:24px; font-weight:800; color:var(--accent); }
.loading-state { text-align:center; padding:32px; display:none; }
.error-state { text-align:center; padding:24px; color:#f87171; display:none; }
.note-box { background:rgba(251,191,36,.06); border:1px solid rgba(251,191,36,.2); border-radius:10px; padding:14px; font-size:13px; color:var(--muted); margin-bottom:20px; line-height:1.6; }
@media(max-width:900px){ .form-grid,.result-info{ grid-template-columns:1fr; } }
</style>

<div class="page-title">MU Official Result Lookup</div>
<div class="page-sub">Search official results from Metropolitan University Sylhet - Department of Software Engineering</div>

<div class="result-hero">
    <div class="mu-logo">Results</div>
    <h2>Metropolitan University Sylhet</h2>
    <p style="color:var(--muted);font-size:14px;margin-top:6px;">Department of Software Engineering - Official Result Portal</p>
    <a href="https://metrouni.edu.bd/sites/department-of-software-engineering/result-se" target="_blank" class="btn btn-outline" style="margin-top:16px;font-size:12px;">Open Official MU Website</a>
</div>

<div class="note-box">
    <strong>How this works:</strong> This page fetches your result directly from the official MU Sylhet result system. Enter your details below and click Search. Results are loaded live from <strong>metrouni.edu.bd</strong>.
</div>

<div class="search-card">
    <div class="search-title">Search Your Result</div>
    <div class="form-grid">
        <div class="field">
            <label>Academic Year</label>
            <select id="acYear">
                <option value="">Select Year</option>
                <option value="2026">2026</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
            </select>
        </div>
        <div class="field">
            <label>Term / Semester</label>
            <select id="term">
                <option value="">Select Term</option>
                <option value="Spring">Spring</option>
                <option value="Summer">Summer</option>
                <option value="Fall">Fall</option>
            </select>
        </div>
        <div class="field">
            <label>Programme</label>
            <select id="programme">
                <option value="">Select Programme</option>
                <option value="B.Sc in Software Engineering">B.Sc in Software Engineering</option>
                <option value="M.Sc in Software Engineering">M.Sc in Software Engineering</option>
            </select>
        </div>
        <div class="field">
            <label>Batch</label>
            <input type="text" id="batch" placeholder="e.g. 2021, 2022, 2023..." value="<?= htmlspecialchars($user['batch'] ?? '') ?>">
        </div>
        <div class="field">
            <label>Exam Type</label>
            <select id="examType">
                <option value="">Select Type</option>
                <option value="Semester Final">Semester Final</option>
                <option value="Mid-Term">Mid-Term</option>
                <option value="Make-Up">Make-Up</option>
            </select>
        </div>
        <div class="field">
            <label>Student ID / Code</label>
            <input type="text" id="studentCode" placeholder="Your student ID..." value="<?= htmlspecialchars($user['student_id'] ?? '') ?>">
        </div>
    </div>
    <button class="btn btn-primary" style="margin-top:16px;width:100%" onclick="searchResult()">Search Result</button>
</div>

<div class="loading-state" id="loadingState">
    <div style="font-size:40px;margin-bottom:12px;">Loading</div>
    <div style="font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:6px;">Fetching your result...</div>
    <div style="color:var(--muted);font-size:13px;">Connecting to MU Sylhet result server</div>
</div>

<div class="error-state" id="errorState">
    <div style="font-size:40px;margin-bottom:10px;">Error</div>
    <div id="errorMsg" style="font-size:15px;margin-bottom:16px;"></div>
    <div style="font-size:13px;color:var(--muted);max-width:500px;margin:0 auto;line-height:1.7;">
        The official MU result server may be temporarily unavailable, or the details entered may not match any records.
        Try visiting the <a href="https://metrouni.edu.bd/sites/department-of-software-engineering/result-se" target="_blank" style="color:var(--accent)">official MU result page</a> directly.
    </div>
</div>

<div class="result-table-wrap" id="resultWrap">
    <div class="result-header">
        <div class="result-title-text">Examination Result</div>
        <button class="btn btn-outline btn-sm" onclick="window.print()">Print Result</button>
    </div>
    <div class="result-info" id="resultInfo"></div>
    <table class="data-table" id="resultTable">
        <thead>
            <tr>
                <th>Course Code</th>
                <th>Course Title</th>
                <th>Credit Hours</th>
                <th>Marks</th>
                <th>Grade</th>
                <th>Grade Point</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="resultBody"></tbody>
    </table>
    <div style="margin-top:20px;display:flex;gap:24px;justify-content:flex-end;align-items:center;padding-top:16px;border-top:1px solid var(--border);">
        <div style="text-align:right;">
            <div style="font-size:13px;color:var(--muted);">Semester GPA</div>
            <div class="gpa-highlight" id="sgpaVal">-</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:13px;color:var(--muted);">Total Credits</div>
            <div class="gpa-highlight" id="totalCredits">-</div>
        </div>
    </div>
    <div style="margin-top:16px;font-size:12px;color:var(--muted);text-align:center;padding:12px;border:1px solid var(--border);border-radius:8px;">
        This result is fetched live from Metropolitan University Sylhet official servers. For any discrepancy, contact the Examination Controller's Office.
    </div>
</div>

<script>
async function searchResult() {
    const year = document.getElementById('acYear').value;
    const term = document.getElementById('term').value;
    const prog = document.getElementById('programme').value;
    const batch = document.getElementById('batch').value.trim();
    const type = document.getElementById('examType').value;
    const code = document.getElementById('studentCode').value.trim();

    if (!year || !term || !code) {
        alert('Please fill in at least Academic Year, Term, and Student ID.');
        return;
    }

    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('resultWrap').style.display = 'none';
    document.getElementById('errorState').style.display = 'none';

    try {
        const resp = await fetch('/api/result-lookup', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ year, term, programme: prog, batch, examType: type, code })
        });
        const data = await resp.json();

        document.getElementById('loadingState').style.display = 'none';

        if (data.error) {
            document.getElementById('errorState').style.display = 'block';
            document.getElementById('errorMsg').textContent = data.error;
            return;
        }

        document.getElementById('resultInfo').innerHTML = `
            <div class="info-item"><label>Student Name</label><span>${data.name || '-'}</span></div>
            <div class="info-item"><label>Student ID</label><span>${data.id || code}</span></div>
            <div class="info-item"><label>Programme</label><span>${data.programme || prog || '-'}</span></div>
            <div class="info-item"><label>Batch</label><span>${data.batch || batch || '-'}</span></div>
            <div class="info-item"><label>Semester / Term</label><span>${term} ${year}</span></div>
            <div class="info-item"><label>Exam Type</label><span>${type || '-'}</span></div>
        `;

        const tbody = document.getElementById('resultBody');
        tbody.innerHTML = '';
        let totalGP = 0;
        let totalCr = 0;

        (data.results || []).forEach((result) => {
            tbody.innerHTML += `<tr>
                <td><strong>${result.code || '-'}</strong></td>
                <td>${result.title || '-'}</td>
                <td style="text-align:center">${result.credit || '-'}</td>
                <td style="text-align:center">${result.marks || '-'}</td>
                <td style="text-align:center;font-weight:800;color:${gradeColor(result.grade)}">${result.grade || '-'}</td>
                <td style="text-align:center">${result.gp || '-'}</td>
                <td style="text-align:center">${result.status || '-'}</td>
            </tr>`;

            if (result.gp && result.credit) {
                totalGP += result.gp * result.credit;
                totalCr += parseFloat(result.credit);
            }
        });

        document.getElementById('sgpaVal').textContent = totalCr > 0 ? (totalGP / totalCr).toFixed(2) : (data.sgpa || '-');
        document.getElementById('totalCredits').textContent = totalCr || data.totalCredits || '-';
        document.getElementById('resultWrap').style.display = 'block';
    } catch (error) {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('errorState').style.display = 'block';
        document.getElementById('errorMsg').textContent = 'Connection error. Please try again or visit the MU website directly.';
    }
}

function gradeColor(grade) {
    if (!grade) return 'var(--muted)';
    if (grade.startsWith('A')) return '#34d399';
    if (grade.startsWith('B')) return '#22d3ee';
    if (grade.startsWith('C')) return '#fbbf24';
    if (grade === 'D') return '#f97316';
    return '#f87171';
}
</script>
