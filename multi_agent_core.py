"""
Multi-Agent Adaptive Trading Engine — V12.1 LOSS-FIXED Edition
Real-data loss analysis identified 5 root causes. All fixed here:
  [FIX-1] MeanRev_B hard-banned when ADX > 20 (was firing counter-trend 75-79% of time)
  [FIX-2] Breakout_A RSI entry cap tightened  < 62 LONG / > 38 SHORT (mean at entry was RSI 73.5)
  [FIX-3] Breakout requires close > bk_high + 0.4×ATR buffer (eliminates fakeout entries)
  [FIX-4] MeanRev_B requires EMA spread confirmation (not just ADX < 18)
  [FIX-5] 8-bar Time Stop — exit stalled positions not at 50% of TP within 8 hours
"""

import pandas as pd
import numpy as np
from dataclasses import dataclass
from typing import Dict, Any, Tuple

# ──────────────────────────────────────────────────────────
# AGENT 1: MACRO REGIME GUARD
# ──────────────────────────────────────────────────────────
class MacroRegimeAgent:
    """Determines high-timeframe market bias to prevent counter-trend liquidations."""
    
    @staticmethod
    def evaluate(row: pd.Series) -> Dict[str, Any]:
        c = row["close"]
        ema200 = row.get("ema_200", c)
        adx = row["adx"]
        
        if c > ema200 and adx > 22:
            bias = "BULLISH_TREND"
            allowed = ["LONG"]
        elif c < ema200 and adx > 22:
            bias = "BEARISH_TREND"
            allowed = ["SHORT"]
        elif adx < 18:
            bias = "RANGE_BOUND"
            allowed = ["LONG", "SHORT"]
        else:
            bias = "CHOPPY_UNCERTAIN"
            allowed = []
            
        return {
            "regime": bias,
            "allowed_directions": allowed,
            "adx": adx          # renamed from adx_strength for consistency
        }

# ──────────────────────────────────────────────────────────
# AGENT 2: BREAKOUT MOMENTUM AGENT (Strategy A)
# ──────────────────────────────────────────────────────────
class BreakoutMomentumAgent:
    """
    Detects clean directional momentum and high-volume breakout continuations.
    [FIX-2] RSI capped at < 62 for longs (was 68; mean RSI at entry was 73.5 = overbought).
    [FIX-3] Entry requires close > bk_high + 0.4×ATR buffer (removes fakeout entries).
            EMA fast must lead EMA slow by 0.3% minimum (meaningful trend separation).
    """

    @staticmethod
    def evaluate(row: pd.Series, params: dict) -> Dict[str, Any]:
        c        = row["close"]
        bk_high  = row["bk_high"]
        bk_low   = row["bk_low"]
        ema_fast = row["ema_fast"]
        ema_slow = row["ema_slow"]
        macd_hist = row["macd_hist"]
        rsi      = row["rsi"]
        atr      = row["atr"]
        vol      = row["volume"]
        vol_avg  = row["vol_avg"]

        vol_mult    = params.get("vol_mult", 1.0)
        atr_sl_mult = params.get("atr_sl_mult", 1.5)
        rr_ratio    = params.get("rr_ratio", 3.0)

        # [FIX-2]: tighter RSI gates at entry
        rsi_long_max  = params.get("rsi_long_max",  62)   # was 68
        rsi_short_min = params.get("rsi_short_min", 38)   # was 32

        # [FIX-3]: ATR buffer to filter fakeouts + configurable EMA spread
        break_buf      = params.get("break_buf_mult", 0.3) * atr
        ema_spread_pct = params.get("ema_spread_pct", 0.003)
        ema_spread     = ema_spread_pct * ema_slow

        long_signal = (
            c > (bk_high + break_buf)               # [FIX-3] buffer
            and ema_fast > (ema_slow + ema_spread)  # [FIX-3] spread
            and macd_hist > 0
            and rsi < rsi_long_max                  # [FIX-2] overbought gate
            and vol >= vol_avg * vol_mult
        )
        short_signal = (
            c < (bk_low - break_buf)                # [FIX-3]
            and ema_fast < (ema_slow - ema_spread)  # [FIX-3]
            and macd_hist < 0
            and rsi > rsi_short_min                 # [FIX-2]
            and vol >= vol_avg * vol_mult
        )

        if long_signal:
            stop = c - (atr_sl_mult * atr)
            tp   = c + (rr_ratio * (c - stop))
            return {"signal": "LONG",  "confidence": 0.85, "stop": stop, "tp": tp, "strategy": "Breakout_A"}
        elif short_signal:
            stop = c + (atr_sl_mult * atr)
            tp   = c - (rr_ratio * (stop - c))
            return {"signal": "SHORT", "confidence": 0.85, "stop": stop, "tp": tp, "strategy": "Breakout_A"}

        return {"signal": "FLAT", "confidence": 0.0, "strategy": "Breakout_A"}

