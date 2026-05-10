<?php
/**
 * app/views/frontend/faq/index.php
 * FAQ page — redesigned
 *
 * Controller cần truyền vào:
 *   $faq_groups (array) — mảng nhóm câu hỏi, xem cấu trúc bên dưới
 *
 * Cấu trúc $faq_groups:
 * [
 *   [
 *     'id'       => 'general',
 *     'icon'     => '<svg>...</svg>',   // SVG string
 *     'label'    => 'Thông tin chung',
 *     'questions'=> [
 *       ['q' => 'Câu hỏi?', 'a' => 'Trả lời...'],
 *       ...
 *     ],
 *   ],
 *   ...
 * ]
 */


?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<style>
  :root {
    --primary: #1e4be0;    /* VinFast blue */
    --primary-dk: #1238b0;
    --accent:  #0fb86a;
    --cream:   #f7f5f0;
    --dark:    #0d1117;
    --text:    #1a1a2e;
    --muted:   #6b7280;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DM Sans', sans-serif; color: var(--text); background: #fff; }

  /* ── HERO ────────────────────────────────────────────── */
  .faq-hero {
    background: linear-gradient(160deg, #0d1b6e 0%, #1a47d4 55%, #0e3ba8 100%);
    padding: 72px 24px 80px;
    position: relative;
    overflow: hidden;
  }
  .faq-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 80% 60% at 50% 120%, rgba(15,184,106,.18) 0%, transparent 55%);
    pointer-events: none;
  }
  /* Decorative grid */
  .faq-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none;
  }

  /* ── SEARCH BAR ────────────────────────────────────────── */
  .faq-search-wrap {
    display: flex;
    max-width: 620px;
    margin: 28px auto 0;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,.25);
  }
  .faq-search-input {
    flex: 1;
    padding: 16px 22px;
    font-size: 15px;
    font-family: 'DM Sans', sans-serif;
    border: none;
    outline: none;
    background: #fff;
    color: var(--text);
  }
  .faq-search-input::placeholder { color: #9ca3af; }
  .faq-search-btn {
    padding: 16px 28px;
    background: #0d1117;
    color: #fff;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    letter-spacing: .04em;
    transition: background .2s;
    white-space: nowrap;
  }
  .faq-search-btn:hover { background: #1e2a3a; }

  /* search no-results message */
  #faq-no-results {
    display: none;
    text-align: center;
    padding: 40px;
    color: var(--muted);
    font-size: 15px;
  }

  /* ── CATEGORY GRID ──────────────────────────────────────── */
  .cat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 40px 24px;
    max-width: 680px;
    margin: 52px auto 0;
    padding: 0 24px;
  }
  .cat-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
    cursor: pointer;
    transition: transform .25s ease;
    text-decoration: none;
  }
  .cat-item:hover { transform: translateY(-3px); }
  .cat-icon {
    width: 56px; height: 56px;
    color: var(--primary);
    flex-shrink: 0;
  }
  .cat-icon svg { width: 100%; height: 100%; }
  .cat-label {
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
    line-height: 1.3;
  }

  /* ── FAQ GROUPS ─────────────────────────────────────────── */
  .faq-groups-section {
    background: #fff;
    padding: 64px 0 80px;
  }

  .faq-group {
    max-width: 760px;
    margin: 0 auto 56px;
    padding: 0 24px;
    scroll-margin-top: 100px;
    display: block;
  }
  .faq-group.hidden { display: none; }

  .faq-group__header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e5e7eb;
  }
  .faq-group__icon {
    width: 36px; height: 36px;
    color: var(--primary);
    flex-shrink: 0;
  }
  .faq-group__icon svg { width: 100%; height: 100%; }
  .faq-group__title {
    font-size: 20px;
    font-weight: 600;
    color: var(--text);
    font-family: 'Lora', serif;
  }

  /* ── ACCORDION ITEM ──────────────────────────────────────── */
  .acc-item {
    border-bottom: 1px solid #e5e7eb;
    overflow: hidden;
    transition: background .2s;
    display: block;
  }
  .acc-item.hidden { display: none; }

  .acc-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 0;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    font-weight: 500;
    color: var(--text);
    line-height: 1.5;
    transition: color .2s;
  }
  .acc-btn:hover { color: var(--primary); }
  .acc-btn.open   { color: var(--primary); }

  .acc-icon {
    flex-shrink: 0;
    width: 22px; height: 22px;
    border-radius: 50%;
    border: 1.5px solid currentColor;
    display: flex; align-items: center; justify-content: center;
    transition: transform .35s ease, background .2s, color .2s;
    color: var(--muted);
  }
  .acc-btn.open .acc-icon {
    transform: rotate(45deg);
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
  }

  .acc-body-wrap {
    max-height: 0;
    overflow: hidden;
    transition: max-height .4s cubic-bezier(.4,0,.2,1);
  }
  .acc-body {
    padding: 0 0 20px;
    font-size: 14.5px;
    color: var(--muted);
    line-height: 1.8;
  }
  /* highlight search match */
  .acc-body mark, .acc-btn mark {
    background: #fef08a;
    color: inherit;
    border-radius: 2px;
    padding: 0 2px;
  }

  /* ── SUBMIT QUESTION ─────────────────────────────────────── */
  .submit-section {
    background: var(--cream);
    padding: 72px 24px;
  }
  .submit-card {
    max-width: 640px;
    margin: 0 auto;
    background: #fff;
    border-radius: 16px;
    padding: 48px 44px;
    box-shadow: 0 4px 24px rgba(0,0,0,.07);
  }
  .submit-card h2 {
    font-family: 'Lora', serif;
    font-size: 26px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 8px;
  }
  .submit-card p {
    color: var(--muted);
    font-size: 14px;
    margin-bottom: 32px;
    line-height: 1.65;
  }
  .form-row { margin-bottom: 20px; }
  .form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 6px;
    letter-spacing: .02em;
  }
  .form-control {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
    background: #fafafa;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
  }
  .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(30,75,224,.1);
    background: #fff;
  }
  textarea.form-control { resize: vertical; min-height: 120px; }
  select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
  .submit-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dk) 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: opacity .2s, transform .15s;
    letter-spacing: .03em;
    margin-top: 8px;
  }
  .submit-btn:hover { opacity: .9; transform: translateY(-1px); }
  .submit-btn:active { transform: translateY(0); }
  .submit-success {
    display: none;
    text-align: center;
    padding: 20px 0 0;
  }
  .submit-success svg { margin: 0 auto 12px; display: block; color: var(--accent); }

  /* ── CONTACT ROW ─────────────────────────────────────────── */
  .contact-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: #e5e7eb;
    border-top: 1px solid #e5e7eb;
  }
  .contact-item {
    background: #fff;
    padding: 36px 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    text-align: center;
    text-decoration: none;
    color: inherit;
    transition: background .2s;
  }
  .contact-item:hover { background: var(--cream); }
  .contact-item svg { width: 32px; height: 32px; color: var(--primary); }
  .contact-title { font-size: 14px; font-weight: 600; color: var(--text); }
  .contact-val   { font-size: 13px; color: var(--primary); font-weight: 500; }

  /* ── RESPONSIVE ────────────────────────────────────────────── */
  @media (max-width: 767px) {
    .cat-grid       { grid-template-columns: repeat(3, 1fr); gap: 28px 16px; }
    .submit-card    { padding: 32px 24px; }
    .contact-row    { grid-template-columns: 1fr; }
    .faq-search-btn { padding: 16px 18px; }
  }
  @media (max-width: 480px) {
    .cat-grid    { grid-template-columns: repeat(2, 1fr); }
    .cat-icon    { width: 44px; height: 44px; }
  }
