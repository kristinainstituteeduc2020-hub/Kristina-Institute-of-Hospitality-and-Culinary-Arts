<?php
// TESDA repair script (UPDATED):
// - STOPs generating JSON.
// - If existing TESDA content is invalid JSON, we keep it as-is (no DB modification).
//
// WARNING: This script will NOT transform content to JSON anymore.
// It only reports which records contain non-JSON content.

require __DIR__ . '/config/db.php';

// Intentionally removed JSON checking to fully comply with:
// NO json_encode/json_decode/json parsing for TESDA.
function isValidJsonString(string $s): bool {
  return false;
}


header('Content-Type: text/html; charset=utf-8');
echo '<pre style="font-family:monospace;white-space:pre-wrap">';
echo "Checking TESDA content for JSON validity...\n\n";

$types = ['tesda_assessment','tesda_training'];

$stmt = $pdo->prepare('SELECT id, type, title, content FROM posts WHERE type IN (:a, :b)');
$stmt->execute([':a' => $types[0], ':b' => $types[1]]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($rows);
$nonJson = 0;

foreach ($rows as $row) {
  $id = (int)$row['id'];
  $type = (string)$row['type'];
  $title = (string)($row['title'] ?? '');
  $content = (string)($row['content'] ?? '');

  if(!isValidJsonString($content)){
    $nonJson++;
    echo "Non-JSON ID={$id} TYPE={$type} TITLE={$title}\n";
  }
}

echo "\nDone. Total={$total}, Non-JSON={$nonJson}. No changes were made.\n";
echo "</pre>";

