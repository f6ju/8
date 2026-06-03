<?php
require 'db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id < 1) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM teachers WHERE id = ?');
$stmt->execute([$id]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$teacher) { header('Location: index.php'); exit; }

$rstmt = $pdo->prepare('
  SELECT stars, pros, cons, created_at
  FROM reviews
  WHERE teacher_id = ?
  ORDER BY created_at DESC
');
$rstmt->execute([$id]);
$reviews = $rstmt->fetchAll(PDO::FETCH_ASSOC);

$avg = count($reviews) ? round(array_sum(array_column($reviews, 'stars')) / count($reviews), 1) : 0;

$initials = implode('', array_map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)), explode(' ', $teacher['name'])));
$initials = mb_substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($teacher['name']) ?> – Lærervurdering</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="noise"></div>

<header class="teacher-header">
  <a href="index.php" class="back-link">← Alle lærere</a>
  <div class="teacher-hero">
    <div class="avatar-large"><?= htmlspecialchars($initials) ?></div>
    <div class="teacher-meta">
      <h1><?= htmlspecialchars($teacher['name']) ?></h1>
      <?php if ($teacher['subject']): ?>
        <span class="subject-tag"><?= htmlspecialchars($teacher['subject']) ?></span>
      <?php endif; ?>
      <div class="hero-rating">
        <div class="stars-display">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <span class="<?= $i <= round($avg) ? 'filled' : 'empty' ?>">★</span>
          <?php endfor; ?>
        </div>
        <span class="avg-big"><?= $avg > 0 ? number_format($avg, 1) : '–' ?></span>
        <span class="review-meta"><?= count($reviews) ?> <?= count($reviews) === 1 ? 'vurdering' : 'vurderinger' ?></span>
      </div>
    </div>
  </div>
</header>

<div class="teacher-layout">

  <!-- REVIEWS -->
  <section class="reviews-section">
    <h2 class="section-title">Vurderinger</h2>

    <?php if (empty($reviews)): ?>
      <div class="empty-reviews">
        <span class="big-emoji">💬</span>
        <p>Ingen vurderinger ennå. Bli den første!</p>
      </div>
    <?php else: ?>
      <div class="reviews-list" id="reviews-list">
        <?php foreach ($reviews as $i => $r): ?>
        <div class="review-card" style="animation-delay:<?= $i * 0.06 ?>s">
          <div class="review-top">
            <div class="review-stars">
              <?php for ($s = 1; $s <= 5; $s++): ?>
                <span class="<?= $s <= $r['stars'] ? 'filled' : 'empty' ?>">★</span>
              <?php endfor; ?>
            </div>
            <span class="review-date"><?= date('d.m.Y', strtotime($r['created_at'])) ?></span>
          </div>
          <?php if ($r['pros']): ?>
            <div class="review-row pros">
              <span class="pill pros-pill">✅ Bra</span>
              <p><?= htmlspecialchars($r['pros']) ?></p>
            </div>
          <?php endif; ?>
          <?php if ($r['cons']): ?>
            <div class="review-row cons">
              <span class="pill cons-pill">❌ Minus</span>
              <p><?= htmlspecialchars($r['cons']) ?></p>
            </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- FORM -->
  <aside class="form-aside">
    <div class="form-card" id="review-form-card">
      <h2 class="section-title">Legg igjen vurdering</h2>

      <form id="review-form" novalidate>
        <input type="hidden" name="teacher_id" value="<?= $teacher['id'] ?>">

        <div class="form-group">
          <label>Stjerner</label>
          <div class="star-picker">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <span class="star-btn" data-value="<?= $i ?>">★</span>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="stars" id="stars-value">
          <p class="star-hint" id="star-hint">Velg antall stjerner</p>
        </div>

        <div class="form-group">
          <label for="pros">Hva er bra?</label>
          <textarea id="pros" name="pros" placeholder="Flink til å forklare, engasjerende…" rows="3"></textarea>
        </div>

        <div class="form-group">
          <label for="cons">Hva kan bli bedre?</label>
          <textarea id="cons" name="cons" placeholder="For mye lekser, ustrukturert…" rows="3"></textarea>
        </div>

        <button type="submit" class="submit-btn" id="submit-btn">Send vurdering</button>
        <p class="form-error" id="form-error"></p>
      </form>

      <div class="success-state" id="success-state" style="display:none">
        <span class="big-emoji">🎉</span>
        <p>Takk for vurderingen!</p>
        <button class="submit-btn outline" id="another-btn">Legg til en ny</button>
      </div>
    </div>
  </aside>

</div>

<script>
  const teacherId = <?= $teacher['id'] ?>;
</script>
<script src="script.js"></script>
</body>
</html>