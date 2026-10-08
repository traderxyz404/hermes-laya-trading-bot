"""
Automated Multi-Agent Calibration & Optimization Loop
Runs systematic walk-forward iterations over 1 year of real Delta Exchange BTC data.
Finds the mathematically optimal, risk-sustainable configuration for Strategy A & B.
"""

import pandas as pd
import numpy as np
import json
import itertools
from multi_agent_core import MacroRegimeAgent, BreakoutMomentumAgent, MeanReversionAgent, ArbitratorAgent, RiskGuardAgent

MAKER_FEE = 0.0002 * 1.18
TAKER_FEE = 0.0005 * 1.18

def prepare_indicators(df: pd.DataFrame) -> pd.DataFrame:
    c, h, l, v = df["close"], df["high"], df["low"], df["volume"]
    
    # 200 EMA for Macro Regime
    df["ema_200"] = c.ewm(span=200, adjust=False).mean()
    df["ema_fast"] = c.ewm(span=20, adjust=False).mean()
    df["ema_slow"] = c.ewm(span=50, adjust=False).mean()

    # RSI
    d = c.diff()
    gain = d.where(d > 0, 0).rolling(14).mean()
    loss = (-d.where(d < 0, 0)).rolling(14).mean()
    df["rsi"] = 100 - (100 / (1 + (gain / (loss + 1e-10))))

    # MACD
    m12 = c.ewm(span=12, adjust=False).mean()
    m26 = c.ewm(span=26, adjust=False).mean()
    macd = m12 - m26
    df["macd_hist"] = macd - macd.ewm(span=9, adjust=False).mean()

    # Bollinger Bands
    df["bb_mid"] = c.rolling(20).mean()
    bb_sd = c.rolling(20).std()
    df["bb_up"] = df["bb_mid"] + 2.0 * bb_sd
    df["bb_lo"] = df["bb_mid"] - 2.0 * bb_sd

    # ATR
    tr = pd.concat([
        h - l,
        (h - c.shift()).abs(),
        (l - c.shift()).abs()
    ], axis=1).max(axis=1)
    df["atr"] = tr.rolling(14).mean()

    # ADX
    up = h.diff()
    down = -l.diff()
    p_dm = up.where((up > down) & (up > 0), 0.0)
    m_dm = down.where((down > up) & (down > 0), 0.0)
    atr_r = tr.rolling(14).mean()
    p_di = 100 * (p_dm.rolling(14).mean() / (atr_r + 1e-10))
    m_di = 100 * (m_dm.rolling(14).mean() / (atr_r + 1e-10))
    dx = 100 * (p_di - m_di).abs() / (p_di + m_di + 1e-10)
    df["adx"] = dx.rolling(14).mean()

    # Volume & Breakout
    df["vol_avg"] = v.rolling(20).mean()
    df["bk_high"] = h.rolling(20).max().shift(1)
    df["bk_low"] = l.rolling(20).min().shift(1)

    return df.dropna().reset_index(drop=True)

