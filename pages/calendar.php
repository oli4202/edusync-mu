<?php
// LEGACY FILE - REDIRECT TO MVC
header('Location: /calendar');
exit;

require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();

$holidays = [
    '2026-02-04' => ['Shab-e-Barat (Based on the moon sighting)', 'islamic'],
    '2026-02-21' => ['International Mother Language Day', 'national'],
    '2026-03-17' => ['Shab-e-Qadr (Based on the moon sighting)', 'islamic'],
    '2026-03-19' => ['Eid-ul-Fitr (Based on the moon sighting)', 'islamic'],
    '2026-03-20' => ['Eid-ul-Fitr', 'islamic'],
    '2026-03-21' => ['Eid-ul-Fitr', 'islamic'],
    '2026-03-22' => ['Eid-ul-Fitr', 'islamic'],
    '2026-03-23' => ['Eid-ul-Fitr', 'islamic'],
    '2026-03-26' => ['Independence & National Day', 'national'],
    '2026-04-14' => ["Bengali New Year's Day", 'national'],
    '2026-05-01' => ['May Day & Buddha Purnima (Based on the moon sighting)', 'national'],
    '2026-05-26' => ['Eid-ul-Adha (Based on the moon sighting)', 'islamic'],
    '2026-05-27' => ['Eid-ul-Adha', 'islamic'],
    '2026-05-28' => ['Eid-ul-Adha', 'islamic'],
    '2026-05-29' => ['Eid-ul-Adha', 'islamic'],
    '2026-05-30' => ['Eid-ul-Adha', 'islamic'],
    '2026-05-31' => ['Eid-ul-Adha (End)', 'islamic'],
    '2026-06-26' => ['Ashura (Based on the moon sighting)', 'islamic'],
    '2026-08-05' => ['July Mass Uprising Day', 'national'],
    '2026-08-26' => ['Eid-e-Milad-Un-Nabi (Based on the moon sighting)', 'islamic'],
    '2026-09-04' => ['Janmashtami', 'religious'],
    '2026-10-20' => ['Durga Puja (Nabami)', 'religious'],
    '2026-10-21' => ['Durga Puja (Vijaya Dashami)', 'religious'],
    '2026-12-16' => ['Victory Day', 'national'],
    '2026-12-25' => ['Christmas Day', 'religious'],
];

$monthNotes = [
    2  => ['04 February : Shab-e-Barat (Based on the moon sighting)', '21 February : International Mother Language Day'],
    3  => ['17 March : Shab-e-Qadr (Based on the moon sighting)  20 March : Jumatul Bidah', '19 March - 23 March : Eid-ul-Fitr (Based on the moon sighting)', '26 March : Independence and National Day'],
    4  => ["14 April : Bengali New Year's Day"],
    5  => ['01 May : May Day & Buddha Purnima (Based on the moon sighting)', '26-31 May : Eid-ul-Adha (Based on the moon sighting)'],
    6  => ['26 June : Ashura (Based on the moon sighting)'],
    8  => ['05 August : July Mass Uprising Day', '26 August : Eid-e-Milad-Un-Nabi (Based on the moon sighting)'],
    9  => ['04 September : Janmashtami'],
    10 => ['20-21 October : Durga Puja (Nabami, Vijaya Dashami)'],
    12 => ['16 December : Victory Day', '25 December : Christmas Day'],
];

$viewYear  = 2026;
$viewMonth = (int)($_GET['month'] ?? date('n'));
$viewMonth = max(1, min(12, $viewMonth));
$monthNames = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
$monthName  = $monthNames[$viewMonth];
$daysInMonth    = cal_days_in_month(CAL_GREGORIAN, $viewMonth, $viewYear);
$firstDayOfWeek = (int)date('w', mktime(0,0,0,$viewMonth,1,$viewYear));
$prevMonth = $viewMonth-1 < 1  ? 12 : $viewMonth-1;
$nextMonth = $viewMonth+1 > 12 ? 1  : $viewMonth+1;
$today = date('Y-m-d');

