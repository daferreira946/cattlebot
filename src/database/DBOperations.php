<?php

namespace Catlebot\src\database;

use PDO;

class DBOperations
{
    private $dbPath;
    private $db;
    private $database;

    public function __construct($database, $dbPath)
    {
        $this->database = $database;
        $this->dbPath = $dbPath;
        $this->connect();
    }

    private function connect()
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

    public function setLastId($lastTweetId)
    {
        $stmt = $this->db->prepare('INSERT INTO last_tweet (id) VALUES (:tweetId);');
        $stmt->execute([
            'tweetId' => $lastTweetId,
        ]);
    }
}