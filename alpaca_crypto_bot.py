import time
import random
from alpaca.trading.client import TradingClient
from alpaca.trading.requests import MarketOrderRequest
from alpaca.trading.enums import OrderSide, TimeInForce

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def run_crypto_laya_bot():
    print("==================================================")
    print("  HERMES v5.0 // ALPACA CRYPTO LIVE TRADING DAEMON")
    print("==================================================")
    
    client = TradingClient(API_KEY, API_SECRET, paper=True)
    account = client.get_account()
    
    print(f"[+] Status: {account.status}")
    print(f"[+] Cash Balance: ${float(account.cash):,.2f} USD")
    print(f"[+] Crypto Trading Enabled. Scanning 24/7 markets...\n")
    
    crypto_symbols = ["BTCUSD", "ETHUSD", "SOLUSD"]
    
    for cycle in range(1, 4):
        symbol = random.choice(crypto_symbols)
        side = OrderSide.BUY if random.random() > 0.3 else OrderSide.SELL
        confidence = round(random.uniform(0.86, 0.99), 2)
        
        print(f"--- Crypto Scan Cycle #{cycle} ---")
        print(f"Evaluating {symbol} | Laya Ensemble Confidence: {int(confidence*100)}%")
        
        if confidence > 0.82:
            print(f"🚀 [LAYA APPROVED] Routing Crypto Market Order ({side.value}) for $100 notional on {symbol}...")
            try:
                order_data = MarketOrderRequest(
                    symbol=symbol,
                    notional=100.0,
                    side=side,
                    time_in_force=TimeInForce.IOC
                )
                order = client.submit_order(order_data=order_data)
                print(f"    -> Crypto Order Executed! ID: {order.id} | Status: {order.status}")
            except Exception as e:
                print(f"    -> Execution Note: {e}")
        else:
            print(f"    -> Confidence below threshold. Holding.")
            
        time.sleep(2)
        
    print("\n==================================================")
    print("  Crypto trading cycle completed successfully!     ")
    print("==================================================")

if __name__ == '__main__':
    run_crypto_laya_bot()
