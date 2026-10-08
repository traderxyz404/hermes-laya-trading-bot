import os
from alpaca.trading.client import TradingClient
from alpaca.trading.requests import MarketOrderRequest
from alpaca.trading.enums import OrderSide, TimeInForce

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def test_alpaca_connection():
    print("Connecting to Alpaca Paper Trading API...")
    try:
        # Initialize TradingClient in paper mode
        client = TradingClient(API_KEY, API_SECRET, paper=True)
        
        # Fetch account details
        account = client.get_account()
        print("\n========================================")
        print("    ALPACA PAPER TRADING CONNECTED!     ")
        print("========================================")
        print(f"Status: {account.status}")
        print(f"Cash Balance: ${float(account.cash):,.2f} USD")
        print(f"Portfolio Value: ${float(account.portfolio_value):,.2f} USD")
        print(f"Buying Power: ${float(account.buying_power):,.2f} USD")
        print("========================================")
        
        # Fetch current positions
        positions = client.get_all_positions()
        print(f"Open Positions: {len(positions)}")
        for p in positions:
            print(f" - {p.symbol}: {p.qty} shares @ ${float(p.avg_entry_price):,.2f}")
            
    except Exception as e:
        print("Alpaca Connection Error:", e)

if __name__ == '__main__':
    test_alpaca_connection()
