import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def test_api_keys():
    endpoints = [
        ("Global Live", "https://api.delta.exchange"),
        ("Global Testnet", "https://testnet-api.delta.exchange"),
        ("India Prod", "https://api.india.delta.exchange"),
    ]
    
    path = "/v2/wallet/balances"
    timestamp = str(int(time.time()))
    
    for name, base_url in endpoints:
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

        url = base_url + path
        try:
            res = requests.get(url, headers=headers, timeout=5)
            print(f"[{name}] URL: {url}")
            print(f"  -> HTTP Status: {res.status_code}")
            print(f"  -> Response: {res.text}")
        except Exception as e:
            print(f"[{name}] Connection Error: {e}")
        print("-" * 60)

if __name__ == '__main__':
    test_api_keys()
