# Laya AI Ensemble Guard

## Agents
1. **Agent 1 (Macro Trend):** Evaluates 200-EMA and ADX to dictate trend permission.
2. **Agent 2 (Volatility ATR):** Dynamically adjusts stop-distances based on market volatility.
3. **Agent 3 (Momentum RSI):** Filters overbought/oversold conditions (RSI 38–62).

## Confidence Gating
- Minimum required confidence score: **83%**
- Rejects choppy or uncertain market regimes automatically.