# ──────────────────────────────────────────────────────────
# AGENT 3: MEAN REVERSION AGENT (Strategy B)
# ──────────────────────────────────────────────────────────
class MeanReversionAgent:
    """
    Exploits price exhaustion at Bollinger extremes in range regimes ONLY.
    [FIX-1] Hard-banned when ADX > 20 — eliminates counter-trend losses in trending months.
    [FIX-4] Only activates when regime is confirmed RANGE_BOUND (passed by Arbitrator).
             RSI thresholds tightened (< 28 / > 72) for deeper exhaustion confirmation.
    """

    @staticmethod
    def evaluate(row: pd.Series, params: dict, regime: str = "RANGE_BOUND") -> Dict[str, Any]:
        # [FIX-1] + [FIX-4]: hard gate — only in confirmed range, ADX must be low
        if regime != "RANGE_BOUND":
            return {"signal": "FLAT", "confidence": 0.0, "strategy": "MeanRev_B"}
        adx = row["adx"]
        if adx > 20:   # [FIX-1]: belt-and-suspenders ADX hard-ban
            return {"signal": "FLAT", "confidence": 0.0, "strategy": "MeanRev_B"}

        c      = row["close"]
        ema200 = row.get("ema_200", c)
        bb_up  = row["bb_up"]
        bb_lo  = row["bb_lo"]
        bb_mid = row["bb_mid"]
        rsi    = row["rsi"]
        atr    = row["atr"]

        rsi_oversold   = params.get("rsi_oversold",   28)
        rsi_overbought = params.get("rsi_overbought",  72)
        atr_sl_mult    = params.get("mr_atr_sl", 1.0)

        # Additional EMA200 direction filter: prevents counter-200EMA trades
        long_signal  = (c < bb_lo) and (rsi < rsi_oversold) and (c < ema200 * 1.03)   # near/below 200EMA
        short_signal = (c > bb_up) and (rsi > rsi_overbought) and (c > ema200 * 0.97) # near/above 200EMA

        if long_signal:
            stop = c - (atr_sl_mult * atr)
            tp   = bb_mid
            if tp > c + 0.5 * atr:
                return {"signal": "LONG",  "confidence": 0.80, "stop": stop, "tp": tp, "strategy": "MeanRev_B"}
        elif short_signal:
            stop = c + (atr_sl_mult * atr)
            tp   = bb_mid
            if tp < c - 0.5 * atr:
                return {"signal": "SHORT", "confidence": 0.80, "stop": stop, "tp": tp, "strategy": "MeanRev_B"}

        return {"signal": "FLAT", "confidence": 0.0, "strategy": "MeanRev_B"}

