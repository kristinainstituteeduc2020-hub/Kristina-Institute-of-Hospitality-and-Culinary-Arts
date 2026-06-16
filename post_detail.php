<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../config/db.php';

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

$allowedTypes = ['tesda_assessment','tesda_training','utpras','news','updates','advertisement'];
// In your DB, posts use `type` values like tesda_assessment / tesda_training.
if (!in_array($type, ['tesda_assessment','tesda_training','news','updates','advertisement'], true)) {
  // For safety, only allow known ones. If unknown, default to tesda_assessment (but still validate below).
  $type = 'news';
}

if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Missing id']);
  exit;
}

$stmt = $pdo->prepare(
  'SELECT id, type, title, content, author_name, image_path, attachment_path, video_path, created_at
   FROM posts
   WHERE id=:id AND type=:type
   LIMIT 1'
);
$stmt->execute([':id'=>$id, ':type'=>$type]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
  http_response_code(404);
  echo json_encode(['ok'=>false,'error'=>'Not found']);
  exit;
}

function file_url($path){
  if(!$path) return '';
  return 'assets/uploads/' . rawurlencode($path);
}

echo json_encode([
  'ok' => true,
  'item' => [
    'id' => (int)$item['id'],
    'type' => $item['type'],
    'title' => $item['title'],
    'content' => $item['content'],
    'author_name' => $item['author_name'],
    'created_at' => $item['created_at'],
    'image_path' => file_url($item['image_path'] ?? ''),
    'attachment_path' => file_url($item['attachment_path'] ?? ''),
    'video_path' => file_url($item['video_path'] ?? ''),
  ]
]);