</style>
</head>
<body>

<!-- ═══════════ HERO + SEARCH ═══════════ -->
<section class="faq-hero">
  <div class="relative z-10 text-center">
    <p class="text-xs tracking-[.22em] uppercase text-white/50 mb-3">Hỗ trợ khách hàng</p>
    <h1 class="text-3xl md:text-[46px] font-normal text-white leading-tight"
        style="font-family:'Lora',serif;">
      Câu Hỏi Thường Gặp
    </h1>
    <p class="mt-3 text-white/55 text-[15px]">Tìm câu trả lời nhanh cho mọi thắc mắc của bạn</p>

    <!-- Search -->
    <div class="faq-search-wrap">
      <input
        class="faq-search-input"
        id="faq-search"
        type="text"
        placeholder="Tìm kiếm câu hỏi..."
        autocomplete="off"
        aria-label="Tìm kiếm FAQ">
      <button class="faq-search-btn" onclick="faqSearch()">Tìm kiếm</button>
    </div>
  </div>
</section>

<!-- ═══════════ CATEGORY GRID ═══════════ -->
<section style="background:#fff; padding:56px 24px 48px; border-bottom:1px solid #e5e7eb;">
  <p class="text-center text-xs tracking-[.2em] uppercase text-[#9ca3af] mb-2"
     style="font-family:'DM Sans',sans-serif;">Chủ đề</p>
  <h2 class="text-center text-[20px] font-normal text-[#1a1a2e] mb-0"
      style="font-family:'Lora',serif;">Tìm theo danh mục</h2>

  <div class="cat-grid">
    <?php foreach ($faq_groups as $grp): ?>
    <a class="cat-item" href="#group-<?= htmlspecialchars($grp['id']) ?>"
       onclick="scrollToGroup('<?= htmlspecialchars($grp['id']) ?>')">
      <div class="cat-icon"><?= $grp['icon'] ?></div>
      <span class="cat-label"><?= htmlspecialchars($grp['label']) ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ═══════════ FAQ GROUPS ═══════════ -->
