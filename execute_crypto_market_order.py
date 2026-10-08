import time
from alpaca.trading.client import TradingClient
from alpaca.trading.requests import MarketOrderRequest
from alpaca.trading.enums import OrderSide, TimeInForce

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def execute_crypto_market_order():
    client = TradingClient(API_KEY, API_SECRET, paper=True)
    
    print("==================================================")
    print("  HERMES v5.0 // ALPACA IMMEDIATE CRYPTO EXECUTION")
    print("==================================================")
    
    account = client.get_account()
    print(f"[+] Account Cash: ${float(account.cash):,.2f} USD")
    
    # Let's buy $200 worth of BTCUSD instantly
    symbol = "BTCUSD"
    print(f"\n[*] Submitting Market Buy Order for $200 of {symbol}...")
    
    order_data = MarketOrderRequest(
        symbol=symbol,
        notional=200.0,
        side=OrderSide.BUY,
        time_in_force=TimeInForce.IOC
    )
    
    order = client.submit_order(order_data=order_data)
    print(f"[+] Order Submitted! ID: {order.id}")
    print(f"[+] Status: {order.status}")
    
    print("\nWaiting 2 seconds for fill confirmation...")
    time.sleep(2)
    
    positions = client.get_all_positions()
    print(f"\n[+] Verified Open Positions on Alpaca: {len(positions)}")
    for p in positions:
        print(f"    - {p.symbol}: {p.qty} shares @ ${float(p.avg_entry_price):,.2f} (Value: ${float(p.market_value):,.2f})")
    print("==================================================")

if __name__ == '__main__':
    execute_crypto_market_order()
