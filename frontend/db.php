<?php
require __DIR__ . '/../vendor/autoload.php';
try {
    $client = new MongoDB\Client("mongodb+srv://mukharjeetanisha05_db_user:Tanisha123@cluster0.591exvf.mongodb.net/?appName=Cluster0");
    $db = $client->fraudDB;
    $collection = $db->transactions;

   


} catch (Exception $e) {
    
}
?>