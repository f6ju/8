<?php
require '../db.php';
header('Content-Type: application/json');

$id    = (int)($_POST['teacher_id'] ?? 0);
$stars = (int)($_POST['stars'] ?? 0);
$pros  = trim($_POST['pros'] ?? '');
$cons  = trim($_POST['cons'] ?? '');

if ($id < 1 || $stars < 1 || $stars > 5) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Ugyldig input.']);
  exit;
}

$stmt = $pdo->prepare(
  'INSERT INTO reviews (teacher_id, stars, pros, cons) VALUES (?, ?, ?, ?)'
);
$stmt->execute([$id, $stars, $pros, $cons]);

// Hent ny snittrating og antall
$avg_stmt = $pdo->prepare('SELECT ROUND(AVG(stars),1) AS avg, COUNT(*) AS cnt FROM reviews WHERE teacher_id = ?');
$avg_stmt->execute([$id]);
$stats = $avg_stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
  'success'   => true,
  'new_avg'   => (float)$stats['avg'],
  'new_count' => (int)$stats['cnt'],
  'review'    => [
    'stars' => $stars,
    'pros'  => $pros,
    'cons'  => $cons,
    'date'  => date('d.m.Y'),
  ]
]);