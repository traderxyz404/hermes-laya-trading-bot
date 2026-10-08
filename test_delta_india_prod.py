import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"
BASE_URL = "https://india.delta.exchange"

def test_india_prod_keys():
    path = "/v2/wallet/balances"
    method = "GET"
    timestamp = str(int(time.time()))
    
    signature = hmac.new(
        API_SECRET.encode('utf-8'),
        (method + timestamp + path).encode('utf-8'),
        hashlib.sha256
    ).hexdigest()

    headers = {
        'api-key': API_KEY,
        'timestamp': timestamp,
        'signature': signature,
        'Content-Type': 'application/json'
    }

    url = BASE_URL + path
    print("Testing Delta India Prod API with new keys at:", url)
    try:
        res = requests.get(url, headers=headers, timeout=5)
        print(f"Status: {res.status_code}")
        print(f"Body: {res.text}")
    except Exception as e:
        print("Error:", e)

if __name__ == '__main__':
    test_india_prod_keys()
