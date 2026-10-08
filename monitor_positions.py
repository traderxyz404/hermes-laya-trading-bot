import os
import time
from alpaca.trading.client import TradingClient

API_KEY = "EoDwOzK74UWMd8CAXPpNBS1zhxwmOs"
API_SECRET = "hk1o5w1TZwjwjBdCEwrrmBSdQh9tEk3bskJcwFXUPeQCoBgaWca8vf6S4Jnw"

def monitor_live_crypto_positions():
    client = TradingClient(API_KEY, API_SECRET, paper=True)
    
    print("==================================================")
    print("  HERMES v5.0 // REAL-TIME CRYPTO POSITION MONITOR")
    print("==================================================")
    print("Polling Alpaca 24/7 crypto markets every 3 seconds...\n")
    
    try:
        while True:
            # Fetch account equity
            account = client.get_account()
            cash = float(account.cash)
            portfolio_value = float(account.portfolio_value)
            
            # Fetch all active crypto/stock positions
            positions = client.get_all_positions()
            
            os.system('cls' if os.name == 'nt' else 'clear')
            print("\033[1;36m" + "="*70)
            print("  HERMES v5.0 // REAL-TIME CRYPTO POSITION MONITOR")
            print("="*70 + "\033[0m")
            print(f"\033[1;32m[ACCOUNT]\033[0m Portfolio Value: \033[1;32m${portfolio_value:,.2f} USD\033[0m | Cash: ${cash:,.2f} USD")
            print(f"\033[1;36m" + "-"*70 + "\033[0m")
            
            if not positions:
                print("\033[1;33m[+] No active positions currently open.\033[0m")
            else:
                print(f"{'SYMBOL':<10} | {'QTY':<10} | {'ENTRY PRICE':<12} | {'MARKET PRICE':<12} | {'UNREALIZED P&L':<15}")
                print("-" * 70)
                for p in positions:
                    symbol = p.symbol
                    qty = float(p.qty)
                    entry = float(p.avg_entry_price)
                    current = float(p.current_price)
                    unrealized_pl = float(p.unrealized_pl)
                    pl_color = "\033[1;32m" if unrealized_pl >= 0 else "\033[1;31m"
                    reset_color = "\033[0m"
                    
                    print(f"{symbol:<10} | {qty:<10.4f} | ${entry:<11,.2f} | ${current:<11,.2f} | {pl_color}${unrealized_pl:>+10,.2f} USD{reset_color}")
            
            print("\n\033[1;30m(Press Ctrl+C to stop monitoring at any time)\033[0m")
            time.sleep(3)
            
    except KeyboardInterrupt:
        print("\n\033[1;31mPosition monitoring stopped by user.\033[0m")

if __name__ == '__main__':
    monitor_live_crypto_positions()
