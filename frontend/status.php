<?php
require '../vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb+srv://mukharjeetanisha05_db_user:Lsceol8m7FaR7noH@cluster0.591exvf.mongodb.net/?appName=Cluster0");

    $client->listDatabases();

    echo "✅ MongoDB Atlas Connected";

} catch (Exception $e) {
    echo "❌ MongoDB Connection Failed";
}
?>