import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def test_all_signature_orders():
    base_url = "https://api.delta.exchange"
    path = "/v2/wallet/balances"
    timestamp = str(int(time.time()))
    
    methods = [
        # method + timestamp + path
        hmac.new(API_SECRET.encode(), ("GET" + timestamp + path).encode(), hashlib.sha256).hexdigest(),
        # method + path + timestamp
        hmac.new(API_SECRET.encode(), ("GET" + path + timestamp).encode(), hashlib.sha256).hexdigest(),
        # timestamp + method + path
        hmac.new(API_SECRET.encode(), (timestamp + "GET" + path).encode(), hashlib.sha256).hexdigest(),
        # path + timestamp + method
        hmac.new(API_SECRET.encode(), (path + timestamp + "GET").encode(), hashlib.sha256).hexdigest(),
    ]

    for i, sig in enumerate(methods, 1):
        headers = {
            'api-key': API_KEY,
            'timestamp': timestamp,
            'signature': sig,
            'Content-Type': 'application/json'
        }
        res = requests.get(base_url + path, headers=headers)
        print(f"Signature Format {i} -> Status: {res.status_code} -> Body: {res.text}")

if __name__ == '__main__':
    test_all_signature_orders()
