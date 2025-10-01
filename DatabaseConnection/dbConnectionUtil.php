<?php
/**
 * Database connection utility using SQLite
 */
class DatabaseUtil {
    private $pdo;

    public function connect() {
        if (!$this->pdo) {
            $this->pdo = new PDO("sqlite:" . __DIR__ . "/../database.sqlite");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->pdo;
    }
}