<?php
/**
 * Assessment View Page
 * Displays available assessments and allows users to take them
 * Shows results based on user role
 */

// Assuming you're passing $currentUser and calling methods before including this view
// Example in your main page:
// $assessmentController = new AssessmentController();
// $assessmentList = $assessmentController->listAssessments($currentUser);
// $userResults = $assessmentController->viewResults($currentUser);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cybersecurity Assessments</title>
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
            max-width: 1400px;
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
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e9ecef;
        }

        .tab {
            padding: 15px 30px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 1.1em;
            color: #6c757d;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab:hover {
            color: #667eea;
        }

        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
            font-weight: bold;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Assessment Cards */
        .assessment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .assessment-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 5px solid #667eea;
        }

        .assessment-card.formative {
            border-left-color: #28a745;
        }

        .assessment-card.summative {
            border-left-color: #dc3545;
        }

        .assessment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .assessment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .assessment-title {
            color: #1e3c72;
            font-size: 1.4em;
            margin-bottom: 5px;
        }

        .assessment-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-formative {
            background: #d4edda;
            color: #155724;
        }

        .badge-summative {
            background: #f8d7da;
            color: #721c24;
        }

        .assessment-description {
            color: #6c757d;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .assessment-footer {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        /* Results Table */
        .results-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .results-table th {
            background: #1e3c72;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .results-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .results-table tr:last-child td {
            border-bottom: none;
        }

        .results-table tr:hover {
            background: #f8f9fa;
        }

        .score-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }

        .score-excellent {
            background: #d4edda;
            color: #155724;
        }

        .score-good {
            background: #d1ecf1;
            color: #0c5460;
        }

        .score-average {
            background: #fff3cd;
            color: #856404;
        }

        .score-poor {
            background: #f8d7da;
            color: #721c24;
        }

        .no-content {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
            font-style: italic;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .no-content-icon {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-value {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.95em;
            opacity: 0.9;
        }

        /* Admin Controls */
        .admin-controls {
            padding: 20px 40px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            text-align: center;
        }

        @media (max-width: 768px) {
            .assessment-grid {
                grid-template-columns: 1fr;
            }

            .tabs {
                flex-direction: column;
            }

            .content-section {
                padding: 20px;
            }

            .results-table {
                font-size: 0.9em;
            }

            .results-table th,
            .results-table td {
                padding: 10px;
            }
        }

        /* Filter Section */
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group label {
            font-weight: 500;
            color: #1e3c72;
        }

        .filter-group select {
            padding: 8px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            background: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📝 Cybersecurity Assessments</h1>
            <p>Test your knowledge and track your progress</p>
        </div>

        <div class="content-section">
            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="switchTab('available')">Available Assessments</button>
                <button class="tab" onclick="switchTab('results')">My Results</button>
                <?php if (isset($currentUser) && ($currentUser->getRoleId() == 1 || $currentUser->getRoleId() == 2)): ?>
                <button class="tab" onclick="switchTab('all-results')">All Results</button>
                <?php endif; ?>
            </div>

            <!-- Tab 1: Available Assessments -->
            <div id="available" class="tab-content active">
                <h2 class="section-title">
                    📚 Available Assessments
                    <span style="font-size: 0.6em; color: #6c757d;">
                        (<?php echo count($assessmentList ?? []); ?> total)
                    </span>
                </h2>

                <?php if (!empty($assessmentList)): ?>
                    <div class="assessment-grid">
                        <?php foreach ($assessmentList as $assessment): ?>
                            <div class="assessment-card <?php echo htmlspecialchars($assessment['type']); ?>">
                                <div class="assessment-header">
                                    <div>
                                        <h3 class="assessment-title">
                                            <?php echo htmlspecialchars($assessment['title']); ?>
                                        </h3>
                                    </div>
                                    <span class="assessment-badge badge-<?php echo htmlspecialchars($assessment['type']); ?>">
                                        <?php echo htmlspecialchars($assessment['type']); ?>
                                    </span>
                                </div>
                                
                                <p class="assessment-description">
                                    <?php echo htmlspecialchars($assessment['description']); ?>
                                </p>

                                <?php if ($assessment['content_id']): ?>
                                    <p style="color: #667eea; font-size: 0.9em; margin-bottom: 15px;">
                                        📎 Linked to content
                                    </p>
                                <?php endif; ?>

                                <div class="assessment-footer">
                                    <a href="take-assessment.php?id=<?php echo $assessment['id']; ?>" class="btn btn-primary">
                                        Start Assessment
                                    </a>
                                    <a href="assessment-details.php?id=<?php echo $assessment['id']; ?>" class="btn btn-secondary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-content">
                        <div class="no-content-icon">📋</div>
                        <h3>No Assessments Available</h3>
                        <p>There are currently no assessments available. Check back later!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab 2: My Results -->
            <div id="results" class="tab-content">
                <h2 class="section-title">📊 My Assessment Results</h2>

                <?php
                // Calculate statistics for current user
                $myResults = $userResults ?? [];
                $totalAttempts = count($myResults);
                $averageScore = $totalAttempts > 0 ? array_sum(array_column($myResults, 'score')) / $totalAttempts : 0;
                $passedCount = count(array_filter($myResults, function($r) { return $r['score'] >= 70; }));
                ?>

                <?php if ($totalAttempts > 0): ?>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $totalAttempts; ?></div>
                            <div class="stat-label">Total Attempts</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($averageScore, 1); ?>%</div>
                            <div class="stat-label">Average Score</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $passedCount; ?></div>
                            <div class="stat-label">Passed (≥70%)</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">
                                <?php echo $totalAttempts > 0 ? number_format(($passedCount / $totalAttempts) * 100, 0) : 0; ?>%
                            </div>
                            <div class="stat-label">Pass Rate</div>
                        </div>
                    </div>

                    <div class="results-container">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Assessment ID</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Completed At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myResults as $result): 
                                    $score = $result['score'];
                                    $scoreClass = $score >= 90 ? 'excellent' : ($score >= 70 ? 'good' : ($score >= 50 ? 'average' : 'poor'));
                                    $status = $score >= 70 ? 'Passed' : 'Failed';
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($result['assessment_id']); ?></td>
                                        <td>
                                            <span class="score-badge score-<?php echo $scoreClass; ?>">
                                                <?php echo number_format($score, 1); ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <strong style="color: <?php echo $score >= 70 ? '#28a745' : '#dc3545'; ?>">
                                                <?php echo $status; ?>
                                            </strong>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($result['completed_at'])); ?></td>
                                        <td>
                                            <a href="view-result.php?id=<?php echo $result['id']; ?>" class="btn btn-secondary" style="padding: 5px 15px; font-size: 0.9em;">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-content">
                        <div class="no-content-icon">📊</div>
                        <h3>No Results Yet</h3>
                        <p>You haven't completed any assessments yet. Start one from the Available Assessments tab!</p>
                        <button onclick="switchTab('available')" class="btn btn-primary" style="margin-top: 20px;">
                            View Available Assessments
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab 3: All Results (Admin/OrgAdmin only) -->
            <?php if (isset($currentUser) && ($currentUser->getRoleId() == 1 || $currentUser->getRoleId() == 2)): ?>
            <div id="all-results" class="tab-content">
                <h2 class="section-title">📈 All Assessment Results</h2>

                <?php
                // Get all results for admin view
                $allResults = $assessmentController->viewResults($currentUser) ?? [];
                ?>

                <?php if ($currentUser->getRoleId() == 2): ?>
                <div class="filter-section">
                    <form method="GET" action="" class="filter-group">
                        <label for="employee-filter">Filter by Employee:</label>
                        <select name="employee_id" id="employee-filter" onchange="this.form.submit()">
                            <option value="">All Employees</option>
                            <!-- You would populate this with actual employees from your organisation -->
                            <?php 
                            // This would require another controller method to get employees
                            // For now, showing the structure
                            ?>
                        </select>
                    </form>
                </div>
                <?php endif; ?>

                <?php if (!empty($allResults)): ?>
                    <div class="results-container">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Result ID</th>
                                    <th>Assessment ID</th>
                                    <th>User ID</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Completed At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allResults as $result): 
                                    $score = $result['score'];
                                    $scoreClass = $score >= 90 ? 'excellent' : ($score >= 70 ? 'good' : ($score >= 50 ? 'average' : 'poor'));
                                    $status = $score >= 70 ? 'Passed' : 'Failed';
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($result['id']); ?></td>
                                        <td><?php echo htmlspecialchars($result['assessment_id']); ?></td>
                                        <td><?php echo htmlspecialchars($result['user_id']); ?></td>
                                        <td>
                                            <span class="score-badge score-<?php echo $scoreClass; ?>">
                                                <?php echo number_format($score, 1); ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <strong style="color: <?php echo $score >= 70 ? '#28a745' : '#dc3545'; ?>">
                                                <?php echo $status; ?>
                                            </strong>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($result['completed_at'])); ?></td>
                                        <td>
                                            <a href="view-result.php?id=<?php echo $result['id']; ?>" class="btn btn-secondary" style="padding: 5px 15px; font-size: 0.9em;">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-content">
                        <div class="no-content-icon">📊</div>
                        <h3>No Results Available</h3>
                        <p>No assessment results have been recorded yet.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Admin Controls -->
        <?php if (isset($currentUser) && $currentUser->getRoleId() == 1): ?>
        <div class="admin-controls">
            <a href="create-assessment.php" class="btn btn-success">➕ Create New Assessment</a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });

            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked tab
            event.target.classList.add('active');
        }
    </script>
</body>
</html>