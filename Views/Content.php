<?php
// Single-content renderer
require_once __DIR__ . '/../Config/databaseUtil.php';

// connect and fetch single content by id
$db = (new DatabaseUtil())->connect();
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id) {
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
$type = isset($content['type_id']) ? (int)$content['type_id'] : null;
if ($type === null) {
    $link = $content['link'] ?? '';
    if (stripos($link, 'youtube') !== false || stripos($link, 'youtu.be') !== false) {
        $type = 2;
    } elseif (preg_match('/\.(pdf|docx?|pptx?)($|\?)/i', $link)) {
        $type = 3;
    } elseif (preg_match('/\.(html?|htm)($|\?)/i', $link)) {
        $type = 4;
    } else {
        $type = preg_match('/\.(jpe?g|png|gif|webp)($|\?)/i', $link) ? 1 : 3;
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
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 20px;
            color: #333;
        }
        .card {
            max-width: 900px;
            margin: 24px auto;
            background: #fff;
            padding: 20px 24px;
            border-radius: 8px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }
        .title {
            font-size: 1.6rem;
            color: #1e3c72;
            margin-bottom: 12px;
        }
        .poster img {
            width: 100%;
            height: auto;
            border-radius: 6px;
        }
        .video {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 6px;
        }
        .video iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .doc {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        .btn {
            background: #007BFF;
            color: white;
            padding: 10px 14px;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .btn:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }
        .meta {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background: #6c757d;
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        .back-btn:active {
            background: #444;
            transform: translateY(0);
        }
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
                    <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($vid) ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                </div>
            <?php else: ?>
                <p>No embeddable video ID found. 
                    <a href="<?= htmlspecialchars($content['link']) ?>" target="_blank" class="btn">Open link</a>
                </p>
            <?php endif; ?>

        <?php elseif ($type === 4): // standalone HTML page ?>
            <?php
                header("Location: " . $content['link']);
                exit();
            ?>

        <?php else: // document or fallback ?>
            <div class="doc">
                <div><?= nl2br(htmlspecialchars($content['title'])); ?></div>
                <a class="btn" href="<?= htmlspecialchars($content['link']) ?>" target="_blank">View / Download</a>
            </div>
        <?php endif; ?>

        <!-- ✅ Back button -->
        <a href="/Views/content_overview.php" class="back-btn">← Back to all content</a>
    </div>
</body>
</html>
