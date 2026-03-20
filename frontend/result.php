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
    "amount" => (float)$amount,
    "hour" => (int)$hour,
    "location" => (int)$location
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

<h4>🤖 AI Reason:</h4>
<ul>
<?php foreach ($reasons as $reason): ?>
    <li style="color: orange;"><?php echo htmlspecialchars($reason); ?></li>
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