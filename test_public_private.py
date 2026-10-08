import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

# Let's test public endpoint vs private endpoint to verify if connection works at all
def test_public_vs_private():
    # Public endpoint (no auth needed)
    pub_res = requests.get("https://api.delta.exchange/v2/products")
    print("Public Endpoint Status (Products):", pub_res.status_code, "Success:", pub_res.json().get('success'))
    
    # Private endpoint with different signature methods
    path = "/v2/wallet/balances"
    timestamp = str(int(time.time()))
    
    # Method + Timestamp + Path + Query + Body
    msg = "GET" + timestamp + path
    sig = hmac.new(API_SECRET.encode(), msg.encode(), hashlib.sha256).hexdigest()
    
    headers = {
        'api-key': API_KEY,
        'timestamp': timestamp,
        'signature': sig,
        'Content-Type': 'application/json'
    }
    priv_res = requests.get("https://api.delta.exchange" + path, headers=headers)
    print("Private Endpoint Status:", priv_res.status_code, "Body:", priv_res.text)

if __name__ == '__main__':
    test_public_vs_private()
