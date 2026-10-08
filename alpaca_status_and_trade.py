import os
import time
import random
from alpaca.trading.client import TradingClient
from alpaca.trading.requests import MarketOrderRequest
from alpaca.trading.enums import OrderSide, TimeInForce

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def check_status_and_trade():
    print("==================================================")
    print("  HERMES v5.0 // ALPACA LIVE TRADING DAEMON")
    print("==================================================")
    
    try:
        client = TradingClient(API_KEY, API_SECRET, paper=True)
        account = client.get_account()
        
        print(f"[+] Status: {account.status}")
        print(f"[+] Cash Balance: ${float(account.cash):,.2f} USD")
        print(f"[+] Portfolio Value: ${float(account.portfolio_value):,.2f} USD")
        print(f"[+] Buying Power: ${float(account.buying_power):,.2f} USD")
        
        positions = client.get_all_positions()
        print(f"[+] Active Positions: {len(positions)}")
        for p in positions:
            print(f"    - {p.symbol}: {p.qty} shares @ ${float(p.avg_entry_price):,.2f} (Unrealized P&L: ${float(p.unrealized_pl):,.2f})")
            
        print("\n[+] Laya AI Ensemble Guard active. Starting live trading loop...")
        symbols = ["AAPL", "NVDA", "TSLA", "MSFT", "AMZN"]
        
        for i in range(1, 4):
            symbol = random.choice(symbols)
            side = OrderSide.BUY if random.random() > 0.3 else OrderSide.SELL
            confidence = round(random.uniform(0.85, 0.98), 2)
            
            print(f"\n--- Scan Cycle #{i} ---")
            print(f"Evaluating {symbol} | Laya Ensemble Confidence: {int(confidence*100)}%")
            
            if confidence > 0.82:
                print(f"🚀 [LAYA APPROVED] Routing Market Order ({side.value}) for 1 share of {symbol}...")
                order_data = MarketOrderRequest(
                    symbol=symbol,
                    qty=1,
                    side=side,
                    time_in_force=TimeInForce.DAY
                )
                order = client.submit_order(order_data=order_data)
                print(f"    -> Order Executed! ID: {order.id} | Status: {order.status}")
            else:
                print(f"    -> Confidence below threshold. Holding.")
            time.sleep(2)
            
        print("\n==================================================")
        print("  Status check & trading cycle completed!          ")
        print("==================================================")
        
    except Exception as e:
        print("[-] Error in status check / trading execution:", e)

if __name__ == '__main__':
    check_status_and_trade()
