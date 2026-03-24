from flask import Flask, request, jsonify
from pymongo import MongoClient
import pickle
import numpy as np
from datetime import datetime
import os
from flask_cors import CORS

app = Flask(__name__)

# ==============================
# ✅ LOAD MODEL
# ==============================
try:
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))

    model_path = os.path.join(BASE_DIR, "model", "model", "model.pkl")
    scaler_path = os.path.join(BASE_DIR, "model", "model", "scaler.pkl")

    model = pickle.load(open(model_path, "rb"))
    scaler = pickle.load(open(scaler_path, "rb"))

    print("✅ Model Loaded Successfully")

except Exception as e:
    print("❌ Model Load Error:", e)
    model = None
    scaler = None

# ==============================
# ✅ MONGODB CONNECTION
# ==============================
try:
    client = MongoClient(
        "mongodb+srv://mukharjeetanisha05_db_user:Tanisha123@cluster0.591exvf.mongodb.net/?retryWrites=true&w=majority",
        serverSelectionTimeoutMS=5000
    )
    db = client["fraudDB"]
    collection = db["transactions"]
    client.server_info()
    print("✅ MongoDB Connected")
except Exception as e:
    print("❌ MongoDB Error:", e)
    collection = None

# ==============================
# 🧠 PREDICT ROUTE
# ==============================

@app.route("/predict", methods=["POST"])
def predict():
    try:
        data = request.get_json()

        amount = float(data.get("amount", 0))
        hour = float(data.get("hour", 0))
        location = float(data.get("location", 0))

        previous_amount = float(data.get("previous_amount", amount))
        transaction_count = float(data.get("transaction_count", 1))
        time_gap = float(data.get("time_gap", 1))

        # ======================
        # 🔥 FRAUD LOGIC
        # ======================
        score = 0

        if amount > previous_amount * 2:
            score += 30

        if transaction_count > 5:
            score += 20

        if hour < 6 or hour > 22:
            score += 15

        if location == 1:
            score += 20

        if time_gap < 2:
            score += 15

        if score > 100:
            score = 100

        probability = score / 100
        prediction = 1 if score > 50 else 0

        status = "FRAUD" if prediction == 1 else "SAFE"

        # ======================
        # RISK
        # ======================
        if score > 75:
            risk = "HIGH RISK"
        elif score > 40:
            risk = "MEDIUM RISK"
        else:
            risk = "LOW RISK"

        # ======================
        # REASONS
        # ======================
        reasons = []

        if amount > previous_amount * 2:
            reasons.append("Unusual spike in transaction amount")

        if transaction_count > 5:
            reasons.append("High transaction frequency")

        if hour < 6 or hour > 22:
            reasons.append("Transaction at abnormal time")

        if location == 1:
            reasons.append("Unfamiliar location")

        if time_gap < 2:
            reasons.append("Rapid transactions")

        if not reasons:
            reasons.append("Normal behavior")

        # ======================
        # ACTION
        # ======================
        if score > 80:
            action = "BLOCKED"
        elif score > 50:
            action = "FLAGGED"
        else:
            action = "ALLOWED"

        # ======================
        # SAVE DB
        # ======================
        if collection is not None:
            collection.insert_one({
                "amount": amount,
                "hour": hour,
                "location": location,
                "status": status,
                "score": score,
                "risk": risk,
                "action": action,
                "time": datetime.now()
            })

        return jsonify({
            "status": status,
            "probability": probability,
            "score": score,
            "risk": risk,
            "action": action,
            "reasons": reasons
        })

    except Exception as e:
        print("🔥 ERROR:", e)
        return jsonify({"error": str(e)}), 500
# ==============================
# 📊 GET DATA FOR DASHBOARD
# ==============================
@app.route("/get-data")
def get_data():
    try:
        fraud = 0
        safe = 0
        rows = []
        labels = []
        values = []

        if collection is not None:
            data = collection.find().limit(50)

            for doc in data:
                amount = doc.get("amount", 0)
                hour = doc.get("hour", 0)
                location = doc.get("location", 0)
                status = doc.get("status", "SAFE")
                score = doc.get("score", 0)

                if status == "FRAUD":
                    fraud += 1
                else:
                    safe += 1

                rows.append({
                    "amount": amount,
                    "hour": hour,
                    "location": location,
                    "status": status,
                    "score": score
                })

                labels.append(str(hour))
                values.append(score)

        return jsonify({
            "fraud": fraud,
            "safe": safe,
            "labels": labels,
            "values": values,
            "rows": rows
        })

    except Exception as e:
        return jsonify({"error": str(e)})

@app.route('/')
def home():
    return "Fraud Detection API is Running 🚀"
# ==============================
# 🚀 RUN SERVER
# ==============================
if __name__ == "__main__":
    import os

    port = int(os.environ.get("PORT", 10000))

    print(f"🚀 Running on port {port}")

    app.run(host="0.0.0.0", port=port, debug=False)