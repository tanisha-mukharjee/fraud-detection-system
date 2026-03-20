<?php
header('Content-Type: application/json');

include 'db.php';

// ✅ ALWAYS initialize variables
$fraud = 0;
$safe = 0;
$labels = [];
$values = [];
$rows = [];

try {

    if (isset($collection)) {

        $cursor = $collection->find([], ['limit' => 20]);

        foreach ($cursor as $doc) {

            $status = $doc['status'] ?? "SAFE";

            if ($status == "FRAUD") $fraud++;
            else $safe++;

            $rows[] = [
                "amount" => $doc['amount'] ?? 0,
                "hour" => $doc['hour'] ?? '-',
                "location" => $doc['location'] ?? '-',
                "status" => $status,
                "score" => $doc['score'] ?? 0
            ];

            $labels[] = $doc['hour'] ?? '0';
            $values[] = $doc['score'] ?? 0;
        }
    }

} catch (Exception $e) {
    // ❌ DO NOT echo here (keeps JSON clean)
}

// ✅ ALWAYS return valid JSON
echo json_encode([
    "fraud" => $fraud,
    "safe" => $safe,
    "labels" => $labels,
    "values" => $values,
    "rows" => $rows
]);

exit;
?>