import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def test_india_endpoint():
    base_url = "https://india.delta.exchange"
    path = "/v2/wallet/balances"
    timestamp = str(int(time.time()))
    
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
    print("Testing on https://india.delta.exchange...")
    try:
        res = requests.get(url, headers=headers, timeout=5)
        print(f"Status Code: {res.status_code}")
        print(f"Response Body: {res.text[:300]}")
    except Exception as e:
        print("Error:", e)

if __name__ == '__main__':
    test_india_endpoint()
