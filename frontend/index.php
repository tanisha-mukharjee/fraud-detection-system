<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style.css">
</head>
<div style="text-align:right; padding:10px;">
    <a href="dashboard.php" style="color:white; margin-right:15px;">📊 Dashboard</a>
    <a href="logout.php" style="color:red;">Logout</a>
</div>

<body>
<div class="container">
<div class="glass">

    <h2>💳 Fraud Detection</h2>
    <p>By Tanisha Mukharjee</p>

    <!-- ✅ CORRECT FORM -->
    <form action="result.php" method="POST">

        <input type="number" name="amount" placeholder="Amount" required>

        <input type="number" name="hour" placeholder="Hour (0-23)" required>

        <div class="checkbox-group">
            <input type="checkbox" name="location" value="1" id="loc">
            <label for="loc">Location Changed</label>
        </div>

        <button type="submit">Check</button>

    </form>
<script>
document.querySelector("form").addEventListener("submit", function(){
    this.querySelector("button").innerText = "Processing...";
});
</script>
</div>




</div>
</div>
</body>
</html>