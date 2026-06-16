<?php
require __DIR__ . '/config/db.php';

$type = 'tesda_training';

$id = intval($_GET['id'] ?? 0);
if($id <= 0){
  http_response_code(400);
  echo 'Missing id.';
  exit;
}

$stmt = $pdo->prepare('SELECT id, type, title, content, author_name, image_path, attachment_path, video_path, created_at FROM posts WHERE id=:id AND type=:type LIMIT 1');
$stmt->execute([':id'=>$id, ':type'=>$type]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$item){
  http_response_code(404);
  echo 'Not found.';
  exit;
}

function h($s){return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');}

$img = $item['image_path'] ? 'assets/uploads/'.rawurlencode($item['image_path']) : '';
$video = $item['video_path'] ? 'assets/uploads/'.rawurlencode($item['video_path']) : '';
$att = $item['attachment_path'] ? 'assets/uploads/'.rawurlencode($item['attachment_path']) : '';

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Community Training Details</title>
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    .page{padding:26px 0 40px;}
    .wrap{max-width:980px; margin:0 auto; padding:0 16px;}
    .details-card{background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:18px; overflow:hidden; box-shadow:var(--shadow)}
    .details-media{background:#0d1528; max-height:380px; overflow:hidden;}
    .details-media img{width:100%; height:auto; display:block;}
    .details-body{padding:18px;}
    .details-title{margin:0; font-size:24px;}
    .details-meta{margin-top:6px; color:var(--muted); font-size:13px; display:flex; gap:12px; flex-wrap:wrap;}
    .details-content{margin-top:14px; color:var(--text); line-height:1.65; white-space:pre-wrap;}
    .details-actions{margin-top:18px; display:flex; gap:12px; flex-wrap:wrap;}
    .btn-link{display:inline-flex; align-items:center; justify-content:center; padding:11px 14px; border-radius:12px; color:#0b1220; text-decoration:none; font-weight:750; background:linear-gradient(135deg,var(--primary),var(--primary2));}
    .btn-out{background:transparent; color:var(--muted); border:1px solid var(--border);}
    .details-grid{display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-top:14px;}
  </style>
</head>
<body class="has-bg-image">
  <header class="site-header">
    <div class="container header-inner">
      <div class="brand">
        <div class="brand-mark" aria-hidden="true">
          <img class="brand-logo" src="assets/KIHCA LOGO MALIWANAG.png" alt="" />
        </div>
        <div>
          <div class="brand-title">Kristina Institute</div>
          <div class="brand-subtitle">Community Training Details</div>
        </div>
      </div>
      <nav class="nav">
        <a href="tesda.html" class="nav-link">Back</a>
      </nav>
    </div>
  </header>

  <main class="page">
    <div class="wrap">
      <div class="details-card">
        <?php if($img): ?>
          <div class="details-media">
            <img src="<?php echo h($img); ?>" alt="<?php echo h($item['title']); ?>" />
          </div>
        <?php endif; ?>

        <div class="details-body">
          <h1 class="details-title"><?php echo h($item['title']); ?></h1>
          <div class="details-meta">
            <span><?php echo h($item['type']); ?></span>
            <?php if(!empty($item['author_name'])): ?><span>By <?php echo h($item['author_name']); ?></span><?php endif; ?>
            <span><?php echo h($item['created_at'] ?? '') ? h((new DateTime($item['created_at']))->format('Y-m-d')) : ''; ?></span>
          </div>

          <?php if($item['content'] ?? ''): ?>
            <div class="details-content"><?php echo nl2br(h($item['content'])); ?></div>
          <?php endif; ?>

          <div class="details-grid">
            <div>
              <?php if($video): ?>
                <div class="section-block">
                  <h3 style="margin:0 0 10px; font-size:16px;">Video</h3>
                  <video controls playsinline style="width:100%; max-height:320px; object-fit:contain; display:block;">
                    <source src="<?php echo h($video); ?>" />
                  </video>
                </div>
              <?php endif; ?>

              <?php if($att): ?>
                <div class="section-block">
                  <h3 style="margin:18px 0 10px; font-size:16px;">Attachment (PDF/Files)</h3>
                  <a class="btn-link" href="<?php echo h($att); ?>" target="_blank" rel="noreferrer">Download Attachment</a>
                </div>
              <?php endif; ?>
            </div>
            <div>
              <div class="section-block">
                <h3 style="margin:0 0 10px; font-size:16px;">Uploaded Image</h3>
                <?php if($img): ?>
                  <a class="btn-link btn-out" href="<?php echo h($img); ?>" target="_blank" rel="noreferrer">View Image</a>
                <?php else: ?>
                  <div class="muted">No image uploaded.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="details-actions">
            <a class="btn-link btn-out" href="tesda.html">Back to TESDA Programs</a>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>

