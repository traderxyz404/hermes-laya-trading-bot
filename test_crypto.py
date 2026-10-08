import time
from alpaca.trading.client import TradingClient
from alpaca.trading.requests import MarketOrderRequest
from alpaca.trading.enums import OrderSide, TimeInForce

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def test_crypto_order():
    client = TradingClient(API_KEY, API_SECRET, paper=True)
    print("Testing Crypto Paper Order on Alpaca (BTCUSD or ETHUSD)...")
    try:
        # Alpaca paper crypto pairs use format like BTCUSD or ETHUSD
        order_data = MarketOrderRequest(
            symbol="BTCUSD",
            notional=100.0, # Buy $100 worth of Bitcoin
            side=OrderSide.BUY,
            time_in_force=TimeInForce.IOC
        )
        order = client.submit_order(order_data=order_data)
        print(f"Crypto Order Submitted Successfully! ID: {order.id} | Status: {order.status}")
    except Exception as e:
        print("Crypto Order Note:", e)

if __name__ == '__main__':
    test_crypto_order()
