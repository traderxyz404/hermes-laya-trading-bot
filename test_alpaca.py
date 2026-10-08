import os
from alpaca.trading.client import TradingClient
from alpaca.trading.requests import MarketOrderRequest
from alpaca.trading.enums import OrderSide, TimeInForce

def test_alpaca_paper():
    # Example paper keys or environment check
    api_key = os.getenv("APCA_API_KEY_ID", "PK_DEMO_KEY")
    api_secret = os.getenv("APCA_API_SECRET_KEY", "SK_DEMO_SECRET")
    
    print("Alpaca-py SDK loaded successfully.")
    print("To connect to Alpaca Paper Trading, set your APCA_API_KEY_ID and APCA_API_SECRET_KEY environment variables.")
    print("Website: https://app.alpaca.markets/signup (Free Paper Trading API Keys)")

if __name__ == '__main__':
    test_alpaca_paper()
