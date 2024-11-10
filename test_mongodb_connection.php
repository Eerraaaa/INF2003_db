<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create a MongoDB Manager instance
$manager = new MongoDB\Driver\Manager("mongodb://127.0.0.1:27017");

try {
    // Check connection success
    echo "Connected successfully to MongoDB.\n";

    // Create a query to fetch data
    $query = new MongoDB\Driver\Query([]);

    // Execute the query on the specified database and collection
    $cursor = $manager->executeQuery('realestate_db.agent', $query);

    // Check if there is data to display
    $hasData = false;
    foreach ($cursor as $document) {
        $hasData = true;
        echo "Connection successful. Retrieved document:\n";
        echo json_encode($document, JSON_PRETTY_PRINT);

    }

    if (!$hasData) {
        echo "No documents found in the collection.\n";
    }

} catch (MongoDB\Driver\Exception\Exception $e) {
    echo "Failed to connect to MongoDB: ", $e->getMessage();
}
?>
