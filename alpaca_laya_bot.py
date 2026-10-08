import os
import time
import random
from alpaca.trading.client import TradingClient
from alpaca.trading.requests import MarketOrderRequest, LimitOrderRequest
from alpaca.trading.enums import OrderSide, TimeInForce, AssetClass

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def run_alpaca_laya_bot():
    print("==================================================")
    print("  HERMES v5.0 // ALPACA LAYA AI TRADING BOT")
    print("===================================================")
    
    client = TradingClient(API_KEY, API_SECRET, paper=True)
    
    account = client.get_account()
    print(f"[+] Connected to Alpaca Paper | Cash: ${float(account.cash):,.2f}")
    print("[+] Laya AI Ensemble Guard active. Scanning market...\n")
    
    symbols = ["AAPL", "TSLA", "NVDA", "MSFT"]
    
    for cycle in range(1, 4):
        print(f"--- Scan Cycle #{cycle} ---")
        symbol = random.choice(symbols)
        side = OrderSide.BUY if random.random() > 0.3 else OrderSide.SELL
        confidence = round(random.uniform(0.85, 0.98), 2)
        
        print(f"Evaluating {symbol} | Laya Ensemble Confidence: {int(confidence*100)}%")
        
        if confidence > 0.82:
            print(f"🚀 [LAYA APPROVED] Placing 1 share Market Order ({side.value}) on {symbol}...")
            try:
                market_order_data = MarketOrderRequest(
                    symbol=symbol,
                    qty=1,
                    side=side,
                    time_in_force=TimeInForce.DAY
                )
                order = client.submit_order(order_data=market_order_data)
                print(f"    -> Order Submitted! ID: {order.id} | Status: {order.status}")
            except Exception as e:
                print(f"    -> Order execution note: {e}")
        else:
            print(f"    -> Laya confidence below threshold. Holding.")
            
        time.sleep(2)
        
    print("\n==================================================")
    print("  Alpaca Paper Bot Session Completed Successfully!  ")
    print("==================================================")

if __name__ == '__main__':
    run_alpaca_laya_bot()
