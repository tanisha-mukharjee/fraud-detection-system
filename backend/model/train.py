import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import StandardScaler
from imblearn.over_sampling import SMOTE
import pickle

# ✅ Load dataset
data = pd.read_csv("../../datasets/creditcard.csv")

print("📊 Original dataset shape:", data.shape)

# ✅ (OPTIONAL) Take smaller sample for project demo
data_sample = data.sample(n=10000, random_state=42)

# ✅ Save CSV for demo (IMPORTANT 🔥)
data_sample.to_csv("clean_dataset.csv", index=False)
print("📁 Clean dataset saved as clean_dataset.csv")

# ✅ Split features/target
X = data_sample.drop("Class", axis=1)
y = data_sample["Class"]

# ✅ Handle imbalance
sm = SMOTE()
X_res, y_res = sm.fit_resample(X, y)

# ✅ Scaling
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X_res)

# ✅ Train/Test split
X_train, X_test, y_train, y_test = train_test_split(
    X_scaled, y_res, test_size=0.2, random_state=42
)

# ✅ Train model
model = RandomForestClassifier(n_estimators=100)
model.fit(X_train, y_train)

# ✅ Save model + scaler
pickle.dump(model, open("model/model.pkl", "wb"))
pickle.dump(scaler, open("model/scaler.pkl", "wb"))

print("✅ Model trained successfully")