import time
import hmac
import hashlib
import requests

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"
BASE_URL = "https://api.india.delta.exchange"

def check_products():
    res = requests.get(BASE_URL + "/v2/products")
    print("Products status:", res.status_code)
    data = res.json()
    if data.get('success'):
        for p in data.get('result', [])[:5]:
            print(f"Symbol: {p.get('symbol')} | ID: {p.get('id')} | Contract Type: {p.get('contract_type')} | Min Size: {p.get('contract_size')}")

if __name__ == '__main__':
    check_products()
