def calculate_risk(amount, hour, location_change):
    risk = 0

    if amount > 5000:
        risk += 30

    if hour < 6 or hour > 23:
        risk += 20

    if location_change:
        risk += 40

    return risk