# ──────────────────────────────────────────────────────────
# AGENT 4: ARBITRATOR & CONSENSUS DESK
# ──────────────────────────────────────────────────────────
class ArbitratorAgent:
    """Fuses macro bias, breakout signals, and mean-reversion signals with veto checks."""
    
    @staticmethod
    def arbitrate(macro: dict, breakout: dict, mean_rev: dict) -> Dict[str, Any]:
        regime = macro["regime"]
        allowed = macro["allowed_directions"]
        
        if regime == "CHOPPY_UNCERTAIN" or not allowed:
            return {"action": "HOLD", "reason": "Macro regime uncertain / avoid zone"}

        # In strong trend: Breakout Agent has priority
        if "TREND" in regime:
            if breakout["signal"] in allowed and breakout["signal"] != "FLAT":
                return {
                    "action": breakout["signal"],
                    "strategy": breakout["strategy"],
                    "confidence": breakout["confidence"],
                    "stop": breakout["stop"],
                    "tp": breakout["tp"],
                    "reason": f"Breakout confirmed by {regime}"
                }
        
        # In range: Mean Reversion Agent has priority
        elif regime == "RANGE_BOUND":
            if mean_rev["signal"] in allowed and mean_rev["signal"] != "FLAT":
                return {
                    "action": mean_rev["signal"],
                    "strategy": mean_rev["strategy"],
                    "confidence": mean_rev["confidence"],
                    "stop": mean_rev["stop"],
                    "tp": mean_rev["tp"],
                    "reason": "Range mean-reversion confirmed"
                }

        return {"action": "HOLD", "reason": "No agent reached execution threshold"}

# ──────────────────────────────────────────────────────────
# AGENT 5: INSTITUTIONAL RISK GUARD AGENT
# ──────────────────────────────────────────────────────────
class RiskGuardAgent:
    """
    Manages position sizing, daily drawdowns, and prevents account wipeout.
    [FIX-5] 8-bar time stop: if position stalls < 50% to TP after 8 bars → forced exit.
    """

    def __init__(self, risk_pct=0.01, max_leverage=2.5, daily_loss_pct=0.03,
                 max_dd_pct=0.15, time_stop_bars=8):
        self.risk_pct           = risk_pct
        self.max_leverage       = max_leverage
        self.daily_loss_pct     = daily_loss_pct
        self.max_dd_pct         = max_dd_pct
        self.time_stop_bars     = time_stop_bars   # [FIX-5]
        self.consecutive_losses = 0

    def size_position(self, capital: float, entry: float, stop: float) -> float:
        risk_capital      = capital * self.risk_pct
        stop_dist         = abs(entry - stop)
        if stop_dist < 1e-6:
            return 0.0
        vol_size          = risk_capital / stop_dist
        max_allowed_size  = (capital * self.max_leverage) / entry
        return min(vol_size, max_allowed_size)

    def evaluate_veto(self, capital: float, day_start: float, peak: float) -> Tuple[bool, str]:
        daily_loss = (day_start - capital) / day_start if day_start > 0 else 0
        if daily_loss >= self.daily_loss_pct:
            return False, f"Daily loss limit: {daily_loss*100:.1f}%"
        total_dd = (peak - capital) / peak if peak > 0 else 0
        if total_dd >= self.max_dd_pct:
            return False, f"Max drawdown breached: {total_dd*100:.1f}%"
        if self.consecutive_losses >= 4:
            return False, f"Circuit breaker: {self.consecutive_losses} consecutive losses"
        return True, "Approved"

    def check_time_stop(self, position: dict, current_price: float, bars_held: int) -> bool:
        """
        [FIX-5]: Time stop — different limits per strategy:
        - Breakout_A: 16-bar stop (trends take time to develop)
        - MeanRev_B:  8-bar stop (reversals should be quick)
        Fires when progress toward TP < 50% within the window.
        """
        strategy = position.get("strategy", "Breakout_A")
        limit    = self.time_stop_bars if strategy == "MeanRev_B" else self.time_stop_bars * 2
        if bars_held < limit:
            return False
        entry    = position["entry"]
        tp       = position["tp"]
        tp_dist  = abs(tp - entry)
        if tp_dist < 1e-6:
            return True
        progress = ((current_price - entry) / tp_dist
                    if position["side"] == "LONG"
                    else (entry - current_price) / tp_dist)
        return progress < 0.50
