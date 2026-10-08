import time
import hmac
import hashlib
import requests

# Let's test checking balances across possible environment endpoints
API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def check_demo_balances():
    endpoints = [
        "https://api.delta.exchange",
        "https://testnet-api.delta.exchange",
        "https://api.india.delta.exchange"
    ]
    
    path = "/v2/wallet/balances"
    timestamp = str(int(time.time()))
    
    for base in endpoints:
        signature = hmac.new(
            API_SECRET.encode('utf-8'),
            ("GET" + timestamp + path).encode('utf-8'),
            hashlib.sha256
        ).hexdigest()

        headers = {
            'api-key': API_KEY,
            'timestamp': timestamp,
            'signature': signature,
            'Content-Type': 'application/json'
        }

        try:
            res = requests.get(base + path, headers=headers, timeout=4)
            print(f"Endpoint: {base} -> Status: {res.status_code} -> Body: {res.text}")
        except Exception as e:
            print(f"Endpoint: {base} -> Error: {e}")

if __name__ == '__main__':
    check_demo_balances()
