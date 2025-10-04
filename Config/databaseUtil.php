<?php
/**
 * Database connection utility using SQLite
 */
class DatabaseUtil {
    private $pdo;

    public function connect() {
        if (!$this->pdo) {
            
          // Create a new PDO instance to connect to the SQLite database file
          // __DIR__ gives the directory of the current file, so we navigate to the database file
         $this->pdo = new PDO("sqlite:" . __DIR__ . "/../database.sqlite");

         // Set the error mode to throw exceptions if any database errors occur
        // This makes it easier to debug instead of silently failing
         $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         
        }
        return $this->pdo;
    }
}