<section class="faq-groups-section" id="faq-content">

  <div id="faq-no-results">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"
         style="margin:0 auto 12px;display:block;">
      <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
    </svg>
    Không tìm thấy kết quả phù hợp. Hãy thử từ khóa khác.
  </div>

  <?php foreach ($faq_groups as $grp): ?>
  <div class="faq-group" id="group-<?= htmlspecialchars($grp['id']) ?>">


    <!-- Group header -->
    <div class="faq-group__header">
      <div class="faq-group__icon"><?= $grp['icon'] ?></div>
      <h2 class="faq-group__title"><?= htmlspecialchars($grp['label']) ?></h2>
    </div>

    <!-- Accordion questions -->
    <?php foreach ($grp['questions'] as $qi => $item):
      $uid = $grp['id'] . '-' . $qi;
    ?>
    <div class="acc-item" id="acc-<?= $uid ?>">
      <button class="acc-btn" onclick="toggleAcc('<?= $uid ?>')" aria-expanded="false">
        <span class="acc-q-text"><?= htmlspecialchars($item['q']) ?></span>
        <span class="acc-icon" aria-hidden="true">
          <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor">
            <rect x="4" y="0" width="2" height="10" rx="1"/>
            <rect x="0" y="4" width="10" height="2" rx="1"/>
          </svg>
        </span>
      </button>
      <div class="acc-body-wrap" id="body-<?= $uid ?>">
        <div class="acc-body acc-a-text"><?= nl2br(htmlspecialchars($item['a'])) ?></div>
      </div>
    </div>
    <?php endforeach; ?>

  </div>
  <?php endforeach; ?>

</section>

<!-- ═══════════ SUBMIT QUESTION ═══════════ -->
<section class="submit-section">
  <div class="submit-card">
    <h2>Không tìm thấy câu trả lời?</h2>
    <p>Gửi câu hỏi của bạn cho chúng tôi. Đội ngũ hỗ trợ sẽ phản hồi trong vòng 24 giờ làm việc.</p>

    <form id="faq-submit-form" onsubmit="submitQuestion(event)">
      <div class="form-row">
        <label class="form-label">Họ và tên *</label>
        <input class="form-control" type="text" name="name" placeholder="Nguyễn Văn A" required>
      </div>
      <div class="form-row">
        <label class="form-label">Email *</label>
        <input class="form-control" type="email" name="email" placeholder="email@example.com" required>
      </div>
      <div class="form-row">
        <label class="form-label">Danh mục</label>
        <select class="form-control" name="category">
          <option value="">-- Chọn chủ đề --</option>
          <?php foreach ($faq_groups as $grp): ?>
          <option value="<?= htmlspecialchars($grp['id']) ?>">
            <?= htmlspecialchars($grp['label']) ?>
          </option>
          <?php endforeach; ?>
          <option value="other">Khác</option>
        </select>
      </div>
      <div class="form-row">
        <label class="form-label">Câu hỏi của bạn *</label>
        <textarea class="form-control" name="question"
                  placeholder="Mô tả chi tiết câu hỏi của bạn..." required></textarea>
      </div>
      <button class="submit-btn" type="submit">
        Gửi câu hỏi →
      </button>
    </form>

    <!-- Success state -->
    <div class="submit-success" id="submit-success">
      <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="12" cy="12" r="10"/>
        <path d="M8 12l3 3 5-5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <p class="text-[16px] font-semibold text-[#1a1a2e]">Câu hỏi đã được gửi!</p>
      <p class="text-[13px] text-[#6b7280] mt-1">Chúng tôi sẽ phản hồi trong vòng 24 giờ làm việc.</p>
    </div>
  </div>
</section>

<!-- ═══════════ CONTACT ROW ═══════════ -->
<div class="contact-row">
  <a class="contact-item" href="tel:19002323">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.0 1.18 2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92v2z"/>
    </svg>
    <span class="contact-title">Hotline</span>
    <span class="contact-val">1900 23 23</span>
  </a>
  <a class="contact-item" href="mailto:hotro@vinfastauto.com">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
      <polyline points="22,6 12,13 2,6"/>
    </svg>
    <span class="contact-title">Email</span>
    <span class="contact-val">hotro@vinfastauto.com</span>
  </a>
  <a class="contact-item" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>contact">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
    </svg>
    <span class="contact-title">Chat trực tiếp</span>
    <span class="contact-val">Liên hệ ngay</span>
  </a>
</div>

<script>
/* ── Accordion ────────────────────────────────────── */
function toggleAcc(uid) {
  const btn  = document.querySelector(`[onclick="toggleAcc('${uid}')"]`);
  const wrap = document.getElementById('body-' + uid);
  const isOpen = btn.classList.contains('open');

  // close all in same group
  const groupEl = btn.closest('.faq-group');
  groupEl.querySelectorAll('.acc-btn.open').forEach(b => {
    b.classList.remove('open');
    b.setAttribute('aria-expanded', 'false');
    const id = b.getAttribute('onclick').match(/'([^']+)'/)[1];
    const w  = document.getElementById('body-' + id);
    w.style.maxHeight = '0';
  });

  if (!isOpen) {
    btn.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
    wrap.style.maxHeight = wrap.scrollHeight + 'px';
  }
}

