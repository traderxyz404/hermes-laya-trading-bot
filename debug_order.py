import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"
BASE_URL = "https://cdn-ind.testnet.deltaex.org"

def debug_order_post():
    path = "/v2/orders"
    timestamp = str(int(time.time()))
    payload = {
        "product_id": 27,
        "size": 1,
        "side": "buy",
        "order_type": "market_order",
        "time_in_force": "ioc"
    }
    import json
    body = json.dumps(payload, separators=(',', ':'))
    message = "POST" + timestamp + path + body
    
    signature = hmac.new(
        API_SECRET.encode('utf-8'),
        message.encode('utf-8'),
        hashlib.sha256
    ).hexdigest()

    headers = {
        'api-key': API_KEY,
        'timestamp': timestamp,
        'signature': signature,
        'Content-Type': 'application/json'
    }

    url = BASE_URL + path
    print("Testing direct market order POST to Delta Testnet...")
    res = requests.post(url, headers=headers, data=body, timeout=10)
    print("Status:", res.status_code)
    print("Response:", res.text)

if __name__ == '__main__':
    debug_order_post()
