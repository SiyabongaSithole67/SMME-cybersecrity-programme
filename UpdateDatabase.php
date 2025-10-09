<?php
/**
 * Database Update Script
 * Run this ONCE to add new fields to the results table
 */

$dbFile = __DIR__ . "/Config/database.sqlite";
$pdo = new PDO("sqlite:$dbFile");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Updating database schema...\n";

try {
    // Check if columns already exist
    $stmt = $pdo->query("PRAGMA table_info(results)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');
    
    // Add answer_file column if it doesn't exist
    if (!in_array('answer_file', $columnNames)) {
        $pdo->exec("ALTER TABLE results ADD COLUMN answer_file TEXT");
        echo "✓ Added 'answer_file' column to results table\n";
    } else {
        echo "✓ Column 'answer_file' already exists\n";
    }
    
    // Add status column if it doesn't exist
    if (!in_array('status', $columnNames)) {
        $pdo->exec("ALTER TABLE results ADD COLUMN status TEXT DEFAULT 'graded'");
        echo "✓ Added 'status' column to results table\n";
    } else {
        echo "✓ Column 'status' already exists\n";
    }
    
    // Update existing results to have 'graded' status
    $pdo->exec("UPDATE results SET status = 'graded' WHERE status IS NULL");
    echo "✓ Updated existing results with 'graded' status\n";
    
    // Add approved column to users table if it doesn't exist
    $stmt = $pdo->query("PRAGMA table_info(users)");
    $userColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $userColumnNames = array_column($userColumns, 'name');
    
    if (!in_array('approved', $userColumnNames)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN approved INTEGER DEFAULT 1");
        echo "✓ Added 'approved' column to users table\n";
        
        // Update existing users to be approved
        $pdo->exec("UPDATE users SET approved = 1 WHERE approved IS NULL");
        echo "✓ Updated existing users with approved status\n";
    } else {
        echo "✓ Column 'approved' already exists in users table\n";
    }
    
    echo "\n✅ Database update completed successfully!\n";
    echo "\nNew columns added:\n";
    echo "  - results.answer_file (TEXT) - stores uploaded file name\n";
    echo "  - results.status (TEXT) - 'pending' or 'graded'\n";
    echo "  - users.approved (INTEGER) - 0=pending, 1=approved\n";
    
} catch (Exception $e) {
    echo " Error: " . $e->getMessage() . "\n";
}