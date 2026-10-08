import time
import hmac
import hashlib
import json
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"
BASE_URL = "https://api.india.delta.exchange"

path = "/v2/orders"
method = "POST"
timestamp = str(int(time.time()))
payload = {
    "product_id": 27,
    "size": 1,
    "side": "buy",
    "order_type": "limit_order",
    "limit_price": "67735.8",
    "time_in_force": "post_only"
}
body = json.dumps(payload, separators=(',', ':'))

# Test standard signature format for POST requests
message = method + timestamp + path + body
signature = hmac.new(API_SECRET.encode(), message.encode(), hashlib.sha256).hexdigest()

headers = {
    'api-key': API_KEY,
    'timestamp': timestamp,
    'signature': signature,
    'Content-Type': 'application/json'
}

res = requests.post(BASE_URL + path, headers=headers, data=body)
print("Status:", res.status_code)
print("Body:", res.text)
