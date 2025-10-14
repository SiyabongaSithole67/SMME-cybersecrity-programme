<?php

$dbFile = __DIR__ . "/database.sqlite"; //directory!! of our db

// Create or open the SQLite database
$pdo = new PDO("sqlite:$dbFile"); //instantiation

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* // Add a new column (INTEGER used for boolean values)
$sql = "ALTER TABLE users ADD COLUMN approved INTEGER DEFAULT 0";
$pdo->exec($sql); */

#only system admin is approved by default
$pdo->exec("UPDATE users SET approved = 1 WHERE role_id = 1");