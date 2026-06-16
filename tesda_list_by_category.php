<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../config/db.php';

$category = trim($_GET['category'] ?? '');
if($category === ''){
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Missing category']);
  exit;
}

$limit = intval($_GET['limit'] ?? 50);
if($limit <= 0 || $limit > 50) $limit = 50;

// Only TESDA posts. Category is stored in posts.tesda_category.
$stmt = $pdo->prepare(
  'SELECT id, type, title, content, author_name, image_path, attachment_path, video_path, created_at, tesda_category
   FROM posts
   WHERE type IN ("tesda_assessment","tesda_training")
     AND tesda_category = :cat
   ORDER BY created_at DESC
   LIMIT :lim'
);

$stmt->bindValue(':cat', $category, PDO::PARAM_STR);
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

function file_url($path){
  if(!$path) return '';
  return 'assets/uploads/' . rawurlencode($path);
}

$items = array_map(function($it){
  return [
    'id' => (int)($it['id'] ?? 0),
    'type' => $it['type'] ?? '',
    'title' => $it['title'] ?? '',
    'content' => $it['content'] ?? '',
    'author_name' => $it['author_name'] ?? '',
    'created_at' => $it['created_at'] ?? null,
    'image_path' => file_url($it['image_path'] ?? ''),
    'attachment_path' => file_url($it['attachment_path'] ?? ''),
    'video_path' => file_url($it['video_path'] ?? ''),
    'tesda_category' => $it['tesda_category'] ?? ''
  ];
}, $items);

echo json_encode(['ok'=>true,'items'=>$items]);

