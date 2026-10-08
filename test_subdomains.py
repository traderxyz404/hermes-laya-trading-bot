import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def test_subdomains():
    subdomains = ["api.india.delta.exchange", "api-india.delta.exchange", "india-api.delta.exchange"]
    path = "/v2/wallet/balances"
    timestamp = str(int(time.time()))
    
    for sub in subdomains:
        base_url = f"https://{sub}"
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
            res = requests.get(base_url + path, headers=headers, timeout=3)
            print(f"{sub} -> Status: {res.status_code} | Body: {res.text[:100]}")
        except Exception as e:
            print(f"{sub} -> Error: {e}")

if __name__ == '__main__':
    test_subdomains()
