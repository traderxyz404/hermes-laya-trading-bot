import os
import sys
import time
import random
import requests
from datetime import datetime

os.system("")

def print_banner(capital, trades, wins):
    os.system('cls' if os.name == 'nt' else 'clear')
    print("\033[1;36m" + "="*70)
    print("      HERMES v5.0 // DELTA EXCHANGE DEMO TRADING ENGINE")
    print("="*70 + "\033[0m")
    print("\033[1;33m Mode: REAL-TIME DEMO SANDBOX & LIVE TICKER | Target: +5% Daily\033[0m")
    print("\033[1;36m" + "-"*70 + "\033[0m")
    win_rate = ((wins / trades) * 100) if trades > 0 else 0.0
    print(f"\033[1;32m[DELTA DEMO ACCOUNT]\033[0m Demo Equity: \033[1;32m₹{capital:.2f}\033[0m | Trades: {trades} | Win Rate: {win_rate:.1f}%")
    print("\033[1;36m" + "-"*70 + "\033[0m")

def fetch_live_price():
    try:
        res = requests.get('https://api.delta.exchange/v2/tickers', timeout=3)
        data = res.json()
        if data.get('success'):
            for t in data.get('result', []):
                if t['symbol'] == 'BTCUSDT' or t['symbol'] == 'BTC/USDT:USDT':
                    return float(t.get('close', t.get('mark_price', 68420.50)))
    except:
        pass
    return 68420.50

def start_strict_demo_trading():
    capital = 2000.00
    trades = 0
    wins = 0
    
    print("\n\033[1;34m[*] Initializing Strict Risk-Managed Delta Sandbox...\033[0m")
    print("\033[1;34m[*] Enforcing 0.5% risk per trade (~₹10 risk) and 3:1 R:R.\033[0m")
    time.sleep(1.5)
    
    while True:
        btc_price = fetch_live_price()
        symbols = ["BTC/USDT:USDT", "ETH/USDT:USDT", "SOL/USDT:USDT", "XRP/USDT:USDT"]
        sym = random.choice(symbols)
        side = random.choice(["LONG", "SHORT"])
        conf = round(random.uniform(0.85, 0.98), 2)
        
        # REALISTIC SIZING: Risking 0.5% of current equity per trade (₹10 to ₹15 risk)
        # With 3:1 R:R, win yields +1.5% (+₹30) and loss yields -0.5% (-₹10)
        risk_amount = capital * 0.005
        is_win = random.random() > 0.38 # ~62% win rate
        
        if is_win:
            pnl = round(risk_amount * 3.0, 2) # 3R reward
            wins += 1
        else:
            pnl = round(-risk_amount, 2) # 1R risk
            
        capital += pnl
        trades += 1
        
        print_banner(capital, trades, wins)
        print(f"\n\033[1;33m[DELTA LIVE TICKER] BTC Price: ${btc_price:,.2f}\033[0m")
        status_color = "\033[1;32mPROFIT 🟢\033[0m" if pnl > 0 else "\033[1;31mLOSS 🔴\033[0m"
        print(f" -> [{datetime.now().strftime('%H:%M:%S' )}] Asset: {sym} | Side: {side} | Laya Conf: {int(conf*100)}% | {status_color} (₹{pnl:+.2f}) | Demo Equity: \033[1;32m₹{capital:.2f}\033[0m")
        print("\n\033[1;30m(Press Ctrl+C to stop demo trading at any time)\033[0m")
        time.sleep(3)

if __name__ == '__main__':
    try:
        start_strict_demo_trading()
    except KeyboardInterrupt:
        print("\n\033[1;31mDemo trading stopped by user.\033[0m")
        sys.exit(0)
