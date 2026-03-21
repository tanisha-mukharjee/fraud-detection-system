<?php
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $data = [
        "amount" => $_POST['amount'],
        "hour" => $_POST['hour'],
        "location" => $_POST['location'],
        "previous_amount" => $_POST['previous_amount'],
        "transaction_count" => $_POST['transaction_count'],
        "time_gap" => $_POST['time_gap']
    ];

    $url = "https://fraud-detection-system-jg0m.onrender.com/predict";

    $options = [
        "http" => [
            "header"  => "Content-type: application/json\r\n",
            "method"  => "POST",
            "content" => json_encode($data),
            "timeout" => 5
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if($result === FALSE){
        echo "<h2 style='color:red'>❌ Backend not reachable</h2>";
        exit();
    }

    $response = json_decode($result, true);
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="glass">

    <h2 style="color:<?= $response['status']=='FRAUD'?'red':'lightgreen' ?>">
        <?= $response['status'] ?>
    </h2>

    <p>Probability: <?= $response['probability'] * 100 ?>%</p>
    <p>Score: <?= $response['score'] ?></p>

    <p><b>Risk Level:</b> <?= $response['risk'] ?></p>
    <p><b>Action:</b> <?= $response['action'] ?></p>

    <h4>🤖 AI Reasons:</h4>
    <ul>
        <?php foreach($response['reasons'] as $r){ ?>
            <li><?= $r ?></li>
        <?php } ?>
    </ul>

    <a href="dashboard.php" class="btn">Dashboard</a>

</div>

</body>
</html>