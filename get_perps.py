import requests

BASE_URL = "https://cdn-ind.testnet.deltaex.org"
res = requests.get(BASE_URL + "/v2/products")
if res.status_code == 200:
    for p in res.json().get('result', []):
        if p.get('contract_type') == 'perpetual_futures':
            print(f"Symbol: {p.get('symbol')} | ID: {p.get('id')} | State: {p.get('state')}")
