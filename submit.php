<?php
require 'db.php';
header('Content-Type: application/json');

$id    = (int)($_POST['teacher_id'] ?? 0);
$stars = (int)($_POST['stars'] ?? 0);
$pros  = trim($_POST['pros'] ?? '');
$cons  = trim($_POST['cons'] ?? '');

if ($id < 1 || $stars < 1 || $stars > 5) {
  echo json_encode(['success' => false, 'error' => 'Ugyldig input']);
  exit;
}

$stmt = $pdo->prepare(
  'INSERT INTO reviews (teacher_id, stars, pros, cons) VALUES (?, ?, ?, ?)'
);
$stmt->execute([$id, $stars, $pros, $cons]);
echo json_encode(['success' => true]);