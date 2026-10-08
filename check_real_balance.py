import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"
BASE_URL = "https://api.delta.exchange"

def check_live_balance():
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
    print("Connecting to Delta Exchange Live API...")
    try:
        res = requests.get(url, headers=headers, timeout=10)
        print(f"Response Status: {res.status_code}")
        data = res.json()
        print("Response Body:", data)
        
        if res.status_code == 200 and data.get('success'):
            print("\n========================================")
            print("       REAL DELTA ACCOUNT BALANCES      ")
            print("========================================")
            for wallet in data.get('result', []):
                coin = wallet.get('asset_symbol', 'USDT')
                balance = wallet.get('balance', 0)
                available = wallet.get('available_balance', 0)
                print(f"Asset: {coin:<6} | Total: {balance:<10} | Available: {available}")
            print("========================================")
        else:
            print("Failed to fetch balances. Error message:", data)
            
    except Exception as e:
        print("Connection Exception:", e)

if __name__ == '__main__':
    check_live_balance()
