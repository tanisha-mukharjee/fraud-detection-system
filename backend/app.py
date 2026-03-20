from flask import Flask, request, jsonify
from pymongo import MongoClient
import pickle
import numpy as np
import os

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
    print("✅ Model Loaded")
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

        # ✅ Get input safely
        amount = float(data.get("amount", 0))
        hour = float(data.get("hour", 0))
        location = float(data.get("location", 0))

        # ==============================
        # 🔥 CREATE 30 FEATURES
        # ==============================
        features = np.zeros(30)

        features[0] = amount
        features[1] = hour
        features[2] = location

        # Scale input
        if scaler:
            features_scaled = scaler.transform([features])
        else:
            features_scaled = [features]

        # ==============================
        # 🤖 MODEL PREDICTION
        # ==============================
        if model:
            prediction = model.predict(features_scaled)[0]
            probability = model.predict_proba(features_scaled)[0][1]
        else:
            prediction = 1 if amount > 5000 else 0
            probability = 0.7 if prediction == 1 else 0.2

        # ==============================
        # 🎯 RESULT
        # ==============================
        status = "FRAUD" if prediction == 1 else "SAFE"
        score = int(probability * 100)

        # ==============================
        # 💡 REASONS
        # ==============================
        reasons = []

        if amount > 5000:
            reasons.append("High transaction amount")

        if hour < 6 or hour > 22:
            reasons.append("Unusual transaction time")

        if location == 1:
            reasons.append("Suspicious location")

        if not reasons:
            reasons.append("Normal behavior")

        # ==============================
        # 💾 SAVE TO MONGODB
        # ==============================
        if collection is not None:
            collection.insert_one({
                "amount": amount,
                "hour": hour,
                "location": location,
                "status": status,
                "score": score
            })

        # ==============================
        # 📤 RESPONSE
        # ==============================
        return jsonify({
            "status": status,
            "probability": score,
            "score": score,
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


# ==============================
# 🚀 RUN SERVER
# ==============================
if __name__ == "__main__":
    print("🚀 Starting Flask...")
    app.run(debug=True)