<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<div id="db-status" style="text-align:center; margin:10px;"></div>

<body>
    <div class="nav">
    
    <div class="nav-left">
        👋 Welcome, <?php echo $_SESSION['user']; ?>
    </div>

    <div class="nav-center">
        📊 Live Fraud Dashboard
    </div>

    <div class="nav-right">
        <a href="index.php" class="btn">💳 Check Fraud</a>
        <a href="download.php" class="btn download">📥 Download</a>
        <a href="logout.php" class="btn logout">Logout</a>
        
    </div>

</div>


<div class="container">

<div class="glass">
    <h3>Fraud vs Safe</h3>
    <canvas id="pieChart"></canvas>
</div>

<div class="glass">
    <h3>Transaction Trend</h3>
    <canvas id="lineChart"></canvas>
</div>

</div>

<div class="stats">

    <div class="card">
        <h3>Total Transactions</h3>
        <p id="total">0</p>
    </div>

    <div class="card">
        <h3>Fraud</h3>
        <p id="fraudCount">0</p>
    </div>

    <div class="card">
        <h3>Safe</h3>
        <p id="safeCount">0</p>
    </div>

</div>

<script>
let pieChart, lineChart;
let lastFraudCount = 0;

// ✅ Initialize charts
function initCharts(){
    pieChart = new Chart(document.getElementById("pieChart"), {
        type: "doughnut",
        data: {
            labels: ["Fraud", "Safe"],
            datasets: [{ data: [0,0] }]
        }
    });

    lineChart = new Chart(document.getElementById("lineChart"), {
        type: "line",
        data: {
            labels: [],
            datasets: [{
                label: "Transaction Score",
                data: [],
                fill: false
            }]
        }
    });
}

// ✅ Fetch data from Flask
function fetchData() {
    fetch("http://127.0.0.1:5000/get-data")
    .then(res => res.json())
    .then(data => {

        // 🚨 Fraud alert
        if (data.fraud > lastFraudCount) {
            alert("🚨 New Fraud Detected!");
        }
        lastFraudCount = data.fraud;

        // Cards
        document.getElementById("fraudCount").innerText = data.fraud;
        document.getElementById("safeCount").innerText = data.safe;
        document.getElementById("total").innerText = data.fraud + data.safe;

        // Table
        let table = document.getElementById("tableData");
        table.innerHTML = "";

        if(data.rows){
            data.rows.forEach(row => {
                table.innerHTML += `
                    <tr>
                        <td>${row.amount}</td>
                        <td>${row.hour ?? '-'}</td>
                        <td>${row.location ?? '-'}</td>
                        <td style="color:${row.status=='FRAUD'?'red':'lightgreen'}">
                            ${row.status}
                        </td>
                        <td>${row.score ?? '-'}</td>
                    </tr>
                `;
            });
        }

        // Charts
        pieChart.data.datasets[0].data = [data.fraud, data.safe];
        pieChart.update();

        lineChart.data.labels = data.labels;
        lineChart.data.datasets[0].data = data.values;
        lineChart.update();

    })
    .catch(err => console.log("Error:", err));
}

// ✅ Correct execution order
initCharts();
fetchData();
setInterval(fetchData, 3000);
</script></div> <!-- container end -->

<!-- ✅ ADD TABLE HERE -->
<div class="table-container">
    <h2>📋 Transaction History</h2>

    <table>
        <thead>
            <tr>
                <th>Amount</th>
                <th>Hour</th>
                <th>Location</th>
                <th>Status</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody id="tableData"></tbody>
    </table>
</div>

<script>
function fetchData() {
    fetch("get_data.php")
    .then(res => res.json())
    .then(data => {

        // Update cards
        document.getElementById("fraudCount").innerText = data.fraud;
        document.getElementById("safeCount").innerText = data.safe;
        document.getElementById("total").innerText = data.fraud + data.safe;

        // Update table
        let table = document.getElementById("tableData");
        table.innerHTML = "";

        data.rows.forEach(row => {
            table.innerHTML += `
                <tr>
                    <td>${row.amount}</td>
                    <td>${row.hour}</td>
                    <td>${row.location}</td>
                    <td style="color:${row.status=='FRAUD'?'red':'lightgreen'}">
                        ${row.status}
                    </td>
                    <td>${row.score}</td>
                </tr>
            `;
        });

        // Update charts (already present)
        pieChart.data.datasets[0].data = [data.fraud, data.safe];
        pieChart.update();

        lineChart.data.labels = data.labels;
        lineChart.data.datasets[0].data = data.values;
        lineChart.update();

    });
}

setInterval(fetchData, 3000);
fetchData();

initCharts();
setInterval(fetchData, 3000);
</script>


</body>
</html>