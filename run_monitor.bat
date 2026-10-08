@echo off
title HERMES // LIVE CRYPTO POSITIONS MONITOR
color 0B
cd /d C:\Projects\delta-trading-v5\hybrid-ai-trader\best_laya_autonomous_v5
python -m pip install alpaca-py
python monitor_positions.py
pause
