<?php
require 'db.php';

$stmt = $pdo->query('
  SELECT t.id, t.name, t.subject,
         ROUND(AVG(r.stars), 1) AS avg_stars,
         COUNT(r.id) AS review_count
  FROM teachers t
  LEFT JOIN reviews r ON r.teacher_id = t.id
  GROUP BY t.id
  ORDER BY t.name ASC
');
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lærervurdering</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:        #0e0e0f;
      --surface:   #161618;
      --border:    #2a2a2e;
      --accent:    #f0c040;
      --accent2:   #e07040;
      --text:      #f0ede8;
      --muted:     #7a7875;
      --radius:    14px;
    }

    html { scroll-behavior: smooth; }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-weight: 300;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* noise overlay */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
      pointer-events: none;
      z-index: 0;
      opacity: 0.4;
    }

    /* HEADER */
    header {
      position: relative;
      z-index: 1;
      padding: 72px 48px 56px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 24px;
    }

    .header-left h1 {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: clamp(2.4rem, 5vw, 4rem);
      line-height: 1;
      letter-spacing: -0.03em;
    }

    .header-left h1 span {
      color: var(--accent);
    }

    .header-left p {
      margin-top: 12px;
      color: var(--muted);
      font-size: 0.95rem;
      letter-spacing: 0.02em;
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--muted);
      font-size: 0.85rem;
      font-family: 'Syne', sans-serif;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      white-space: nowrap;
    }

    .header-right .dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: var(--accent);
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50%       { opacity: 0.4; transform: scale(0.7); }
    }

    /* SEARCH */
    .search-bar {
      position: relative;
      z-index: 1;
      padding: 32px 48px 0;
    }

    .search-bar input {
      width: 100%;
      max-width: 420px;
      background: var(--surface);
      border: 1px solid var(--border);
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem;
      padding: 12px 18px 12px 44px;
      border-radius: 8px;
      outline: none;
      transition: border-color 0.2s;
    }

    .search-bar input::placeholder { color: var(--muted); }
    .search-bar input:focus { border-color: var(--accent); }

    .search-bar .icon {
      position: absolute;
      left: 66px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted);
      pointer-events: none;
      font-size: 1rem;
    }

    /* GRID */
    main {
      position: relative;
      z-index: 1;
      padding: 40px 48px 80px;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
    }

    /* CARD */
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 28px;
      text-decoration: none;
      color: inherit;
      display: flex;
      flex-direction: column;
      gap: 16px;
      transition: border-color 0.2s, transform 0.2s;
      animation: fadeUp 0.4s both;
    }

    .card:hover {
      border-color: var(--accent);
      transform: translateY(-3px);
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    <?php foreach ($teachers as $i => $_): ?>
    .card:nth-child(<?= $i + 1 ?>) { animation-delay: <?= $i * 0.06 ?>s; }
    <?php endforeach; ?>

    .card-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
    }

    .avatar {
      width: 48px; height: 48px;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--accent2), var(--accent));
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: 1.1rem;
      color: var(--bg);
      flex-shrink: 0;
    }

    .badge {
      font-family: 'Syne', sans-serif;
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      padding: 4px 10px;
      border-radius: 99px;
      border: 1px solid var(--border);
      color: var(--muted);
    }

    .card h2 {
      font-family: 'Syne', sans-serif;
      font-weight: 700;
      font-size: 1.15rem;
      letter-spacing: -0.01em;
    }

    .card .subject {
      font-size: 0.85rem;
      color: var(--muted);
      margin-top: 2px;
    }

    .rating-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: auto;
    }

    .stars {
      display: flex;
      gap: 2px;
    }

    .stars span {
      font-size: 1rem;
      line-height: 1;
    }

    .stars span.filled  { color: var(--accent); }
    .stars span.empty   { color: var(--border); }

    .avg-number {
      font-family: 'Syne', sans-serif;
      font-weight: 700;
      font-size: 1rem;
      color: var(--accent);
    }

    .review-count {
      font-size: 0.8rem;
      color: var(--muted);
      margin-left: auto;
    }

    /* empty state */
    .empty-state {
      grid-column: 1 / -1;
      text-align: center;
      padding: 80px 24px;
      color: var(--muted);
    }

    .empty-state .big { font-size: 3rem; margin-bottom: 16px; }
    .empty-state p { font-family: 'Syne', sans-serif; font-size: 1rem; }

    /* no-results (hidden by default) */
    #no-results {
      display: none;
      grid-column: 1 / -1;
      text-align: center;
      padding: 60px 24px;
      color: var(--muted);
      font-family: 'Syne', sans-serif;
    }

    /* responsive */
    @media (max-width: 600px) {
      header, .search-bar, main { padding-left: 20px; padding-right: 20px; }
      header { padding-top: 40px; flex-direction: column; align-items: flex-start; }
    }
  </style>