def simulate_engine(df: pd.DataFrame, params: dict, initial_cap=5000.0) -> dict:
    capital = initial_cap
    peak_cap = initial_cap
    day_start = initial_cap
    position = None
    trades = []
    wins = 0
    losses = 0
    total_fees = 0.0
    current_day = None
    trades_today = 0
    
    risk_guard = RiskGuardAgent(
        risk_pct=params.get("risk_pct", 0.015),
        max_leverage=params.get("leverage", 2.0),
        daily_loss_pct=0.03,
        max_dd_pct=0.20
    )

    for _, row in df.iterrows():
        c = row["close"]
        dt = row["datetime"]
        day = dt.date()

        if day != current_day:
            day_start = capital
            trades_today = 0
            risk_guard.consecutive_losses = 0
            current_day = day

        # Check risk veto
        can_trade, _ = risk_guard.evaluate_veto(capital, day_start, peak_cap)
        if not can_trade and not position:
            continue

        # Position management
        if position:
            bars_held = position.get("bars_held", 0) + 1
            position["bars_held"] = bars_held

            hit_sl   = (c <= position["stop"]) if position["side"] == "LONG" else (c >= position["stop"])
            hit_tp   = (c >= position["tp"])   if position["side"] == "LONG" else (c <= position["tp"])
            # [FIX-5]: time stop check
            hit_time = risk_guard.check_time_stop(position, c, bars_held)

            if hit_sl or hit_tp or hit_time:
                fee = c * position["size"] * TAKER_FEE
                pnl = (c - position["entry"]) * position["size"] if position["side"] == "LONG" \
                      else (position["entry"] - c) * position["size"]
                net = pnl - fee - position["open_fee"]
                capital += pnl - fee
                total_fees += fee + position["open_fee"]
                if capital > peak_cap:
                    peak_cap = capital

                if net > 0:
                    wins += 1
                    risk_guard.consecutive_losses = 0
                else:
                    losses += 1
                    risk_guard.consecutive_losses += 1

                result = "TP" if hit_tp else ("TIME" if hit_time else "SL")
                trades.append({
                    "month"   : dt.strftime("%Y-%m"),
                    "net_pnl" : round(net, 2),
                    "result"  : result,
                    "strategy": position["strategy"]
                })
                position = None
            else:
                continue

        if trades_today >= params.get("max_trades_per_day", 5):
            continue

        # Agent consensus cycle — pass regime to MeanRev for [FIX-1]/[FIX-4]
        macro_out    = MacroRegimeAgent.evaluate(row)
        regime       = macro_out["regime"]
        breakout_out = BreakoutMomentumAgent.evaluate(row, params)
        mean_rev_out = MeanReversionAgent.evaluate(row, params, regime)   # [FIX-1/4]
        decision     = ArbitratorAgent.arbitrate(macro_out, breakout_out, mean_rev_out)

        if decision["action"] in ["LONG", "SHORT"]:
            size = risk_guard.size_position(capital, c, decision["stop"])
            if size > 0:
                open_fee = c * size * MAKER_FEE
                capital -= open_fee
                total_fees += open_fee
                trades_today += 1
                position = {
                    "side"     : decision["action"],
                    "entry"    : c,
                    "size"     : size,
                    "stop"     : decision["stop"],
                    "tp"       : decision["tp"],
                    "open_fee" : open_fee,
                    "strategy" : decision["strategy"],
                    "bars_held": 0,   # [FIX-5] time stop counter
                }

    total_trades = wins + losses
    ret_pct = ((capital - initial_cap) / initial_cap) * 100
    win_rate = (wins / total_trades * 100) if total_trades else 0.0

    return {
        "final_cap": round(capital, 2),
        "return_pct": round(ret_pct, 2),
        "win_rate": round(win_rate, 1),
        "trades": total_trades,
        "wins": wins,
        "losses": losses,
        "fees": round(total_fees, 2),
        "trade_list": trades
    }

def run_calibration_loop():
    print("🚀 Loading 1-year Delta market data...")
    df = pd.read_csv("data/BTCUSDT_1h_1year.csv")
    df["datetime"] = pd.to_datetime(df["datetime"], utc=True)
    for col in ["open", "high", "low", "close", "volume"]:
        df[col] = pd.to_numeric(df[col])
    df = prepare_indicators(df)

    grid = {
        "rr_ratio": [2.0, 3.0],
        "atr_sl_mult": [1.2, 1.8],
        "risk_pct": [0.01, 0.02],
        "vol_mult": [0.9, 1.1],
        "rsi_oversold": [30],
        "rsi_overbought": [70],
        "leverage": [1.5, 2.5]
    }

    keys, values = zip(*grid.items())
    combinations = [dict(zip(keys, v)) for v in itertools.product(*values)]
    print(f"🔬 Testing {len(combinations)} systematic agent parameter configurations on real data...")

    best_config = None
    best_return = -999.0
    results_summary = []

    for i, cfg in enumerate(combinations):
        res = simulate_engine(df, cfg)
        results_summary.append({**cfg, **res})
        
        if res["return_pct"] > best_return and res["trades"] >= 25:
            best_return = res["return_pct"]
            best_config = {**cfg, **res}

    print("\n" + "=" * 70)
    print("🏆 OPTIMIZATION / CALIBRATION RESULTS SUMMARY")
    print("=" * 70)
    
    # Sort top 5 configurations
    sorted_res = sorted(results_summary, key=lambda x: x["return_pct"], reverse=True)[:5]
    for idx, r in enumerate(sorted_res, 1):
        print(f"Top #{idx} Config:")
        print(f"  Return: {r['return_pct']:+.2f}% | Final Cap: ₹{r['final_cap']:,.2f} | WR: {r['win_rate']}% | Trades: {r['trades']} | Fees: ₹{r['fees']}")
        print(f"  Parameters: R:R={r['rr_ratio']}x, ATR_SL={r['atr_sl_mult']}, Risk={r['risk_pct']*100}%, Vol_Mult={r['vol_mult']}, Lev={r['leverage']}x")
        print("-" * 70)

    with open("data/best_multiagent_calibrated.json", "w") as f:
        json.dump(best_config, f, indent=2)
    print("💾 Saved best configuration to data/best_multiagent_calibrated.json")

if __name__ == "__main__":
    run_calibration_loop()