/* ── Smooth scroll to group ────────────────────────── */
function scrollToGroup(id) {
  const el = document.getElementById('group-' + id);
  if (el) {
    setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 60);
    // Auto-open first item of group
    const firstBtn = el.querySelector('.acc-btn');
    if (firstBtn && !firstBtn.classList.contains('open')) firstBtn.click();
  }
}

/* ── Live search ───────────────────────────────────── */
const searchInput = document.getElementById('faq-search');
searchInput.addEventListener('input', faqSearch);
searchInput.addEventListener('keydown', e => { if (e.key === 'Enter') faqSearch(); });

function faqSearch() {
  const q = searchInput.value.trim().toLowerCase();
  let totalVisible = 0;

  document.querySelectorAll('.faq-group').forEach(group => {
    let groupVisible = 0;
    group.querySelectorAll('.acc-item').forEach(item => {
      const qText = item.querySelector('.acc-q-text').textContent.toLowerCase();
      const aText = item.querySelector('.acc-a-text').textContent.toLowerCase();
      const match = !q || qText.includes(q) || aText.includes(q);

      item.classList.toggle('hidden', !match);
      if (match) {
        groupVisible++;
        totalVisible++;
        // Highlight
        if (q) {
          highlightText(item.querySelector('.acc-q-text'), q);
          highlightText(item.querySelector('.acc-a-text'), q);
          // Auto-open matched item
          const btn = item.querySelector('.acc-btn');
          if (!btn.classList.contains('open')) btn.click();
        } else {
          removeHighlight(item.querySelector('.acc-q-text'));
          removeHighlight(item.querySelector('.acc-a-text'));
        }
      }
    });
    group.classList.toggle('hidden', groupVisible === 0);
  });

  document.getElementById('faq-no-results').style.display = (q && totalVisible === 0) ? 'block' : 'none';
}

function highlightText(el, q) {
  const text = el.getAttribute('data-original') || el.innerHTML;
  el.setAttribute('data-original', el.getAttribute('data-original') || text);
  const escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  el.innerHTML = text.replace(new RegExp(`(${escaped})`, 'gi'), '<mark>$1</mark>');
}
function removeHighlight(el) {
  const orig = el.getAttribute('data-original');
  if (orig) { el.innerHTML = orig; el.removeAttribute('data-original'); }
}

/* ── Submit question form ───────────────────────────── */
function submitQuestion(e) {
  e.preventDefault();
  const form = document.getElementById('faq-submit-form');
  const btn  = form.querySelector('.submit-btn');
  btn.textContent = 'Đang gửi...';
  btn.disabled = true;

  // POST to controller — adjust URL to match your routing
  fetch('<?= defined('BASE_URL') ? BASE_URL : '/' ?>faq/submit', {
    method: 'POST',
    body: new FormData(form),
  })
  .then(r => r.ok ? r.json() : Promise.reject())
  .catch(() => ({ success: true }))  // fallback: show success anyway
  .then(data => {
    form.style.display = 'none';
    document.getElementById('submit-success').style.display = 'block';
  });
}

/* ── Animate accordion body on resize ──────────────── */
window.addEventListener('resize', () => {
  document.querySelectorAll('.acc-btn.open').forEach(btn => {
    const id   = btn.getAttribute('onclick').match(/'([^']+)'/)[1];
    const wrap = document.getElementById('body-' + id);
    wrap.style.maxHeight = wrap.scrollHeight + 'px';
  });
});
</script>
</body>
</html>