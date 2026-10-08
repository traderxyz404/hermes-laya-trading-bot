import requests

BASE_URL = "https://api.india.delta.exchange"
res = requests.get(BASE_URL + "/v2/products")
data = res.json()
if data.get('success'):
    print("Total products found:", len(data['result']))
    for p in data.get('result', []):
        if 'BTC' in p.get('symbol', ''):
            print(f"Symbol: {p.get('symbol')} | ID: {p.get('id')} | Contract Type: {p.get('contract_type')}")