</head>
<body>

<header>
  <div class="header-left">
    <h1>Lærer<span>vurdering</span></h1>
    <p><?= count($teachers) ?> lærere · Rate og les erfaringer</p>
  </div>
  <div class="header-right">
    <span class="dot"></span>
    Anonymt
  </div>
</header>

<div class="search-bar">
  <span class="icon">🔍</span>
  <input type="text" id="search" placeholder="Søk etter lærer eller fag…" autocomplete="off">
</div>

<main id="grid">
  <?php if (empty($teachers)): ?>
    <div class="empty-state">
      <div class="big">🎓</div>
      <p>Ingen lærere er lagt til ennå.</p>
    </div>
  <?php else: ?>
    <?php foreach ($teachers as $t):
      $avg     = $t['avg_stars'] ?? 0;
      $count   = (int)$t['review_count'];
      $initials = implode('', array_map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)), explode(' ', $t['name'])));
      $initials = mb_substr($initials, 0, 2);
    ?>
    <a class="card" href="teacher.php?id=<?= $t['id'] ?>" data-name="<?= htmlspecialchars(strtolower($t['name'])) ?>" data-subject="<?= htmlspecialchars(strtolower($t['subject'] ?? '')) ?>">
      <div class="card-top">
        <div class="avatar"><?= htmlspecialchars($initials) ?></div>
        <?php if ($t['subject']): ?>
          <span class="badge"><?= htmlspecialchars($t['subject']) ?></span>
        <?php endif; ?>
      </div>

      <div>
        <h2><?= htmlspecialchars($t['name']) ?></h2>
        <?php if ($t['subject']): ?>
          <p class="subject"><?= htmlspecialchars($t['subject']) ?></p>
        <?php endif; ?>
      </div>

      <div class="rating-row">
        <div class="stars">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <span class="<?= $i <= round($avg) ? 'filled' : 'empty' ?>">★</span>
          <?php endfor; ?>
        </div>
        <?php if ($avg > 0): ?>
          <span class="avg-number"><?= number_format($avg, 1) ?></span>
        <?php else: ?>
          <span style="color:var(--muted);font-size:.85rem">Ingen vurderinger</span>
        <?php endif; ?>
        <span class="review-count"><?= $count ?> <?= $count === 1 ? 'vurdering' : 'vurderinger' ?></span>
      </div>
    </a>
    <?php endforeach; ?>
    <div id="no-results">Ingen lærere matcher søket ditt.</div>
  <?php endif; ?>
</main>

<script>
  const input = document.getElementById('search');
  const cards = document.querySelectorAll('.card');
  const noResults = document.getElementById('no-results');

  input.addEventListener('input', () => {
    const q = input.value.toLowerCase().trim();
    let visible = 0;
    cards.forEach(card => {
      const match = card.dataset.name.includes(q) || card.dataset.subject.includes(q);
      card.style.display = match ? '' : 'none';
      if (match) visible++;
    });
    if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
  });
</script>

</body>
</html>