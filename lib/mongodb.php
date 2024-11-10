<?php
// lib/mongodb.php

class MongoDBConnection {
    private static $instance = null;
    private $connection;
    private $database;

    private function __construct() {
        try {
            $this->connection = new MongoDB\Driver\Manager("mongodb://127.0.0.1:27017");
            $this->database = "realestate_db";
        } catch (Exception $e) {
            error_log("MongoDB Connection Error: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function findOne($collection, $filter) {
        try {
            $query = new MongoDB\Driver\Query($filter, ['limit' => 1]);
            $cursor = $this->connection->executeQuery("$this->database.$collection", $query);
            $result = current($cursor->toArray());
            
            if ($result) {
                // Convert MongoDB object to array
                return json_decode(json_encode($result), true);
            }
            return null;
        } catch (Exception $e) {
            error_log("MongoDB Query Error in findOne: " . $e->getMessage());
            return null;
        }
    }

    public function find($collection, $filter = [], $options = []) {
        try {
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->connection->executeQuery("$this->database.$collection", $query);
            return $cursor;
        } catch (Exception $e) {
            error_log("MongoDB Query Error in find: " . $e->getMessage());
            return null;
        }
    }
}
?>