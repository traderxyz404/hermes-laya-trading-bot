import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"
BASE_URL = "https://api.india.delta.exchange"

def test_original_key_on_india_api():
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
    print("Testing original key on https://api.india.delta.exchange...")
    try:
        res = requests.get(url, headers=headers, timeout=5)
        print(f"Status Code: {res.status_code}")
        print(f"Response Body: {res.text}")
        
        if res.status_code == 200 and res.json().get('success'):
            print("\nSUCCESS! Connected to Delta India API with original key!")
            for w in res.json().get('result', []):
                print(f"Coin: {w.get('asset_symbol')} | Balance: {w.get('balance')} | Available: {w.get('available_balance')}")
    except Exception as e:
        print("Error:", e)

if __name__ == '__main__':
    test_original_key_on_india_api()
