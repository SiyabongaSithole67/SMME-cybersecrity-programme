<?php
/**
 * Content View Page
 * Displays cybersecurity awareness training content including:
 * - Poster from Deliverable 1
 * - YouTube content links
 * - Training documents
 */

// Assuming you're passing $currentUser and calling listContent() before including this view
// Example in your main page:
// $contentController = new ContentController();
// $contentList = $contentController->listContent($currentUser);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cybersecurity Awareness Training Content</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content-section {
            padding: 40px;
        }

        .section-title {
            font-size: 1.8em;
            color: #1e3c72;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }

        /* Poster Section */
        .poster-container {
            margin-bottom: 50px;
            text-align: center;
        }

        .poster-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            margin-top: 20px;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .content-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .content-card h3 {
            color: #1e3c72;
            margin-bottom: 15px;
            font-size: 1.3em;
        }

        .content-link {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
            margin-top: 10px;
        }

        .content-link:hover {
            background: #5568d3;
        }

        /* YouTube Video Section */
        .youtube-section {
            margin: 50px 0;
        }

        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            margin-top: 20px;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        /* Documents Section */
        .documents-section {
            margin-top: 50px;
        }

        .document-item {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.3s ease;
        }

        .document-item:hover {
            background: #e9ecef;
        }

        .document-icon {
            font-size: 2em;
            margin-right: 15px;
            color: #667eea;
        }

        .document-info {
            flex: 1;
        }

        .document-title {
            font-weight: bold;
            color: #1e3c72;
            margin-bottom: 5px;
        }

        .no-content {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }

        /* Admin Button */
        .admin-controls {
            padding: 20px 40px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            text-align: center;
        }

        .btn-add {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s ease;
        }

        .btn-add:hover {
            background: #218838;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8em;
            }

            .content-section {
                padding: 20px;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🔒 Cybersecurity Awareness Training</h1>
            <p>Empowering your SMME with security knowledge</p>
        </div>

        <div class="content-section">
            <!-- Poster Section -->
            <div class="poster-container">
                <h2 class="section-title">📋 Deliverable 1 Poster</h2>
                <!-- Replace with your actual poster path -->
                <img src="assets/images/cybersecurity-poster.jpg" alt="Cybersecurity Awareness Poster">
                <p style="margin-top: 15px; color: #6c757d;">Download the poster: 
                    <a href="assets/images/cybersecurity-poster.jpg" download class="content-link">Download</a>
                </p>
            </div>

            <!-- YouTube Content Section -->
            <div class="youtube-section">
                <h2 class="section-title">🎥 Training Videos</h2>
                <?php
                // Filter content that contains YouTube links
                $youtubeContent = array_filter($contentList ?? [], function($content) {
                    return stripos($content['link'], 'youtube') !== false || stripos($content['link'], 'youtu.be') !== false;
                });

                if (!empty($youtubeContent)):
                    foreach ($youtubeContent as $video):
                        // Extract YouTube video ID
                        $videoId = '';
                        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $video['link'], $matches)) {
                            $videoId = $matches[1];
                        }
                ?>
                    <div style="margin-bottom: 40px;">
                        <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                        <?php if ($videoId): ?>
                            <div class="video-container">
                                <iframe 
                                    src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoId); ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                                </iframe>
                            </div>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($video['link']); ?>" target="_blank" class="content-link">
                                Watch Video
                            </a>
                        <?php endif; ?>
                    </div>
                <?php 
                    endforeach;
                else:
                ?>
                    <p class="no-content">No video content available yet.</p>
                <?php endif; ?>
            </div>

            <!-- Training Documents Section -->
            <div class="documents-section">
                <h2 class="section-title">📚 Training Documents</h2>
                <?php
                // Filter content that doesn't contain YouTube links (documents)
                $documentContent = array_filter($contentList ?? [], function($content) {
                    return stripos($content['link'], 'youtube') === false && stripos($content['link'], 'youtu.be') === false;
                });

                if (!empty($documentContent)):
                    foreach ($documentContent as $document):
                ?>
                    <div class="document-item">
                        <span class="document-icon">📄</span>
                        <div class="document-info">
                            <div class="document-title"><?php echo htmlspecialchars($document['title']); ?></div>
                        </div>
                        <a href="<?php echo htmlspecialchars($document['link']); ?>" target="_blank" class="content-link">
                            View/Download
                        </a>
                    </div>
                <?php 
                    endforeach;
                else:
                ?>
                    <p class="no-content">No training documents available yet.</p>
                <?php endif; ?>
            </div>

            <!-- All Content Grid (Alternative view) -->
            <div style="margin-top: 50px;">
                <h2 class="section-title">📖 All Content</h2>
                <?php if (!empty($contentList)): ?>
                    <div class="content-grid">
                        <?php foreach ($contentList as $content): ?>
                            <div class="content-card">
                                <h3><?php echo htmlspecialchars($content['title']); ?></h3>
                                <a href="<?php echo htmlspecialchars($content['link']); ?>" target="_blank" class="content-link">
                                    Access Content
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-content">No content available yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Admin Controls (only for SystemAdmin) -->
        <?php if (isset($currentUser) && $currentUser->getRoleId() == 1): ?>
        <div class="admin-controls">
            <a href="add-content.php" class="btn-add">➕ Add New Content</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>