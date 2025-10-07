<?php
class DatabaseUtil {
    private $pdo;

    public function connect() {
        if (!$this->pdo) {
            // Correct path to your SQLite database
            $this->pdo = new PDO("sqlite:" . __DIR__ . "/../Config/database.sqlite");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->pdo;
    }
}
