<?php

namespace Catlebot\src\database;

use PDO;

class DBOperations
{
    private string $dbPath;
    private PDO $db;
    private string $database;

    public function __construct(string $database, string $dbPath)
    {
        $this->database = $database;
        $this->dbPath = $dbPath;
        $this->connect();
    }

    private function connect(): void
    {
        $databaseConnectionString = $this->database . ':' . $this->dbPath;
        $this->db = new PDO($databaseConnectionString);
    }

    public function getLastId()
    {
        return $this->db->query('
            SELECT * FROM last_tweet
            ORDER BY id
            DESC
            LIMIT 1
        ')->fetch(PDO::FETCH_ASSOC);
    }

    public function setLastId(string $lastTweetId)
    {
        $stmt = $this->db->prepare('INSERT INTO last_tweet (id) VALUES (:tweetId);');
        $stmt->execute([
            'tweetId' => $lastTweetId,
        ]);
    }
}