import requests

BASE_URL = "https://cdn-ind.testnet.deltaex.org"
res = requests.get(BASE_URL + "/v2/products")
if res.status_code == 200:
    for p in res.json().get('result', []):
        if 'BTC' in p.get('symbol', ''):
            print(f"Symbol: {p.get('symbol')} | ID: {p.get('id')} | Type: {p.get('contract_type')} | State: {p.get('state')} | Spot: {p.get('spot_symbol')}")
