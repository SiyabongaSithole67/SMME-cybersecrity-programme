<?php
// Path to SQLite file
$dbFile = __DIR__ . "/database.sqlite";

// Create or open the SQLite database
$pdo = new PDO("sqlite:$dbFile");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Creating database and tables...\n";

// --- Roles table ---
$pdo->exec("
CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY,
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

echo "Database and tables created successfully!\n";
echo "Default SystemAdmin: admin@system.com / admin123\n";
echo "Sample OrgAdmins and Employees added.\n";

