import os
import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"
BASE_URL = "https://api.india.delta.exchange"

def test_xTyR():
    path = "/v2/wallet/balances"
    timestamp = str(int(time.time()))
    signature = hmac.new(API_SECRET.encode(), ("GET" + timestamp + path).encode(), hashlib.sha256).hexdigest()
    headers = {'api-key': API_KEY, 'timestamp': timestamp, 'signature': signature, 'Content-Type': 'application/json'}
    res = requests.get(BASE_URL + path, headers=headers)
    print("xTyR Status:", res.status_code, "Body:", res.text)

if __name__ == '__main__':
    test_xTyR()
