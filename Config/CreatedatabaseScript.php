<?php
//STAND ALONE PROGRAM TO CREATE SQLITE DATABASE!!
// Path to SQLite file
$dbFile = __DIR__ . "/Config/database.sqlite"; //directory!! of our db

// Create or open the SQLite database
$pdo = new PDO("sqlite:$dbFile"); //instantiation
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Creating database and tables...\n"; //like__system.out.println() in java or print()

// --- Roles table ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY,P
    name TEXT NOT NULL
);
");

// Insert default roles
$pdo->exec("
INSERT OR IGNORE INTO roles (id, name) VALUES
(1, 'SystemAdmin'),
(2, 'OrgAdmin'),
(3, 'Employee');
");

// --- Organisations table ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS organisations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    approved INTEGER DEFAULT 0, -- 0 = pending, 1 = approved
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
");

// Insert sample organisations
$pdo->exec("
INSERT OR IGNORE INTO organisations (id, name, approved) VALUES
(1, 'CyberTech SMME', 1),
(2, 'Secure Solutions', 0);
");

// --- Users table ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role_id INTEGER NOT NULL, -- 1=SystemAdmin, 2=OrgAdmin, 3=Employee
    organisation_id INTEGER,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(role_id) REFERENCES roles(id),
    FOREIGN KEY(organisation_id) REFERENCES organisations(id)
);
");

// Insert default SystemAdmin
$pdo->exec("
INSERT OR IGNORE INTO users (name, email, password, role_id)
VALUES ('Admin', 'admin@system.com', 'admin123', 1);
");

// Insert sample OrgAdmins and Employees
$pdo->exec("
INSERT OR IGNORE INTO users (name, email, password, role_id, organisation_id) VALUES
('Alice OrgAdmin', 'alice@cybertech.com', 'alice123', 2, 1),
('Bob Employee', 'bob@cybertech.com', 'bob123', 3, 1),
('Charlie OrgAdmin', 'charlie@securesolutions.com', 'charlie123', 2, 2),
('Dana Employee', 'dana@securesolutions.com', 'dana123', 3, 2);
");

// --- Content table ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS contents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    link TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
");

// Insert sample content
$pdo->exec("
INSERT OR IGNORE INTO contents (title, link) VALUES
('Cybersecurity Basics', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
('Phishing Awareness', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
");

// --- Assessments table ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS assessments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    content_id INTEGER,
    type TEXT NOT NULL, -- 'formative' or 'summative'
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(content_id) REFERENCES contents(id)
);
");

// Insert sample assessments
$pdo->exec("
INSERT OR IGNORE INTO assessments (title, description, content_id, type) VALUES
('Phishing Quiz', 'Test knowledge on phishing emails', 2, 'formative'),
('Cybersecurity Final Test', 'Summative assessment on all modules', 1, 'summative');
");

// --- Results table ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    assessment_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    score REAL NOT NULL,
    completed_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(assessment_id) REFERENCES assessments(id),
    FOREIGN KEY(user_id) REFERENCES users(id)
);
");

// Insert sample results
$pdo->exec("
INSERT OR IGNORE INTO results (assessment_id, user_id, score) VALUES
(1, 3, 85.5),
(2, 3, 92.0),
(1, 4, 78.0);
");

echo "Database and tables created successfully!\n";
echo "Default SystemAdmin: admin@system.com / admin123\n";
echo "Sample OrgAdmins, Employees, content, assessments, and results added.\n";

