<?php
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo "❌ Invalid access";
    exit();
}
?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $amount = $_POST['amount'];

    $data = array(
        "amount" => (float)$amount
    );

    $url = "https://fraud-detection-system-jg0m.onrender.com/predict";

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));

    $response = curl_exec($ch);

    if ($response === false) {
        echo "❌ Backend not reachable";
    } else {
        $result = json_decode($response, true);
        echo "<h2>Prediction: " . $result['prediction'] . "</h2>";
    }

    curl_close($ch);
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

    <p>Probability: <?= round($response['probability'] * 100 ,2)?>%</p>
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