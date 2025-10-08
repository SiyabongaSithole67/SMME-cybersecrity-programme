<?php
// Single-content renderer
require_once __DIR__ . '/../Config/databaseUtil.php';

// connect and fetch single content by id
$db = (new DatabaseUtil())->connect();
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id) {
    // If no id provided, redirect to overview
    header('Location: /Views/content_overview.php');
    exit();
}

$stmt = $db->prepare('SELECT * FROM contents WHERE id = ?');
$stmt->execute([$id]);
$content = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$content) {
    echo "<p style=\"padding:20px;\">Content not found.</p>";
    exit();
}

// Determine type: prefer explicit type_id, otherwise infer from link
// type mapping: 1 = poster (image), 2 = youtube/video, 3 = document (pdf/link)
$type = isset($content['type_id']) ? (int)$content['type_id'] : null;
if ($type === null) {
    $link = $content['link'] ?? '';
    if (stripos($link, 'youtube') !== false || stripos($link, 'youtu.be') !== false) {
        $type = 2;
    } elseif (preg_match('/\.(pdf|docx?|pptx?)($|\?)/i', $link)) {
        $type = 3;
    } elseif (preg_match('/\.(html?|htm)($|\?)/i', $link)) {
        $type = 4; // HTML page
    } else {
        // default to poster if link looks like an image, else document
        if (preg_match('/\.(jpe?g|png|gif|webp)($|\?)/i', $link)) $type = 1;
        else $type = 3;
    }
}

// helper: extract youtube id
function extractYouTubeId($url) {
   if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $m)) {
        return $m[1];
    }
    if (preg_match('/[?&]v=([a-zA-Z0-9_-]+)/', $url, $m)) return $m[1];
    return '';
}

?>
<?php include __DIR__ . '/_user_badge.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($content['title']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .card { max-width: 900px; margin: 24px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
        .title { font-size: 1.6rem; color: #1e3c72; margin-bottom: 12px; }
        .poster img { width:100%; height:auto; border-radius:6px; }
        .video { position:relative; padding-bottom:56.25%; height:0; overflow:hidden; border-radius:6px; }
        .video iframe { position:absolute; top:0; left:0; width:100%; height:100%; }
        .doc { display:flex; justify-content:space-between; align-items:center; }
        .btn { background:#667eea; color:white; padding:10px 14px; border-radius:6px; text-decoration:none; }
        .meta { color:#6c757d; font-size:0.9rem; margin-bottom:10px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="title"><?php echo htmlspecialchars($content['title']); ?></div>
        <div class="meta">Published: <?php echo htmlspecialchars($content['created_at'] ?? ''); ?></div>

        <?php if ($type === 1): // poster/image ?>
            <div class="poster">
                <img src="<?= htmlspecialchars($content['link']) ?>" alt="<?= htmlspecialchars($content['title']) ?>">
            </div>
        <?php elseif ($type === 2): // youtube/video ?>
            <?php $vid = extractYouTubeId($content['link']); ?>
            <?php if ($vid): ?>
                <div class="video">
                    <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($vid) ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            <?php else: ?>
                <p>No embeddable video ID found. <a href="<?= htmlspecialchars($content['link']) ?>" target="_blank">Open link</a></p>
            <?php endif; ?>
        <?php elseif ($type === 4): // standalone HTML page ?>
            <?php
                // Redirect immediately to the HTML page
                header("Location: " . $content['link']);
                exit();
            ?>
        <?php else: // document or fallback ?>
            <div class="doc">
                <div><?php echo nl2br(htmlspecialchars($content['title'])); ?></div>
                <a class="btn" href="<?= htmlspecialchars($content['link']) ?>" target="_blank">View / Download</a>
            </div>
        <?php endif; ?>

        <p style="margin-top:18px;"><a href="/Views/content_overview.php">← Back to all content</a></p>
    </div>
</body>
</html>