function toBn($n) {
    $d=['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return preg_replace_callback('/\d/',fn($m)=>$d[$m[0]],(string)$n);
}
function bnDate($gMonth,$gDay) {
    // Simplified mapping for demonstration matching the user's logic
    $off=[1=>17,2=>18,3=>17,4=>17,5=>17,6=>18,7=>17,8=>16,9=>16,10=>15,11=>15,12=>16];
    $o=$off[$gMonth]??17;
    $d1=$gDay+$o; if($d1>30)$d1-=30;
    $d2=$d1+1;   if($d2>30)$d2-=30;
    return toBn($d1).' '.toBn($d2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Academic Calendar 2026 — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Noto+Serif+Bengali:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.cal-main{margin-left:240px;padding:20px 24px;min-height:100vh;}
.month-pills{display:flex;gap:5px;flex-wrap:wrap;margin-bottom:16px;}
.mpill{padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;border:1px solid var(--border);color:var(--muted);text-decoration:none;transition:all .2s;}
.mpill:hover{border-color:#c8102e;color:#c8102e;}
.mpill.on{background:#c8102e;color:#fff;border-color:#c8102e;}
.nav-row{display:flex;align-items:center;justify-content:space-between;max-width:880px;margin:0 auto 10px;}
.nvbtn{background:#1a2a6c;color:#fff;border:none;border-radius:6px;padding:7px 16px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;}
.nvbtn:hover{opacity:.85;}
.cur-lbl{font-family:'Arial Black',sans-serif;font-size:20px;font-weight:900;color:var(--text);}
.cal-card{background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 6px 32px rgba(0,0,0,.22);max-width:880px;margin:0 auto;color:#111;}
.mu-top{background:#fff;padding:14px 20px 8px;text-align:center;border-bottom:4px solid #c8102e;}
.mu-bn{font-size:13px;color:#1a2a6c;font-family:'Noto Serif Bengali',serif;font-weight:600;margin-bottom:2px;}
.mu-en{font-family:'Times New Roman',serif;font-size:28px;font-weight:900;color:#1a2a6c;letter-spacing:2px;line-height:1.1;}
.mu-u{font-size:14px;font-weight:400;letter-spacing:5px;color:#c8102e;display:block;}
.mu-tag{font-size:9.5px;color:#c8102e;font-style:italic;margin-top:2px;}
.mu-fnd{font-size:9px;color:#555;margin-top:1px;}
.yr{font-family:'Arial Black',sans-serif;font-size:56px;font-weight:900;letter-spacing:-3px;line-height:1;margin:4px 0;}
.yr .c1{color:#1a2a6c;}.yr .c2{color:#999;}.yr .c3{color:#c8102e;}
.mbar{display:flex;justify-content:space-between;align-items:center;padding:8px 18px;background:#f5f5f5;border-bottom:2px solid #ccc;}
.mbar-name{font-family:'Arial Black',sans-serif;font-size:22px;font-weight:900;color:#1a2a6c;}
.mbar-bn{font-size:10px;color:#666;font-family:'Noto Serif Bengali',serif;margin-top:1px;}
.mbar-yr{font-family:'Arial Black',sans-serif;font-size:26px;font-weight:900;color:#c8102e;}
.dnames{display:grid;grid-template-columns:repeat(7,1fr);}
.dname{padding:9px 0;text-align:center;font-size:14px;font-weight:900;letter-spacing:.5px;border:0.5px solid #ccc;}
.dname.red{background:#c8102e;color:#fff;font-size:16px;}
.dname.gray{background:#e8e8e8;color:#444;}
.dname.lgray{background:#f0f0f0;color:#555;}
.cgrid{display:grid;grid-template-columns:repeat(7,1fr);border-left:0.5px solid #ccc;border-top:0.5px solid #ccc;}
.cc{background:#fff;border-right:0.5px solid #ccc;border-bottom:0.5px solid #ccc;min-height:96px;padding:5px 7px;cursor:pointer;position:relative;}
.cc:hover{background:#f0f8ff;}
.cc.emp{background:#f2f2f2;cursor:default;}
.cc.hday{background:#fffbf0;}
.cc.tday{background:#e8f0fe;}
.dn{font-family:'Arial Black',sans-serif;font-size:24px;font-weight:900;line-height:1;display:block;color:#1a1a1a;}
.cc.scol .dn{color:#c8102e;}
.cc.tday .dn{color:#1a2a6c;}
.bnd{font-size:9px;color:#888;font-family:'Noto Serif Bengali',serif;display:block;margin-top:1px;line-height:1.3;}
.hpill{display:block;font-size:8.5px;font-weight:700;margin-top:3px;padding:1px 4px;border-radius:2px;line-height:1.5;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;}
.hp-islamic{background:#d1fae5;color:#065f46;border-left:2px solid #059669;}
.hp-national{background:#fee2e2;color:#991b1b;border-left:2px solid #dc2626;}
.hp-religious{background:#fef3c7;color:#92400e;border-left:2px solid #d97706;}
.tring{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:50%;background:#1a2a6c;color:#fff!important;font-size:17px!important;}
.cal-notes{padding:10px 16px;background:#f9f9f9;border-top:2px solid #ddd;font-size:11px;color:#333;line-height:2;}
.cal-foot{background:#1a2a6c;color:#fff;display:grid;grid-template-columns:1fr auto 1fr;gap:12px;padding:12px 18px;align-items:start;}
.fp{font-size:9.5px;line-height:1.9;color:#ccc;}
.fp .ft{color:#fbbf24;font-weight:700;font-size:10.5px;margin-bottom:3px;}
.fc{text-align:center;}
.fc .fn{font-family:'Times New Roman',serif;font-size:16px;font-weight:900;letter-spacing:1px;}
.fc .fu{font-size:8px;letter-spacing:4px;color:#9bb;}
.fc .fa{font-size:9px;color:#bbb;line-height:1.9;margin-top:5px;}
.fs{font-size:9.5px;line-height:1.9;color:#ccc;text-align:right;}
.fs .ft{color:#fbbf24;font-weight:700;font-size:10.5px;margin-bottom:3px;}
.cal-tagline{background:#c8102e;padding:8px;text-align:center;font-size:13px;font-weight:700;letter-spacing:2px;color:#fff;}
.badge-row{background:#fff;padding:6px 16px;display:flex;gap:10px;justify-content:center;align-items:center;border-top:1px solid #eee;}
.gb{background:#1a2a6c;color:#fff;padding:4px 10px;border-radius:3px;font-size:9.5px;font-weight:700;letter-spacing:.5px;}
.eb{background:#555;color:#fff;padding:4px 10px;border-radius:3px;font-size:9.5px;letter-spacing:.5px;}
@media(max-width:900px){.sidebar{display:none;}.cal-main{margin-left:0;padding:10px;}.cc{min-height:65px;}.dn{font-size:17px;}.cal-foot{grid-template-columns:1fr;}.fs{text-align:left;}}
@media(max-width:580px){.cc{min-height:44px;padding:2px 3px;}.dn{font-size:13px;}.bnd,.hpill{display:none;}.yr{font-size:38px;}}
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="cal-main">
    <div class="month-pills">
        <?php for($m=1;$m<=12;$m++): ?>
        <a href="?month=<?=$m?>" class="mpill <?=$m===$viewMonth?'on':''?>"><?=$monthNames[$m]?></a>
        <?php endfor; ?>
    </div>
    <div class="nav-row">
        <a href="?month=<?=$prevMonth?>" class="nvbtn">← <?=$monthNames[$prevMonth]?></a>
        <div class="cur-lbl"><?=$monthName?> 2026</div>
        <a href="?month=<?=$nextMonth?>" class="nvbtn"><?=$monthNames[$nextMonth]?> →</a>
    </div>
    <div class="cal-card">
        <div class="mu-top">
            <div class="mu-bn">মেট্রোপলিটন ইউনিভার্সিটি</div>
            <div class="mu-en">Metropolitan<span class="mu-u">U N I V E R S I T Y</span></div>
            <div class="mu-tag">The First Permanently Chartered Private University in Sylhet</div>
            <div class="mu-fnd">Founder: Dr. Toufique Rahman Chowdhury</div>
            <div class="yr"><span class="c1">2</span><span class="c2">0</span><span class="c2">2</span><span class="c3">6</span></div>
        </div>
        <?php
        $bnM=[1=>'পৌষ-মাঘ ১৪৩২ বাংলা',2=>'মাঘ-ফাল্গুন ১৪৩২ বাংলা',3=>'ফাল্গুন-চৈত্র ১৪৩২ বাংলা',4=>'চৈত্র-বৈশাখ ১৪৩৩ বাংলা',5=>'বৈশাখ-জ্যৈষ্ঠ ১৪৩৩ বাংলা',6=>'জ্যৈষ্ঠ-আষাঢ় ১৪৩৩ বাংলা',7=>'আষাঢ়-শ্রাবণ ১৪৩৩ বাংলা',8=>'শ্রাবণ-ভাদ্র ১৪৩৩ বাংলা',9=>'ভাদ্র-আশ্বিন ১৪৩৩ বাংলা',10=>'আশ্বিন-কার্তিক ১৪৩৩ বাংলা',11=>'কার্তিক-অগ্রহায়ণ ১৪৩৩ বাংলা',12=>'অগ্রহায়ণ-পৌষ ১৪৩৩ বাংলা'];
        $hjM=[1=>'রজব-শাবান ১৪৪৭ হিজরী',2=>'শাবান-রমজান ১৪৪৭ হিজরী',3=>'রমজান-শাওয়াল ১৪৪৭ হিজরী',4=>'শাওয়াল-জিলকদ ১৪৪৭ হিজরী',5=>'জিলকদ-জিলহজ ১৪৪৭ হিজরী',6=>'জিলহজ-মহরম ১৪৪৮ হিজরী',7=>'মহরম-সফর ১৪৪৮ হিজরী',8=>'সফর-রবিউল আউয়াল ১৪৪৮ হিজরী',9=>'রবিউল আউয়াল-রবিউল সানি ১৪৪৮ হিজরী',10=>'রবিউল সানি-জমাদিউল আউয়াল ১৪৪৮ হিজরী',11=>'জমাদিউল আউয়াল-জমাদিউল সানি ১৪৪৮ হিজরী',12=>'জমাদিউল সানি-রজব ১৪৪৮ হিজরী'];
        ?>
        <div class="mbar">
            <div>
                <div class="mbar-name"><?=$monthName?></div>
                <div class="mbar-bn"><?=$bnM[$viewMonth]??''?></div>
                <div class="mbar-bn" style="color:#888;"><?=$hjM[$viewMonth]??''?></div>
            </div>
            <div class="mbar-yr">2026</div>
        </div>
        <div class="dnames">
            <div class="dname red">SUN</div>
            <div class="dname lgray">MON</div>
            <div class="dname lgray">TUE</div>
            <div class="dname lgray">WED</div>
            <div class="dname lgray">THU</div>
            <div class="dname gray">FRI</div>
            <div class="dname red">SAT</div>
        </div>
        <div class="cgrid">
            <?php
            for($e=0;$e<$firstDayOfWeek;$e++) echo '<div class="cc emp"></div>';
            for($d=1;$d<=$daysInMonth;$d++){
                $ds=sprintf('%04d-%02d-%02d',$viewYear,$viewMonth,$d);
                $dow=(int)date('w',mktime(0,0,0,$viewMonth,$d,$viewYear));
                $isWE=$dow===0||$dow===6;
                $isT=($ds===$today);
                $h=$holidays[$ds]??null;
                $cls='cc'.($isWE?' scol':'').($isT?' tday':'').($h?' hday':'');
                $bn=bnDate($viewMonth,$d);
                $hType=$h?$h[1]:'';
                $hName=$h?mb_substr($h[0],0,26):'';
                echo '<div class="'.$cls.'" onclick="showDay(\''.$ds.'\',\''.addslashes($hName).'\')">';
                if($isT){
                    echo '<span class="dn"><span class="tring">'.$d.'</span></span>';
                } else {
                    echo '<span class="dn">'.$d.'</span>';
                }
                echo '<span class="bnd">'.$bn.'</span>';
                if($h) echo '<span class="hpill hp-'.$hType.'">'.htmlspecialchars($hName).'</span>';
                echo '</div>';
            }
            $trail=(7-(($firstDayOfWeek+$daysInMonth)%7))%7;
            for($e=0;$e<$trail;$e++) echo '<div class="cc emp"></div>';
            ?>
        </div>
        <?php if(!empty($monthNotes[$viewMonth])): ?>
        <div class="cal-notes">
            <?php foreach($monthNotes[$viewMonth] as $n): ?>
            <div><?=htmlspecialchars($n)?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="badge-row">
            <span class="gb">Govt. &amp; UGC Approved</span>
            <span class="eb">Established 2003</span>
        </div>
        <div class="cal-foot">
            <div class="fp">
                <div class="ft">Our Programmes</div>
                <div>■ BSc Engineering in SWE/CSE/EEE</div>
                <div>■ BSc (Hons.) in Data Science</div>
                <div>■ BBA, MBA (General/Regular), MBM</div>
                <div>■ BSS, MSS in Economics</div>
                <div>■ LLB (Hons.) and LLM</div>
                <div>■ BA (Hons.), MA in English</div>
                <div>■ MA in ELT</div>
            </div>
            <div class="fc">
                <div class="fn">Metropolitan</div>
                <div class="fu">U N I V E R S I T Y</div>
                <div class="fa">
                    Bateshwar, Sylhet-3104, Bangladesh<br>
                    +88 01313 050044, 66<br>
                    info@metrouni.edu.bd<br>
                    www.metrouni.edu.bd
                </div>
            </div>
            <div class="fs">
                <div class="ft">Scholarships and Special Waivers</div>
                <div>■ Merit, Tribal and Need Based</div>
                <div>■ Freedom Fighters Quota</div>
                <div>■ Chairman Scholarship</div>
                <div>■ Vice Chancellor Scholarship</div>
                <div>■ Physically Differently abled Students</div>
                <div>■ Siblings Quota</div>
            </div>
        </div>
        <div class="cal-tagline">...Committed To Excellence</div>
    </div>
</main>
<div id="popup" style="display:none;position:absolute;background:#fff;border:1.5px solid #1a2a6c;border-radius:10px;padding:14px 18px;box-shadow:0 8px 28px rgba(0,0,0,.18);z-index:600;min-width:220px;max-width:300px;">
    <div id="pp-title" style="font-weight:700;font-size:14px;color:#1a2a6c;margin-bottom:8px;"></div>
    <div id="pp-body" style="font-size:12px;color:#444;line-height:1.7;"></div>
    <button onclick="document.getElementById('popup').style.display='none'" style="margin-top:10px;background:#c8102e;color:#fff;border:none;border-radius:5px;padding:5px 14px;cursor:pointer;font-size:11px;font-weight:700;">Close</button>
</div>
<script>
const holidays = <?=json_encode($holidays)?>;
function showDay(ds, hname) {
    const pop = document.getElementById('popup');
    const d = new Date(ds + 'T00:00:00');
    const lbl = d.toLocaleDateString('en-US',{weekday:'long',month:'long',day:'numeric',year:'numeric'});
    document.getElementById('pp-title').textContent = lbl;
    let html = '';
    if (holidays[ds]) {
        const types = {islamic:'#059669', national:'#dc2626', religious:'#d97706'};
        const h = holidays[ds];
        const c = types[h[1]] || '#555';
        html += `<div style="border-left:3px solid ${c};padding:5px 8px;background:${c}18;border-radius:3px;color:${c};font-weight:600;margin-bottom:6px;">${h[0]}</div>`;
    }
    const today = new Date().toISOString().split('T')[0];
    if (ds === today) html += '<div style="color:#1a2a6c;font-weight:700;font-size:11px;">⚡ Today</div>';
    if (!html) html = '<span style="color:#999;font-size:11px;">No holiday on this day.</span>';
    document.getElementById('pp-body').innerHTML = html;
    const card = document.querySelector('.cal-card');
    const r = card.getBoundingClientRect();
    pop.style.left = Math.max(10, r.left + r.width/2 - 140 + window.scrollX) + 'px';
    pop.style.top  = (r.top + 160 + window.scrollY) + 'px';
    pop.style.display = 'block';
}
document.addEventListener('click', e => {
    const pop = document.getElementById('popup');
    if (!pop.contains(e.target) && !e.target.closest('.cc')) {
        pop.style.display = 'none';
    }
});
</script>
</body>
</html>
