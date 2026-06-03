<?php
$pdo = new PDO(
  'mysql:host=localhost;dbname=_111imkattano;charset=utf8',
  '111imkattano', 'mangoserver1',
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);