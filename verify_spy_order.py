import time
from alpaca.trading.client import TradingClient
from alpaca.trading.requests import MarketOrderRequest
from alpaca.trading.enums import OrderSide, TimeInForce

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def verify_and_place_market_order():
    client = TradingClient(API_KEY, API_SECRET, paper=True)
    
    print("Checking open orders and positions before trade...")
    orders = client.get_orders()
    print(f"Open Orders count: {len(orders)}")
    
    print("\nPlacing immediate Market Buy Order for 1 share of SPY...")
    market_order_data = MarketOrderRequest(
        symbol="SPY",
        qty=1,
        side=OrderSide.BUY,
        time_in_force=TimeInForce.DAY
    )
    
    order = client.submit_order(order_data=market_order_data)
    print(f"Submitted Order ID: {order.id}")
    print(f"Initial Status: {order.status}")
    
    # Wait 3 seconds and check positions
    print("\nWaiting 3 seconds for exchange fill settlement...")
    time.sleep(3)
    
    positions = client.get_all_positions()
    print(f"Confirmed Open Positions count: {len(positions)}")
    for p in positions:
        print(f" -> {p.symbol}: {p.qty} shares @ ${float(p.avg_entry_price):,.2f}")

if __name__ == '__main__':
    verify_and_place_market_order()
