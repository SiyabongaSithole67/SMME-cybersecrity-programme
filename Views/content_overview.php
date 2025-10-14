<?php
require_once __DIR__ . '/../Config/databaseUtil.php';
$db = (new DatabaseUtil())->connect();
$stmt = $db->query('SELECT id, title, link, created_at FROM contents ORDER BY created_at DESC');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/_user_badge.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>All Content</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; padding:20px }
    .wrap { max-width:900px; margin:24px auto; }
    .item { background:#fff; border-radius:8px; padding:14px 18px; margin-bottom:12px; box-shadow:0 6px 18px rgba(0,0,0,0.06); display:flex; justify-content:space-between; align-items:center }
    .meta { color:#6c757d; font-size:0.9rem }
    a.btn { background:#667eea; color:#fff; padding:8px 12px; border-radius:6px; text-decoration:none }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>All Content</h1>
    <?php if (empty($rows)): ?>
      <p>No content yet.</p>
    <?php else: ?>
      <?php foreach ($rows as $r): ?>
        <div class="item">
          <div>
            <div style="font-weight:600"><?php echo htmlspecialchars($r['title']) ?></div>
            <div class="meta">Added: <?php echo htmlspecialchars($r['created_at']) ?></div>
          </div>
          <div>
            <a class="btn" href="/Views/Content.php?id=<?php echo (int)$r['id'] ?>">View</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>
