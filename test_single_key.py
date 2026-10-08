import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
# We'll test with a blank secret or check if you have a secret for this key
API_SECRET = ""

def test_api_key_only():
    base_url = "https://api.delta.exchange"
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
    print("Testing new Delta API Key:", API_KEY)
    try:
        res = requests.get(url, headers=headers, timeout=5)
        print(f"Status: {res.status_code}")
        print(f"Body: {res.text}")
    except Exception as e:
        print("Error:", e)

if __name__ == '__main__':
    test_api_key_only()
