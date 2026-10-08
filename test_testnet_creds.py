import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"
BASE_URL = "https://cdn-ind.testnet.deltaex.org"

def test_testnet_credentials():
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

    url = BASE_URL + path
    print(f"Testing on Delta Testnet URL: {url}")
    try:
        res = requests.get(url, headers=headers, timeout=5)
        print(f"Status: {res.status_code}")
        print(f"Body: {res.text}")
    except Exception as e:
        print("Error connecting to testnet:", e)

if __name__ == '__main__':
    test_testnet_credentials()
