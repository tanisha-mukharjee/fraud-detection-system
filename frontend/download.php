<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="transactions.csv"');

$data = file_get_contents("http://127.0.0.1:5000/get-data");
$data = json_decode($data, true);

$output = fopen("php://output", "w");

fputcsv($output, ["Amount", "Hour", "Location", "Status", "Score"]);

foreach ($data["rows"] as $row) {
    fputcsv($output, $row);
}

fclose($output);
?>