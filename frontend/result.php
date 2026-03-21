<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

// ✅ Get form data safely
$amount = $_POST['amount'] ?? 0;
$hour = $_POST['hour'] ?? 0;
$location = isset($_POST['location']) ? 1 : 0;

// ✅ Prepare JSON for Flask
$data = json_encode([
    "amount" => $amount,
    "hour" => $hour,
    "location" => $location,

    "previous_amount" => $_POST['previous_amount'] ?? $amount,
    "transaction_count" => $_POST['transaction_count'] ?? 1,
    "time_gap" => $_POST['time_gap'] ?? 1
]);

// ✅ Send request to Flask
$options = [
    "http" => [
        "header"  => "Content-Type: application/json\r\n",
        "method"  => "POST",
        "content" => $data,
        "timeout" => 5
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents("http://127.0.0.1:5000/predict", false, $context);
// ✅ Decode response
$result = $response ? json_decode($response, true) : null;

// ✅ Safe fallback
$status = $result['status'] ?? "ERROR";
$probability = $result['probability'] ?? 0;
$score = $result['score'] ?? 0;
$reasons = $result['reasons'] ?? ["No reason available"];

// ✅ UI class
$class = ($status == "FRAUD") ? "fraud" : "safe";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Result</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">
<div class="glass">

<?php if ($result && isset($result['status'])): ?>

<h2 class="<?php echo $class; ?>">
    <?php echo htmlspecialchars($status); ?>
</h2>

<p>Probability: <?php echo htmlspecialchars($probability); ?>%</p>
<p>Score: <?php echo htmlspecialchars($score); ?></p>

<h2 class="<?php echo $class; ?>"><?php echo $status; ?></h2>

<p>Probability: <?php echo $probability; ?>%</p>
<p>Score: <?php echo $score; ?></p>

<p><strong>Risk Level:</strong> <?php echo $result['risk']; ?></p>
<p><strong>Action:</strong> <?php echo $result['action']; ?></p>

<h4>🤖 AI Reasons:</h4>
<ul>
<?php foreach ($reasons as $reason): ?>
<li style="color: orange;"><?php echo $reason; ?></li>
<?php endforeach; ?>
</ul>

<!-- 🚨 Alert -->
<script>
if("<?php echo $status; ?>" === "FRAUD"){
    alert("🚨 Fraud Detected!");
}
</script>


<?php endif; ?>

<br>
<a href="dashboard.php"><button>Dashboard</button></a>

</div>
</div>
</body>
</html>