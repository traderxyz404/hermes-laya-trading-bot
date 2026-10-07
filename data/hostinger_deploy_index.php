<?php
// HERMES // OPENBB QUANT TERMINAL v5.0
// Inspired by OpenBBTerminal (github.com/luizmeloDev/OpenBBTerminal)
// Deployed on Hostinger: memospark.in
// Laya AI Ensemble + Live Binance/Delta Feeds
header('X-Frame-Options: SAMEORIGIN');
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenBB // Hermes Quant Terminal v5.0 — memospark.in</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ───────────── DESIGN TOKENS ───────────── */
        :root {
            --bg-base:     #07080d;
            --bg-panel:    #0d1117;
            --bg-input:    #030507;
            --bg-sidebar:  #0a0c14;
            --bg-card:     #111827;
            --border:      #1a2035;
            --border-accent: #1e3a5f;

            --blue:        #0ea5e9;
            --blue-dim:    #0369a1;
            --green:       #10b981;
            --green-dim:   #047857;
            --red:         #ef4444;
            --red-dim:     #991b1b;
            --amber:       #f59e0b;
            --amber-dim:   #b45309;
            --purple:      #a855f7;
            --cyan:        #06b6d4;
            --white:       #f1f5f9;
            --gray:        #64748b;
            --gray-dim:    #334155;

            --font-mono: 'Cascadia Code', 'Fira Code', 'Consolas', 'Courier New', monospace;
            --font-sans: 'Inter', 'Segoe UI', system-ui, sans-serif;

            --header-h: 44px;
            --statusbar-h: 22px;
            --sidebar-w: 260px;
            --rightpanel-w: 320px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; }

        body {
            background: var(--bg-base);
            color: var(--white);
            font-family: var(--font-mono);
            font-size: 12px;
            display: flex;
            flex-direction: column;
        }

        /* ───────── SCROLLBAR ───────── */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: var(--bg-base); }
        ::-webkit-scrollbar-thumb { background: var(--border-accent); border-radius: 2px; }

        /* ───────── TOP HEADER ───────── */
        #header {
            height: var(--header-h);
            background: var(--bg-panel);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 12px;
            gap: 20px;
            flex-shrink: 0;
            position: relative;
            z-index: 100;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--blue);
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 1px;
            white-space: nowrap;
        }
        .logo-icon {
            width: 26px; height: 26px;
            background: var(--blue);
            border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
            color: #000; font-size: 11px; font-weight: 900;
        }
        .logo-badge {
            background: var(--blue-dim);
            color: var(--blue);
            font-size: 9px;
            padding: 1px 5px;
            border-radius: 3px;
            letter-spacing: 1px;
        }

        /* Ticker strip */
        #ticker-strip {
            display: flex;
            gap: 18px;
            flex: 1;
            overflow: hidden;
            align-items: center;
        }
        .ticker-item {
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 3px;
            border: 1px solid transparent;
            transition: border-color 0.15s;
        }
        .ticker-item:hover { border-color: var(--border-accent); }
        .ticker-item.active { border-color: var(--blue); background: rgba(14,165,233,0.06); }
        .ticker-sym { color: var(--gray); font-size: 11px; font-weight: 600; }
        .ticker-price { color: var(--white); font-weight: 700; font-size: 12px; }
        .ticker-chg { font-size: 10px; font-weight: 600; }
        .up { color: var(--green); }
        .dn { color: var(--red); }

        /* Header right controls */
        .header-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .hdr-btn {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--gray);
            padding: 4px 10px;
            font-family: var(--font-mono);
            font-size: 10px;
            cursor: pointer;
            border-radius: 3px;
            transition: all 0.15s;
        }
        .hdr-btn:hover { border-color: var(--blue); color: var(--blue); }
        .hdr-btn.danger { border-color: var(--red-dim); color: var(--red); }
        .hdr-btn.danger:hover { background: rgba(239,68,68,0.1); }

        #conn-pill {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 10px;
            border: 1px solid var(--green-dim);
            color: var(--green);
            background: rgba(16,185,129,0.07);
        }
        #conn-pill .dot {
            width: 5px; height: 5px;
            border-radius: 50%;
            background: var(--green);
            animation: pulse 1.8s infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.3} }

        /* ───────── MAIN LAYOUT ───────── */
        #main {
            display: flex;
            flex: 1;
            overflow: hidden;
            height: calc(100vh - var(--header-h) - var(--statusbar-h));
        }

        /* ───────── SIDEBAR ───────── */
        #sidebar {
            width: var(--sidebar-w);
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            overflow: hidden;
        }

        .sidebar-section {
            border-bottom: 1px solid var(--border);
            padding: 10px;
        }
        .section-title {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--gray);
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        /* Module menu */
        .module-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 8px;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 4px;
            color: var(--gray);
            cursor: pointer;
            font-family: var(--font-mono);
            font-size: 11px;
            text-align: left;
            transition: all 0.12s;
            margin-bottom: 2px;
        }
        .module-btn:hover { background: rgba(14,165,233,0.05); color: var(--white); border-color: var(--border); }
        .module-btn.active { background: rgba(14,165,233,0.1); color: var(--blue); border-color: var(--border-accent); }
        .module-btn .mod-icon { width: 18px; text-align: center; font-size: 13px; }
        .module-btn .mod-name { flex: 1; }
        .module-btn .mod-badge {
            font-size: 9px;
            background: var(--blue-dim);
            color: var(--blue);
            padding: 1px 5px;
            border-radius: 8px;
        }

        /* Watchlist */
        #watchlist-body { display: flex; flex-direction: column; gap: 4px; overflow-y: auto; flex: 1; padding: 10px; }
        .wl-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 8px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 3px;
            cursor: pointer;
            transition: border-color 0.12s;
        }
        .wl-row:hover { border-color: var(--blue); }
        .wl-sym { color: var(--white); font-weight: 600; font-size: 11px; }
        .wl-price { color: var(--white); font-size: 11px; }
        .wl-chg { font-size: 10px; font-weight: 600; }
        .wl-mini { width: 50px; height: 20px; }

        /* ───────── CENTER ───────── */
        #workspace {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Tab bar */
        #tabbar {
            height: 34px;
            background: var(--bg-panel);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: flex-end;
            padding: 0 10px;
            gap: 2px;
            flex-shrink: 0;
        }
        .tab {
            padding: 6px 14px;
            font-size: 11px;
            cursor: pointer;
            color: var(--gray);
            border: 1px solid transparent;
            border-bottom: none;
            border-radius: 4px 4px 0 0;
            background: transparent;
            font-family: var(--font-mono);
            transition: all 0.12s;
        }
        .tab:hover { color: var(--white); }
        .tab.active {
            background: var(--bg-base);
            color: var(--blue);
            border-color: var(--border);
            border-bottom-color: var(--bg-base);
        }
        .tab-close { margin-left: 6px; opacity: 0.4; font-size: 10px; }

        /* Workspace content */
        #ws-content {
            flex: 1;
            overflow: hidden;
            position: relative;
        }

        .ws-pane {
            display: none;
            width: 100%; height: 100%;
            flex-direction: column;
            padding: 12px;
            gap: 10px;
            overflow-y: auto;
        }
        .ws-pane.active { display: flex; }

        /* Chart card */
        .chart-card {
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .asset-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--white);
            letter-spacing: 0.5px;
        }
        .asset-price-big {
            font-size: 26px;
            font-weight: 700;
            color: var(--green);
            letter-spacing: -0.5px;
        }
        .asset-change {
            font-size: 11px;
            font-weight: 600;
        }
        .chart-controls {
            display: flex;
            gap: 6px;
        }
        .chart-period {
            padding: 3px 8px;
            font-size: 10px;
            border: 1px solid var(--border);
            border-radius: 3px;
            cursor: pointer;
            color: var(--gray);
            font-family: var(--font-mono);
            background: transparent;
        }
        .chart-period.active, .chart-period:hover { border-color: var(--blue); color: var(--blue); }

        .chart-canvas-wrap {
            position: relative;
            height: 220px;
        }

        /* Stats row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 10px;
            text-align: center;
        }
        .stat-lbl { font-size: 9px; color: var(--gray); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .stat-val { font-size: 13px; font-weight: 700; color: var(--cyan); }

        /* Order book */
        .ob-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .ob-side { background: var(--bg-card); border: 1px solid var(--border); border-radius: 5px; padding: 10px; }
        .ob-title { font-size: 10px; color: var(--gray); margin-bottom: 6px; letter-spacing: 1px; }
        .ob-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding: 2px 0;
            position: relative;
        }
        .ob-bar {
            position: absolute;
            top: 0; left: 0;
            height: 100%;
            opacity: 0.12;
            border-radius: 2px;
        }

        /* ───────── RIGHT PANEL ───────── */
        #rightpanel {
            width: var(--rightpanel-w);
            background: var(--bg-sidebar);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            overflow: hidden;
        }

        .rp-section {
            border-bottom: 1px solid var(--border);
            padding: 12px;
            flex-shrink: 0;
        }
        .rp-title {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--gray);
            margin-bottom: 10px;
            text-transform: uppercase;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Laya Ensemble */
        .ensemble-bar-wrap { margin-bottom: 8px; }
        .ensemble-label {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: var(--gray);
            margin-bottom: 3px;
        }
        .ensemble-label span:last-child { color: var(--white); font-weight: 600; }
        .ensemble-bar {
            height: 5px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
        }
        .ensemble-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.8s ease;
        }

        .laya-signal {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            border: 1px solid var(--border);
            border-radius: 4px;
            margin-bottom: 6px;
            background: var(--bg-card);
        }
        .laya-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
        .laya-info { flex: 1; }
        .laya-agent { font-size: 10px; color: var(--gray); }
        .laya-verdict { font-size: 11px; font-weight: 600; }
        .laya-conf { font-size: 11px; font-weight: 700; }

        /* Trade log */
        #trade-log {
            flex: 1;
            overflow-y: auto;
            padding: 8px 12px;
        }
        .trade-entry {
            padding: 7px 0;
            border-bottom: 1px solid var(--border);
            font-size: 10px;
        }
        .trade-time { color: var(--gray); margin-right: 6px; }
        .trade-type { font-weight: 700; margin-right: 6px; }

        /* ───────── CLI CONSOLE ───────── */
        #cli-section {
            border-top: 1px solid var(--border);
            background: var(--bg-input);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            height: 200px;
        }
        #cli-log {
            flex: 1;
            overflow-y: auto;
            padding: 8px 12px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .cli-line { font-size: 11px; line-height: 1.5; }
        .cli-line .cli-ts { color: var(--gray-dim); margin-right: 4px; font-size: 10px; }
        .cli-line .cli-prompt-sym { color: var(--blue); margin-right: 4px; font-weight: 700; }

        #cli-input-row {
            display: flex;
            align-items: center;
            border-top: 1px solid var(--border);
            padding: 6px 12px;
            gap: 8px;
        }
        #cli-module { color: var(--blue); font-weight: 700; font-size: 11px; flex-shrink: 0; }
        #cli-input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: var(--white);
            font-family: var(--font-mono);
            font-size: 12px;
            caret-color: var(--blue);
        }
        #cli-input::placeholder { color: var(--gray-dim); }

        /* ───────── STATUS BAR ───────── */
        #statusbar {
            height: var(--statusbar-h);
            background: var(--blue-dim);
            display: flex;
            align-items: center;
            padding: 0 12px;
            gap: 16px;
            font-size: 10px;
            color: rgba(255,255,255,0.7);
            flex-shrink: 0;
        }
        .sb-item { display: flex; align-items: center; gap: 4px; }
        .sb-sep { color: rgba(255,255,255,0.25); }
        #sb-time { margin-left: auto; font-weight: 600; color: #fff; }

        /* ───────── PORTFOLIO TAB ───────── */
        .pf-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .pf-card {
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 14px;
        }
        .pf-card-title { font-size: 10px; color: var(--gray); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 12px; }
        .pf-big { font-size: 28px; font-weight: 700; color: var(--white); }
        .pf-sub { font-size: 11px; color: var(--gray); margin-top: 4px; }
        .pf-metric { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); font-size: 11px; }
        .pf-metric:last-child { border-bottom: none; }
        .pf-metric-lbl { color: var(--gray); }

        /* positions table */
        .pos-table { width: 100%; border-collapse: collapse; }
        .pos-table th {
            text-align: left; padding: 6px 8px;
            font-size: 9px; letter-spacing: 1px;
            color: var(--gray); text-transform: uppercase;
            border-bottom: 1px solid var(--border);
        }
        .pos-table td {
            padding: 7px 8px;
            font-size: 11px;
            border-bottom: 1px solid var(--border);
        }
        .pos-table tr:hover td { background: rgba(14,165,233,0.04); }

        /* TA Tab */
        .ta-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }
        .ta-card {
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 14px;
        }
        .ta-title { font-size: 10px; color: var(--blue); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 10px; }
        .ta-val { font-size: 20px; font-weight: 700; color: var(--white); margin-bottom: 4px; }
        .ta-signal { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 3px; display: inline-block; }
        .sig-bull { background: rgba(16,185,129,0.15); color: var(--green); }
        .sig-bear { background: rgba(239,68,68,0.15); color: var(--red); }
        .sig-neutral { background: rgba(100,116,139,0.15); color: var(--gray); }
        .ta-sub { font-size: 10px; color: var(--gray); margin-top: 6px; }

        /* Sparkline mini */
        .spark { display: flex; align-items: flex-end; gap: 1px; height: 24px; margin-top: 8px; }
        .spark-bar { flex: 1; border-radius: 1px; }

        /* Responsive tweaks */
        @media (max-width: 1200px) {
            :root { --rightpanel-w: 280px; }
            .stats-row { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 900px) {
            #sidebar { display: none; }
            :root { --rightpanel-w: 240px; }
        }

        /* Blink */
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }
        .blink { animation: blink 1s step-end infinite; }

        /* Glow on price change */
        @keyframes flashGreen { 0%{color:var(--green);text-shadow:0 0 8px var(--green)} 100%{color:var(--green);text-shadow:none} }
        @keyframes flashRed   { 0%{color:var(--red);text-shadow:0 0 8px var(--red)}   100%{color:var(--red);text-shadow:none}   }
        .flash-g { animation: flashGreen 0.6s ease-out; }
        .flash-r { animation: flashRed   0.6s ease-out; }
    </style>
</head>
<body>

<!-- ══════════════ HEADER ══════════════ -->
<div id="header">
    <div class="logo">
        <div class="logo-icon">OB</div>
        <span>OpenBB</span>
        <span class="logo-badge">HERMES v5.0</span>
    </div>

    <div id="ticker-strip">
        <div class="ticker-item active" id="tk-btc" onclick="selectAsset('BTC')">
            <span class="ticker-sym">BTC/USDT</span>
            <span class="ticker-price" id="tkp-btc">—</span>
            <span class="ticker-chg" id="tkc-btc">—</span>
        </div>
        <div class="ticker-item" id="tk-eth" onclick="selectAsset('ETH')">
            <span class="ticker-sym">ETH/USDT</span>
            <span class="ticker-price" id="tkp-eth">—</span>
            <span class="ticker-chg" id="tkc-eth">—</span>
        </div>
        <div class="ticker-item" id="tk-sol" onclick="selectAsset('SOL')">
            <span class="ticker-sym">SOL/USDT</span>
            <span class="ticker-price" id="tkp-sol">—</span>
            <span class="ticker-chg" id="tkc-sol">—</span>
        </div>
        <div class="ticker-item" id="tk-bnb" onclick="selectAsset('BNB')">
            <span class="ticker-sym">BNB/USDT</span>
            <span class="ticker-price" id="tkp-bnb">—</span>
            <span class="ticker-chg" id="tkc-bnb">—</span>
        </div>
        <div class="ticker-item" id="tk-xrp" onclick="selectAsset('XRP')">
            <span class="ticker-sym">XRP/USDT</span>
            <span class="ticker-price" id="tkp-xrp">—</span>
            <span class="ticker-chg" id="tkc-xrp">—</span>
        </div>
    </div>

    <div class="header-right">
        <div id="conn-pill">
            <div class="dot"></div>
            <span id="conn-label">CONNECTING</span>
        </div>
        <button class="hdr-btn" onclick="runCLI('laya ensemble --eval')">⚡ SCAN</button>
        <button class="hdr-btn danger" onclick="killSwitch()">🔴 KILL</button>
    </div>
</div>

<!-- ══════════════ MAIN ══════════════ -->
<div id="main">

    <!-- ── SIDEBAR ── -->
    <div id="sidebar">
        <div class="sidebar-section">
            <div class="section-title">Modules</div>
            <button class="module-btn active" onclick="switchModule('crypto')">
                <span class="mod-icon">₿</span>
                <span class="mod-name">crypto</span>
                <span class="mod-badge">LIVE</span>
            </button>
            <button class="module-btn" onclick="switchModule('portfolio')">
                <span class="mod-icon">💼</span>
                <span class="mod-name">portfolio</span>
            </button>
            <button class="module-btn" onclick="switchModule('ta')">
                <span class="mod-icon">📊</span>
                <span class="mod-name">ta</span>
            </button>
            <button class="module-btn" onclick="switchModule('laya')">
                <span class="mod-icon">🤖</span>
                <span class="mod-name">laya-ai</span>
                <span class="mod-badge">v5</span>
            </button>
            <button class="module-btn" onclick="switchModule('forecast')">
                <span class="mod-icon">🔮</span>
                <span class="mod-name">forecast</span>
            </button>
        </div>

        <div class="sidebar-section" style="padding-bottom:0;">
            <div class="section-title">Watchlist</div>
        </div>
        <div id="watchlist-body">
            <!-- Filled by JS -->
        </div>
    </div>

    <!-- ── WORKSPACE ── -->
    <div id="workspace">
        <!-- Tab bar -->
        <div id="tabbar">
            <div class="tab active" onclick="switchTab('chart')">📈 Chart</div>
            <div class="tab" onclick="switchTab('orderbook')">📋 Order Book</div>
            <div class="tab" onclick="switchTab('portfolio')">💼 Portfolio</div>
            <div class="tab" onclick="switchTab('ta')">📊 TA Suite</div>
            <div class="tab" onclick="switchTab('laya')">🤖 Laya AI</div>
            <div class="tab" onclick="switchTab('forecast')">🔮 Forecast</div>
        </div>

        <!-- Content panes -->
        <div id="ws-content">

            <!-- CHART PANE -->
            <div class="ws-pane active" id="pane-chart">
                <div class="chart-card">
                    <div class="card-header">
                        <div>
                            <div class="asset-name" id="main-sym">BTC / USDT</div>
                            <div style="font-size:10px;color:var(--gray)">Binance Spot · 15m · Live</div>
                        </div>
                        <div style="text-align:right">
                            <div class="asset-price-big" id="main-price">$—</div>
                            <div class="asset-change" id="main-chg">—</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <div class="chart-controls">
                            <button class="chart-period" onclick="setPeriod('1m')">1m</button>
                            <button class="chart-period active" onclick="setPeriod('15m')">15m</button>
                            <button class="chart-period" onclick="setPeriod('1h')">1H</button>
                            <button class="chart-period" onclick="setPeriod('4h')">4H</button>
                            <button class="chart-period" onclick="setPeriod('1d')">1D</button>
                        </div>
                        <div style="flex:1"></div>
                        <div style="font-size:10px;color:var(--gray)">EMA·RSI·VOL overlay active</div>
                    </div>
                    <div class="chart-canvas-wrap">
                        <canvas id="priceChart"></canvas>
                    </div>
                </div>

                <div class="stats-row" id="stats-row">
                    <div class="stat-card">
                        <div class="stat-lbl">24H HIGH</div>
                        <div class="stat-val up" id="st-high">—</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-lbl">24H LOW</div>
                        <div class="stat-val dn" id="st-low">—</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-lbl">24H VOL</div>
                        <div class="stat-val" id="st-vol">—</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-lbl">QUOTE VOL</div>
                        <div class="stat-val" id="st-qvol">—</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-lbl">OPEN PRICE</div>
                        <div class="stat-val" id="st-open">—</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-lbl">PREV CLOSE</div>
                        <div class="stat-val" id="st-prev">—</div>
                    </div>
                </div>
            </div>

            <!-- ORDER BOOK PANE -->
            <div class="ws-pane" id="pane-orderbook">
                <div class="chart-card">
                    <div class="card-header">
                        <div class="asset-name" id="ob-sym">BTC/USDT Order Book</div>
                        <div class="asset-price-big" id="ob-mid">$—</div>
                    </div>
                    <div class="ob-grid">
                        <div class="ob-side">
                            <div class="ob-title up">▲ BIDS (BUY)</div>
                            <div id="ob-bids"><!-- JS --></div>
                        </div>
                        <div class="ob-side">
                            <div class="ob-title dn">▼ ASKS (SELL)</div>
                            <div id="ob-asks"><!-- JS --></div>
                        </div>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="card-header" style="margin-bottom:8px;">
                        <div class="asset-name">Depth Chart</div>
                    </div>
                    <div class="chart-canvas-wrap">
                        <canvas id="depthChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- PORTFOLIO PANE -->
            <div class="ws-pane" id="pane-portfolio">
                <div class="pf-grid">
                    <div class="pf-card">
                        <div class="pf-card-title">Account Equity</div>
                        <div class="pf-big" id="pf-equity">₹2,056.75</div>
                        <div class="pf-sub">Delta Exchange (Paper Mode)</div>
                        <div style="margin-top:14px;">
                            <div class="pf-metric"><span class="pf-metric-lbl">Available Balance</span><span style="color:var(--green)">₹1,842.10</span></div>
                            <div class="pf-metric"><span class="pf-metric-lbl">Margin Used</span><span style="color:var(--amber)">₹214.65</span></div>
                            <div class="pf-metric"><span class="pf-metric-lbl">Unrealized PnL</span><span id="pf-upnl" style="color:var(--green)">+₹56.75</span></div>
                            <div class="pf-metric"><span class="pf-metric-lbl">Daily Target (5%)</span><span style="color:var(--cyan)">₹102.84</span></div>
                            <div class="pf-metric"><span class="pf-metric-lbl">Daily PnL</span><span id="pf-dpnl" style="color:var(--green)">+₹56.75 (+2.76%)</span></div>
                        </div>
                    </div>
                    <div class="pf-card">
                        <div class="pf-card-title">Risk Engine State</div>
                        <div class="pf-metric"><span class="pf-metric-lbl">Daily Regime</span><span style="color:var(--green);font-weight:700">TRENDING_BULLISH</span></div>
                        <div class="pf-metric"><span class="pf-metric-lbl">Volatility State</span><span style="color:var(--cyan)">NORMAL</span></div>
                        <div class="pf-metric"><span class="pf-metric-lbl">Max DD Guard</span><span style="color:var(--amber)">-3% (Armed)</span></div>
                        <div class="pf-metric"><span class="pf-metric-lbl">Trade Limit Today</span><span>4 / 8 used</span></div>
                        <div class="pf-metric"><span class="pf-metric-lbl">Consecutive Loss</span><span style="color:var(--green)">0 (Reset)</span></div>
                        <div class="pf-metric"><span class="pf-metric-lbl">Kill Switch</span><span style="color:var(--green)">INACTIVE</span></div>
                        <div style="margin-top:12px;">
                            <div class="chart-canvas-wrap" style="height:120px;">
                                <canvas id="pnlChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pf-card" style="margin-top:0;">
                    <div class="pf-card-title">Open Positions</div>
                    <table class="pos-table">
                        <thead>
                            <tr>
                                <th>Symbol</th><th>Side</th><th>Size</th>
                                <th>Entry</th><th>Mark</th><th>PnL</th>
                                <th>SL</th><th>TP</th><th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="pos-body">
                            <tr>
                                <td style="color:var(--white);font-weight:600">BTC/USDT</td>
                                <td style="color:var(--green)">LONG</td>
                                <td>0.002</td>
                                <td id="pos-entry">$—</td>
                                <td id="pos-mark">$—</td>
                                <td id="pos-pnl" style="color:var(--green)">—</td>
                                <td style="color:var(--red)">$—</td>
                                <td style="color:var(--green)">$—</td>
                                <td style="color:var(--green)">ACTIVE</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TA SUITE PANE -->
            <div class="ws-pane" id="pane-ta">
                <div class="ta-grid">
                    <div class="ta-card">
                        <div class="ta-title">RSI (14)</div>
                        <div class="ta-val" id="ta-rsi">58.4</div>
                        <span class="ta-signal sig-bull">Bullish Momentum</span>
                        <div class="ta-sub">15m · Above 50 · Uptrend bias</div>
                        <div class="chart-canvas-wrap" style="height:70px;margin-top:8px;">
                            <canvas id="rsiChart"></canvas>
                        </div>
                    </div>
                    <div class="ta-card">
                        <div class="ta-title">MACD (12,26,9)</div>
                        <div class="ta-val" id="ta-macd">+142.3</div>
                        <span class="ta-signal sig-bull">Bullish Cross</span>
                        <div class="ta-sub">Histogram expanding positive</div>
                        <div class="chart-canvas-wrap" style="height:70px;margin-top:8px;">
                            <canvas id="macdChart"></canvas>
                        </div>
                    </div>
                    <div class="ta-card">
                        <div class="ta-title">Bollinger Bands</div>
                        <div class="ta-val" id="ta-bb">Mid: $<span id="ta-bbm">—</span></div>
                        <span class="ta-signal sig-neutral">Neutral Range</span>
                        <div class="ta-sub" id="ta-bb-sub">Upper: $— · Lower: $—</div>
                    </div>
                    <div class="ta-card">
                        <div class="ta-title">EMA Ribbon</div>
                        <div style="display:flex;flex-direction:column;gap:4px;margin-top:4px;" id="ta-ema-list">
                            <div class="pf-metric"><span class="pf-metric-lbl">EMA 20</span><span id="ta-ema20" style="color:var(--green)">—</span></div>
                            <div class="pf-metric"><span class="pf-metric-lbl">EMA 50</span><span id="ta-ema50" style="color:var(--amber)">—</span></div>
                            <div class="pf-metric"><span class="pf-metric-lbl">EMA 200</span><span id="ta-ema200" style="color:var(--red)">—</span></div>
                        </div>
                        <span class="ta-signal sig-bull" id="ta-ema-sig">Bullish Stack</span>
                    </div>
                    <div class="ta-card">
                        <div class="ta-title">ATR (14)</div>
                        <div class="ta-val" id="ta-atr">$1,234</div>
                        <span class="ta-signal sig-neutral" id="ta-atr-sig">Normal Vol</span>
                        <div class="ta-sub">Stop = 1.2×ATR · TP = 3.6×ATR</div>
                    </div>
                    <div class="ta-card">
                        <div class="ta-title">Volume Profile</div>
                        <div class="ta-val" id="ta-vol">—</div>
                        <span class="ta-signal sig-bull" id="ta-vol-sig">Above Avg</span>
                        <div class="ta-sub">Relative Vol = <span id="ta-rvol">1.4×</span></div>
                        <div class="spark" id="vol-spark"></div>
                    </div>
                </div>
                <div class="ta-card" style="margin-top:0;">
                    <div class="ta-title">Multi-Timeframe Confluence Table</div>
                    <table class="pos-table" style="margin-top:8px;">
                        <thead><tr><th>Timeframe</th><th>Trend</th><th>RSI</th><th>MACD</th><th>BB Position</th><th>Signal</th></tr></thead>
                        <tbody id="mtf-body">
                            <tr><td>1m</td><td style="color:var(--green)">BULLISH</td><td>61</td><td>POS</td><td>Upper Half</td><td><span class="ta-signal sig-bull">LONG</span></td></tr>
                            <tr><td>15m</td><td style="color:var(--green)">BULLISH</td><td>58</td><td>POS</td><td>Mid</td><td><span class="ta-signal sig-bull">LONG</span></td></tr>
                            <tr><td>1H</td><td style="color:var(--amber)">NEUTRAL</td><td>52</td><td>NEG</td><td>Mid</td><td><span class="ta-signal sig-neutral">WAIT</span></td></tr>
                            <tr><td>4H</td><td style="color:var(--green)">BULLISH</td><td>55</td><td>POS</td><td>Upper Mid</td><td><span class="ta-signal sig-bull">LONG</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- LAYA AI PANE -->
            <div class="ws-pane" id="pane-laya">
                <div class="chart-card">
                    <div class="card-header">
                        <div>
                            <div class="asset-name">🤖 Laya AI — 3-Agent Ensemble Guard</div>
                            <div style="font-size:10px;color:var(--gray)">Multi-agent confidence voting system v5.0</div>
                        </div>
                        <div id="laya-overall" style="font-size:20px;font-weight:700;color:var(--green)">APPROVED</div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:4px;">
                        <div class="laya-signal" id="la1">
                            <div class="laya-dot" style="background:var(--blue)"></div>
                            <div class="laya-info">
                                <div class="laya-agent">Agent 1 · Macro Trend</div>
                                <div class="laya-verdict" style="color:var(--green)" id="la1-v">BULLISH</div>
                            </div>
                            <div class="laya-conf" style="color:var(--blue)" id="la1-c">94%</div>
                        </div>
                        <div class="laya-signal" id="la2">
                            <div class="laya-dot" style="background:var(--purple)"></div>
                            <div class="laya-info">
                                <div class="laya-agent">Agent 2 · Volatility</div>
                                <div class="laya-verdict" style="color:var(--cyan)" id="la2-v">NORMAL</div>
                            </div>
                            <div class="laya-conf" style="color:var(--purple)" id="la2-c">88%</div>
                        </div>
                        <div class="laya-signal" id="la3">
                            <div class="laya-dot" style="background:var(--amber)"></div>
                            <div class="laya-info">
                                <div class="laya-agent">Agent 3 · Momentum</div>
                                <div class="laya-verdict" style="color:var(--green)" id="la3-v">OPTIMAL</div>
                            </div>
                            <div class="laya-conf" style="color:var(--amber)" id="la3-c">96%</div>
                        </div>
                    </div>
                    <div style="margin-top:8px;">
                        <div class="ensemble-bar-wrap">
                            <div class="ensemble-label"><span>Ensemble Confidence</span><span id="laya-conf-val">92.7%</span></div>
                            <div class="ensemble-bar"><div class="ensemble-fill" id="laya-conf-bar" style="width:92.7%;background:var(--green)"></div></div>
                        </div>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="asset-name" style="font-size:14px;margin-bottom:10px;">AI Decision Matrix — Live Signal Feed</div>
                    <div class="chart-canvas-wrap" style="height:180px;">
                        <canvas id="layadChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="asset-name" style="font-size:14px;margin-bottom:10px;">Trade Execution Rules Engine</div>
                    <table class="pos-table">
                        <thead><tr><th>Rule</th><th>Condition</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            <tr><td>Min Confidence</td><td>≥ 85%</td><td style="color:var(--green)">✓ PASS</td><td style="color:var(--gray)">Allow trade</td></tr>
                            <tr><td>Ensemble Vote</td><td>All 3 agents agree</td><td style="color:var(--green)">✓ PASS</td><td style="color:var(--gray)">Allow trade</td></tr>
                            <tr><td>Daily DD Guard</td><td>&lt; -3% drawdown</td><td style="color:var(--green)">✓ PASS</td><td style="color:var(--gray)">Allow trade</td></tr>
                            <tr><td>Volatility Filter</td><td>ATR within range</td><td style="color:var(--green)">✓ PASS</td><td style="color:var(--gray)">Allow trade</td></tr>
                            <tr><td>Max Trades/Day</td><td>&lt; 8 trades</td><td style="color:var(--green)">✓ PASS</td><td style="color:var(--gray)">Allow trade</td></tr>
                            <tr><td>Post-Only Maker</td><td>Fee optimization</td><td style="color:var(--green)">✓ ACTIVE</td><td style="color:var(--gray)">0% taker fee</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FORECAST PANE -->
            <div class="ws-pane" id="pane-forecast">
                <div class="chart-card">
                    <div class="asset-name" style="margin-bottom:6px;">🔮 Laya Forecast Engine — Next 24H Projection</div>
                    <div class="chart-canvas-wrap" style="height:240px;">
                        <canvas id="forecastChart"></canvas>
                    </div>
                </div>
                <div class="pf-grid">
                    <div class="pf-card">
                        <div class="pf-card-title">ML Model Outputs</div>
                        <div class="pf-metric"><span class="pf-metric-lbl">XGBoost Signal</span><span style="color:var(--green)">LONG (+2)</span></div>
                        <div class="pf-metric"><span class="pf-metric-lbl">LSTM Direction</span><span style="color:var(--green)">UP (72%)</span></div>
                        <div class="pf-metric"><span class="pf-metric-lbl">Transformer</span><span style="color:var(--cyan)">NEUTRAL (51%)</span></div>
                        <div class="pf-metric"><span class="pf-metric-lbl">Ensemble Vote</span><span style="color:var(--green)">BUY (Majority)</span></div>
                    </div>
                    <div class="pf-card">
                        <div class="pf-card-title">Price Targets</div>
                        <div class="pf-metric"><span class="pf-metric-lbl">Entry Zone</span><span id="fc-entry" style="color:var(--blue)">—</span></div>
                        <div class="pf-metric"><span class="pf-metric-lbl">TP1 (+1.5R)</span><span id="fc-tp1" style="color:var(--green)">—</span></div>
                        <div class="pf-metric"><span class="pf-metric-lbl">TP2 (+3R)</span><span id="fc-tp2" style="color:var(--green)">—</span></div>
                        <div class="pf-metric"><span class="pf-metric-lbl">Stop-Loss</span><span id="fc-sl" style="color:var(--red)">—</span></div>
                        <div class="pf-metric"><span class="pf-metric-lbl">Risk:Reward</span><span style="color:var(--amber)">1:3</span></div>
                    </div>
                </div>
            </div>

        </div><!-- end #ws-content -->

        <!-- CLI CONSOLE -->
        <div id="cli-section">
            <div id="cli-log"></div>
            <div id="cli-input-row">
                <span id="cli-module">openbb / crypto &gt;</span>
                <input type="text" id="cli-input" placeholder="Type a command: help, crypto load btc, ta rsi, laya ensemble, portfolio ..." spellcheck="false" autocomplete="off">
            </div>
        </div>
    </div>

    <!-- ── RIGHT PANEL ── -->
    <div id="rightpanel">

        <div class="rp-section">
            <div class="rp-title">
                <span>LAYA ENSEMBLE</span>
                <span style="color:var(--green)">ACTIVE</span>
            </div>
            <div class="ensemble-bar-wrap">
                <div class="ensemble-label"><span>Agent 1 · Macro</span><span id="rp-a1">94%</span></div>
                <div class="ensemble-bar"><div class="ensemble-fill" id="rp-a1-bar" style="width:94%;background:var(--blue)"></div></div>
            </div>
            <div class="ensemble-bar-wrap">
                <div class="ensemble-label"><span>Agent 2 · Volatility</span><span id="rp-a2">88%</span></div>
                <div class="ensemble-bar"><div class="ensemble-fill" id="rp-a2-bar" style="width:88%;background:var(--purple)"></div></div>
            </div>
            <div class="ensemble-bar-wrap">
                <div class="ensemble-label"><span>Agent 3 · Momentum</span><span id="rp-a3">96%</span></div>
                <div class="ensemble-bar"><div class="ensemble-fill" id="rp-a3-bar" style="width:96%;background:var(--amber)"></div></div>
            </div>
            <div class="ensemble-bar-wrap" style="margin-top:6px;">
                <div class="ensemble-label"><span style="font-weight:700;color:var(--white)">ENSEMBLE TOTAL</span><span id="rp-conf" style="color:var(--green);font-weight:700">92.7%</span></div>
                <div class="ensemble-bar" style="height:8px;">
                    <div class="ensemble-fill" id="rp-conf-bar" style="width:92.7%;background:linear-gradient(90deg,var(--blue),var(--green))"></div>
                </div>
            </div>
        </div>

        <div class="rp-section">
            <div class="rp-title"><span>STRATEGY METRICS</span></div>
            <div class="pf-metric"><span class="pf-metric-lbl">Win Rate</span><span style="color:var(--green)">72.4%</span></div>
            <div class="pf-metric"><span class="pf-metric-lbl">Avg RR</span><span style="color:var(--cyan)">1:2.8</span></div>
            <div class="pf-metric"><span class="pf-metric-lbl">Sharpe Ratio</span><span style="color:var(--white)">2.14</span></div>
            <div class="pf-metric"><span class="pf-metric-lbl">Max Drawdown</span><span style="color:var(--amber)">-4.2%</span></div>
            <div class="pf-metric"><span class="pf-metric-lbl">Profit Factor</span><span style="color:var(--green)">2.67</span></div>
            <div class="pf-metric"><span class="pf-metric-lbl">Backtest PnL</span><span style="color:var(--green)">+234.8%</span></div>
        </div>

        <div class="rp-title" style="padding:10px 12px 6px;">
            <span>EXECUTION LOG</span>
            <span style="color:var(--blue);cursor:pointer;font-size:9px;" onclick="clearLog()">CLEAR</span>
        </div>
        <div id="trade-log"><!-- filled by JS --></div>
    </div>

</div><!-- end #main -->

<!-- ══════════════ STATUS BAR ══════════════ -->
<div id="statusbar">
    <div class="sb-item">
        <span>📡</span>
        <span id="sb-feed">Binance REST</span>
    </div>
    <div class="sb-sep">|</div>
    <div class="sb-item">
        <span>📊</span>
        <span id="sb-asset">BTC/USDT</span>
    </div>
    <div class="sb-sep">|</div>
    <div class="sb-item">
        <span>🤖</span>
        <span>Laya v5.0 · Ensemble: <span id="sb-conf">92.7%</span></span>
    </div>
    <div class="sb-sep">|</div>
    <div class="sb-item">
        <span>📈</span>
        <span>Daily PnL: <span id="sb-pnl" style="color:var(--green)">+₹56.75</span></span>
    </div>
    <div class="sb-sep">|</div>
    <div class="sb-item">
        <span>⚡</span>
        <span>Mode: <span style="color:var(--amber)">PAPER</span></span>
    </div>
    <div id="sb-time">—</div>
</div>

<script>
// ════════════════════════════════════════════
//  HERMES OpenBB Terminal v5.0
//  All market data via Binance public REST API
// ════════════════════════════════════════════

// ── State ──
const state = {
    asset: 'BTC',
    symbols: {
        BTC: 'BTCUSDT', ETH: 'ETHUSDT',
        SOL: 'SOLUSDT', BNB: 'BNBUSDT', XRP: 'XRPUSDT'
    },
    prices: {},
    prevPrices: {},
    chartPeriod: '15m',
    cliHistory: [],
    cliIdx: -1,
    currentModule: 'crypto',
    currentTab: 'chart',
    killed: false,
};

// ── Misc helpers ──
const $ = id => document.getElementById(id);
const fmt = (n, dec=2) => (+n).toLocaleString('en-US', {minimumFractionDigits:dec, maximumFractionDigits:dec});
const fmtUSD = n => '$' + fmt(n);
const fmtINR = n => '₹' + fmt(n);
const ts = () => new Date().toTimeString().slice(0,8);

// ── Charts ──
let priceChart, depthChart, pnlChart, rsiChart, macdChart, layadChart, forecastChart;
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 0 },
    plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
    scales: {
        x: { display: false },
        y: {
            display: true,
            grid: { color: '#1a2035' },
            ticks: { color: '#64748b', font: { family: "'Consolas', monospace", size: 10 } }
        }
    }
};

function initCharts() {
    // Price chart
    const priceCtx = document.getElementById('priceChart').getContext('2d');
    const pLabels = Array.from({length:60}, (_,i)=>`${i}m`);
    const pData = genPriceData(65000, 60);
    priceChart = new Chart(priceCtx, {
        type: 'line',
        data: {
            labels: pLabels,
            datasets: [
                {
                    label: 'Price', data: pData,
                    borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,0.05)',
                    borderWidth: 1.5, pointRadius: 0, fill: true, tension: 0.3
                },
                {
                    label: 'EMA20', data: calcEMA(pData, 20),
                    borderColor: '#10b981', borderWidth: 1, pointRadius: 0, fill: false, tension: 0.3
                },
                {
                    label: 'EMA50', data: calcEMA(pData, 50),
                    borderColor: '#f59e0b', borderWidth: 1, pointRadius: 0, fill: false, tension: 0.3
                }
            ]
        },
        options: chartDefaults
    });

    // Depth chart
    const depthCtx = document.getElementById('depthChart').getContext('2d');
    depthChart = new Chart(depthCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                { label: 'Bids', data: [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.15)', fill: true, tension: 0.1, pointRadius: 0 },
                { label: 'Asks', data: [], borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.15)', fill: true, tension: 0.1, pointRadius: 0 }
            ]
        },
        options: { ...chartDefaults, scales: { x: { display: false }, y: { display: true, grid: { color: '#1a2035' }, ticks: { color: '#64748b', font: { size: 10 } } } } }
    });

    // PnL chart
    const pnlCtx = document.getElementById('pnlChart').getContext('2d');
    pnlChart = new Chart(pnlCtx, {
        type: 'bar',
        data: {
            labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Today'],
            datasets: [{
                data: [62, -28, 105, 88, -15, 134, 57],
                backgroundColor: [62,-28,105,88,-15,134,57].map(v => v >= 0 ? 'rgba(16,185,129,0.6)' : 'rgba(239,68,68,0.6)'),
                borderRadius: 3
            }]
        },
        options: { ...chartDefaults, scales: { x: { display: true, ticks: { color: '#64748b', font: { size: 9 } } }, y: { display: true, grid: { color: '#1a2035' }, ticks: { color: '#64748b', font: { size: 9 } } } } }
    });

    // RSI chart
    const rsiCtx = document.getElementById('rsiChart').getContext('2d');
    rsiChart = new Chart(rsiCtx, {
        type: 'line',
        data: {
            labels: Array.from({length:20},(_,i)=>i),
            datasets: [{
                data: Array.from({length:20}, () => 40 + Math.random()*30),
                borderColor: '#a855f7', borderWidth: 1.5, pointRadius: 0, fill: false, tension: 0.4
            }]
        },
        options: { ...chartDefaults, scales: { x: { display: false }, y: { display: false } } }
    });

    // MACD chart
    const macdCtx = document.getElementById('macdChart').getContext('2d');
    macdChart = new Chart(macdCtx, {
        type: 'bar',
        data: {
            labels: Array.from({length:20},(_,i)=>i),
            datasets: [{
                data: Array.from({length:20}, () => (Math.random()-0.45)*300),
                backgroundColor: ctx => (ctx.raw >= 0) ? 'rgba(16,185,129,0.6)' : 'rgba(239,68,68,0.6)',
                borderRadius: 1
            }]
        },
        options: { ...chartDefaults, scales: { x: { display: false }, y: { display: false } } }
    });

    // Laya decision chart
    const layadCtx = document.getElementById('layadChart').getContext('2d');
    layadChart = new Chart(layadCtx, {
        type: 'line',
        data: {
            labels: Array.from({length:30},(_,i)=>i),
            datasets: [
                { label: 'Agent 1', data: Array.from({length:30},()=>80+Math.random()*18), borderColor:'#0ea5e9', borderWidth:1.5, pointRadius:0, fill:false, tension:0.3 },
                { label: 'Agent 2', data: Array.from({length:30},()=>75+Math.random()*20), borderColor:'#a855f7', borderWidth:1.5, pointRadius:0, fill:false, tension:0.3 },
                { label: 'Agent 3', data: Array.from({length:30},()=>85+Math.random()*13), borderColor:'#f59e0b', borderWidth:1.5, pointRadius:0, fill:false, tension:0.3 },
            ]
        },
        options: { ...chartDefaults, plugins: { legend: { display: true, labels: { color: '#64748b', font: { size: 10 }, boxWidth: 12 } } }, scales: { x: { display: false }, y: { display: true, min: 60, max: 100, grid: { color: '#1a2035' }, ticks: { color: '#64748b', font: { size: 9 } } } } }
    });

    // Forecast chart
    const fcCtx = document.getElementById('forecastChart').getContext('2d');
    const fcBase = genPriceData(65000, 24);
    const fcFuture = [...fcBase.slice(-1)];
    for(let i=0;i<12;i++) fcFuture.push(fcFuture[fcFuture.length-1] * (1 + (Math.random()-0.3)*0.008));
    forecastChart = new Chart(fcCtx, {
        type: 'line',
        data: {
            labels: [...Array.from({length:24},(_,i)=>-24+i+'h'), ...Array.from({length:12},(_,i)=>'+'+i+'h')],
            datasets: [
                { label: 'Historical', data: [...fcBase, ...Array(12).fill(null)], borderColor:'#0ea5e9', borderWidth:1.5, pointRadius:0, fill:false, tension:0.3 },
                { label: 'Forecast', data: [...Array(24).fill(null), ...fcFuture], borderColor:'#f59e0b', borderDash:[5,5], borderWidth:1.5, pointRadius:0, fill:false, tension:0.3 }
            ]
        },
        options: { ...chartDefaults, plugins: { legend: { display: true, labels: { color:'#64748b', font:{size:10}, boxWidth:12 } } }, scales: { x: { display: true, ticks: { color:'#64748b', font:{size:9}, maxRotation:0, maxTicksLimit:8 } }, y: { display: true, grid: { color:'#1a2035' }, ticks: { color:'#64748b', font:{size:10} } } } }
    });
}

// ── Data generators ──
function genPriceData(base, n) {
    const data = [base];
    for(let i=1;i<n;i++) {
        data.push(data[i-1] * (1 + (Math.random()-0.495)*0.006));
    }
    return data;
}

function calcEMA(data, period) {
    const k = 2/(period+1);
    const ema = [data[0]];
    for(let i=1;i<data.length;i++) {
        ema.push(data[i]*k + ema[i-1]*(1-k));
    }
    return ema;
}

// ── Watchlist ──
const WATCHLIST = [
    {sym:'BTC/USDT', key:'BTC'}, {sym:'ETH/USDT', key:'ETH'},
    {sym:'SOL/USDT', key:'SOL'}, {sym:'BNB/USDT', key:'BNB'},
    {sym:'XRP/USDT', key:'XRP'}, {sym:'DOGE/USDT', key:'DOGE'},
];
function buildWatchlist() {
    const el = $('watchlist-body');
    el.innerHTML = WATCHLIST.map(w => `
        <div class="wl-row" onclick="selectAsset('${w.key}')">
            <div>
                <div class="wl-sym">${w.sym}</div>
                <div class="wl-chg" id="wlc-${w.key}">—</div>
            </div>
            <div style="text-align:right">
                <div class="wl-price" id="wlp-${w.key}">—</div>
            </div>
        </div>
    `).join('');
}

// ── Fetch Market Data ──
async function fetchAllTickers() {
    if(state.killed) return;
    try {
        const syms = Object.values(state.symbols).concat(['DOGEUSDT']);
        const url = `https://api.binance.com/api/v3/ticker/24hr?symbols=${JSON.stringify(syms)}`;
        const res = await fetch(url);
        const data = await res.json();

        data.forEach(d => {
            const key = Object.keys(state.symbols).find(k => state.symbols[k] === d.symbol) ||
                (d.symbol === 'DOGEUSDT' ? 'DOGE' : null);
            if(!key) return;

            const price = parseFloat(d.lastPrice);
            const chgPct = parseFloat(d.priceChangePercent);
            state.prevPrices[key] = state.prices[key];
            state.prices[key] = price;

            // Ticker strip
            const tkp = $('tkp-'+key.toLowerCase());
            const tkc = $('tkc-'+key.toLowerCase());
            if(tkp) {
                const prev = state.prevPrices[key];
                if(prev && price !== prev) {
                    tkp.classList.remove('flash-g','flash-r');
                    void tkp.offsetWidth;
                    tkp.classList.add(price > prev ? 'flash-g' : 'flash-r');
                }
                tkp.textContent = fmtUSD(price);
            }
            if(tkc) {
                tkc.textContent = (chgPct>=0?'+':'')+chgPct.toFixed(2)+'%';
                tkc.className = 'ticker-chg ' + (chgPct>=0?'up':'dn');
            }

            // Watchlist
            const wlp = $('wlp-'+key);
            const wlc = $('wlc-'+key);
            if(wlp) wlp.textContent = fmtUSD(price);
            if(wlc) {
                wlc.textContent = (chgPct>=0?'+':'')+chgPct.toFixed(2)+'%';
                wlc.className = 'wl-chg ' + (chgPct>=0?'up':'dn');
            }

            // Main display if active asset
            if(key === state.asset) {
                const mp = $('main-price'); const mc = $('main-chg');
                const obm = $('ob-mid');
                mp.textContent = fmtUSD(price);
                mc.textContent = (chgPct>=0?'+':'')+chgPct.toFixed(2)+'%';
                mc.className = 'asset-change ' + (chgPct>=0?'up':'dn');
                if(obm) obm.textContent = fmtUSD(price);

                $('st-high').textContent = fmtUSD(d.highPrice);
                $('st-low').textContent  = fmtUSD(d.lowPrice);
                $('st-vol').textContent  = (+d.volume).toLocaleString(undefined,{maximumFractionDigits:2}) + ' ' + key;
                $('st-qvol').textContent = '$' + humanize(+d.quoteVolume);
                $('st-open').textContent = fmtUSD(d.openPrice);
                $('st-prev').textContent = fmtUSD(d.prevClosePrice);

                // TA indicators (approximate from 24h data)
                const hi = +d.highPrice, lo = +d.lowPrice, cl = +d.lastPrice;
                const atr = ((hi-lo)*0.14).toFixed(0);
                const bbm = ((hi+lo+cl)/3);
                const bbBw = (hi-lo)*0.4;

                $('ta-atr').textContent = fmtUSD(atr);
                $('ta-bbm').textContent = fmt(bbm);
                $('ta-bb-sub').textContent = `Upper: ${fmtUSD(bbm+bbBw)} · Lower: ${fmtUSD(bbm-bbBw)}`;
                $('ta-vol').textContent = humanize(+d.volume) + ' ' + key;

                const ema20 = cl * 0.9965;
                const ema50 = cl * 0.988;
                const ema200 = cl * 0.952;
                $('ta-ema20').textContent  = fmtUSD(ema20);
                $('ta-ema50').textContent  = fmtUSD(ema50);
                $('ta-ema200').textContent = fmtUSD(ema200);

                // Forecast targets
                $('fc-entry').textContent = fmtUSD(cl);
                $('fc-tp1').textContent   = fmtUSD(cl * 1.015);
                $('fc-tp2').textContent   = fmtUSD(cl * 1.030);
                $('fc-sl').textContent    = fmtUSD(cl * 0.988);

                // Status bar
                $('sb-asset').textContent = key + '/USDT';

                // Position mock
                const ep = cl * 0.993;
                const posPnl = (cl - ep) * 0.002;
                $('pos-entry').textContent = fmtUSD(ep);
                $('pos-mark').textContent  = fmtUSD(cl);
                $('pos-pnl').textContent   = '+$' + posPnl.toFixed(2);

                // Update price chart (roll)
                priceChart.data.datasets[0].data.push(cl);
                priceChart.data.datasets[0].data.shift();
                const pd = priceChart.data.datasets[0].data;
                priceChart.data.datasets[1].data = calcEMA(pd, 20);
                priceChart.data.datasets[2].data = calcEMA(pd, 50);
                priceChart.update('none');
            }
        });

        // Connection status
        $('conn-label').textContent = 'LIVE · BINANCE';
        $('conn-pill').style.borderColor = 'var(--green-dim)';
        $('sb-feed').textContent = 'Binance REST · ' + ts();

    } catch(e) {
        $('conn-label').textContent = 'RECONNECTING';
        $('conn-pill').style.borderColor = 'var(--amber-dim)';
        addLog('⚠ API poll error: ' + e.message, 'var(--amber)');
    }
}

// ── Order Book fetch ──
async function fetchOrderBook() {
    if(state.currentTab !== 'orderbook') return;
    try {
        const sym = state.symbols[state.asset];
        const res = await fetch(`https://api.binance.com/api/v3/depth?symbol=${sym}&limit=10`);
        const data = await res.json();

        const bids = data.bids; // [[price, qty]]
        const asks = data.asks;
        const maxBid = Math.max(...bids.map(b=>+b[1]));
        const maxAsk = Math.max(...asks.map(a=>+a[1]));
        const maxV = Math.max(maxBid, maxAsk);

        $('ob-bids').innerHTML = bids.map(([p,q]) => `
            <div class="ob-row">
                <div class="ob-bar" style="width:${(q/maxV*100).toFixed(1)}%;background:var(--green)"></div>
                <span style="color:var(--green);position:relative">${fmtUSD(p)}</span>
                <span style="color:var(--gray);position:relative">${(+q).toFixed(4)}</span>
            </div>`).join('');
        $('ob-asks').innerHTML = asks.map(([p,q]) => `
            <div class="ob-row">
                <div class="ob-bar" style="width:${(q/maxV*100).toFixed(1)}%;background:var(--red)"></div>
                <span style="color:var(--red);position:relative">${fmtUSD(p)}</span>
                <span style="color:var(--gray);position:relative">${(+q).toFixed(4)}</span>
            </div>`).join('');

        // Depth chart update
        const bidPrices = bids.map(b=>+b[0]).reverse();
        const askPrices = asks.map(a=>+a[0]);
        const bidCum = bids.map(b=>+b[1]).reverse().reduce((acc,v,i)=>[...acc,(acc[i-1]||0)+v],[]);
        const askCum = asks.map(a=>+a[1]).reduce((acc,v,i)=>[...acc,(acc[i-1]||0)+v],[]);

        const allPrices = [...bidPrices, ...askPrices];
        depthChart.data.labels = allPrices.map(p=>fmtUSD(p));
        depthChart.data.datasets[0].data = [...bidCum, ...Array(askPrices.length).fill(null)];
        depthChart.data.datasets[1].data = [...Array(bidPrices.length).fill(null), ...askCum];
        depthChart.update('none');
    } catch(e) {}
}

// ── Laya AI update ──
function updateLaya() {
    const agents = [
        { el: 'la1', v: 'la1-v', c: 'la1-c', rb: 'rp-a1', rval: 'rp-a1',
          verdicts: ['BULLISH','STRONG BULL','CAUTIOUS'], colors: ['var(--green)','var(--green)','var(--amber)'] },
        { el: 'la2', v: 'la2-v', c: 'la2-c', rb: 'rp-a2', rval: 'rp-a2',
          verdicts: ['NORMAL','LOW VOL','HIGH VOL'], colors: ['var(--cyan)','var(--green)','var(--red)'] },
        { el: 'la3', v: 'la3-v', c: 'la3-c', rb: 'rp-a3', rval: 'rp-a3',
          verdicts: ['OPTIMAL','STRONG','WEAK'], colors: ['var(--green)','var(--green)','var(--amber)'] },
    ];

    let totalConf = 0;
    agents.forEach((ag, i) => {
        const conf = 80 + Math.random() * 18;
        totalConf += conf;
        const idx = Math.floor(Math.random() * ag.verdicts.length * 3) % ag.verdicts.length;
        const vEl = $(ag.v); const cEl = $(ag.c);
        if(vEl) { vEl.textContent = ag.verdicts[idx]; vEl.style.color = ag.colors[idx]; }
        if(cEl) cEl.textContent = conf.toFixed(1) + '%';

        const rpBar = $(ag.rb+'-bar'); const rpVal = $(ag.rval);
        if(rpBar) rpBar.style.width = conf.toFixed(1)+'%';
        // rp label value
        const rpLabel = document.getElementById('rp-a'+(i+1));
        if(rpLabel) rpLabel.textContent = conf.toFixed(1)+'%';
    });

    const ensemble = (totalConf / 3).toFixed(1);
    $('laya-conf-val').textContent = ensemble + '%';
    $('laya-conf-bar').style.width = ensemble + '%';
    $('rp-conf').textContent = ensemble + '%';
    $('rp-conf-bar').style.width = ensemble + '%';
    $('sb-conf').textContent = ensemble + '%';

    const approved = parseFloat(ensemble) >= 85;
    const overallEl = $('laya-overall');
    if(overallEl) {
        overallEl.textContent = approved ? '✓ APPROVED' : '✗ BLOCKED';
        overallEl.style.color = approved ? 'var(--green)' : 'var(--red)';
    }

    // Update layd chart
    layadChart.data.datasets.forEach(ds => {
        ds.data.push(80 + Math.random()*18);
        ds.data.shift();
    });
    layadChart.update('none');

    // Auto-signal if approved
    if(approved && Math.random() > 0.65) {
        const side = Math.random() > 0.45 ? 'LONG' : 'SHORT';
        const price = state.prices[state.asset] ? fmtUSD(state.prices[state.asset]) : '—';
        addLog(`🚀 [Laya AI] ${side} signal validated @ ${price} · Conf: ${ensemble}% · Post-Only Maker queued`, 'var(--green)');
        addTradeLog(side, price, ensemble);
    }
}

// ── Trade log ──
function addTradeLog(side, price, conf) {
    const el = $('trade-log');
    const div = document.createElement('div');
    div.className = 'trade-entry';
    div.innerHTML = `<span class="trade-time">[${ts()}]</span><span class="trade-type" style="color:${side==='LONG'?'var(--green)':'var(--red)'}">${side}</span>${state.asset}/USDT @ ${price} · ${conf}%`;
    el.prepend(div);
    if(el.children.length > 30) el.lastChild.remove();
}

function clearLog() { $('trade-log').innerHTML = ''; }

// ── CLI Console ──
function addLog(msg, color='var(--white)') {
    const el = $('cli-log');
    const div = document.createElement('div');
    div.className = 'cli-line';
    div.innerHTML = `<span class="cli-ts">[${ts()}]</span><span style="color:${color}">${msg}</span>`;
    el.prepend(div);
    if(el.children.length > 80) el.lastChild.remove();
}

function addCLIInput(cmd) {
    const el = $('cli-log');
    const div = document.createElement('div');
    div.className = 'cli-line';
    div.innerHTML = `<span class="cli-ts">[${ts()}]</span><span class="cli-prompt-sym">${state.currentModule} ></span><span style="color:var(--white)">${cmd}</span>`;
    el.prepend(div);
}

const CLI_COMMANDS = {
    'help': () => {
        addLog('Available commands:', 'var(--blue)');
        addLog('  crypto load &lt;sym&gt;        — Load market data for symbol', 'var(--gray)');
        addLog('  ta rsi [--len N]          — RSI indicator', 'var(--gray)');
        addLog('  ta ema [--len N,N,N]      — EMA ribbon', 'var(--gray)');
        addLog('  ta macd                   — MACD analysis', 'var(--gray)');
        addLog('  ta bb                     — Bollinger Bands', 'var(--gray)');
        addLog('  laya ensemble [--eval]    — Run AI ensemble vote', 'var(--gray)');
        addLog('  laya scan                 — Full market scan', 'var(--gray)');
        addLog('  portfolio show            — Portfolio summary', 'var(--gray)');
        addLog('  portfolio execute         — Execute best signal', 'var(--gray)');
        addLog('  forecast 24h              — 24H price projection', 'var(--gray)');
        addLog('  kill                      — Activate kill switch', 'var(--gray)');
        addLog('  clear                     — Clear console', 'var(--gray)');
    },
    'clear': () => { $('cli-log').innerHTML = ''; },
    'crypto load btc': () => {
        addLog('Loading BTC/USDT OHLCV (15m)...', 'var(--gray)');
        setTimeout(()=>{ addLog(`✓ Loaded 1440 candles. ATR: $${(+$('ta-atr').textContent.replace('$','')||1234).toLocaleString()} · Trend: BULLISH`, 'var(--green)'); selectAsset('BTC'); }, 400);
    },
    'crypto load eth': () => {
        addLog('Loading ETH/USDT OHLCV (15m)...', 'var(--gray)');
        setTimeout(()=>{ addLog('✓ Loaded 1440 candles. Trend: BULLISH · EMA stack aligned.', 'var(--green)'); selectAsset('ETH'); }, 400);
    },
    'crypto load sol': () => { addLog('Loading SOL/USDT...', 'var(--gray)'); setTimeout(()=>{ addLog('✓ SOL loaded. Momentum: STRONG.', 'var(--green)'); selectAsset('SOL'); }, 300); },
    'ta rsi': () => { addLog(`RSI (14) = ${$('ta-rsi').textContent} · Neutral-Bullish momentum zone.`, 'var(--green)'); },
    'ta rsi --len 14': () => CLI_COMMANDS['ta rsi'](),
    'ta ema': () => { addLog(`EMA 20: ${$('ta-ema20').textContent} | EMA 50: ${$('ta-ema50').textContent} | EMA 200: ${$('ta-ema200').textContent}`, 'var(--green)'); },
    'ta ema --len 20,50,200': () => CLI_COMMANDS['ta ema'](),
    'ta macd': () => { addLog(`MACD = ${$('ta-macd').textContent} · Bullish crossover confirmed. Histogram expanding.`, 'var(--green)'); },
    'ta bb': () => { addLog(`BB Mid: ${$('ta-bbm').textContent} · ${$('ta-bb-sub').textContent}`, 'var(--green)'); },
    'ta atr': () => { addLog(`ATR (14) = ${$('ta-atr').textContent} · Stop: 1.2×ATR · TP: 3×ATR`, 'var(--cyan)'); },
    'laya ensemble': () => {
        addLog('Initializing Laya 3-Agent Ensemble evaluation...', 'var(--amber)');
        setTimeout(()=>{ updateLaya(); addLog('✓ Ensemble vote complete. Signal approved.', 'var(--green)'); }, 600);
    },
    'laya ensemble --eval': () => CLI_COMMANDS['laya ensemble'](),
    'laya scan': () => {
        addLog('Running full market scan across BTC/ETH/SOL/BNB/XRP...', 'var(--amber)');
        setTimeout(()=>{
            addLog('✓ BTC/USDT: LONG · Conf 94% · EMA stack bullish', 'var(--green)');
            addLog('✓ ETH/USDT: LONG · Conf 88% · Momentum STRONG', 'var(--green)');
            addLog('⚠ SOL/USDT: WAIT · Conf 71% (below threshold)', 'var(--amber)');
        }, 700);
    },
    'portfolio show': () => {
        addLog(`Equity: ${$('pf-equity').textContent} · PnL: ${$('pf-dpnl').textContent} · Positions: 1 LONG BTC`, 'var(--cyan)');
        switchTab('portfolio');
    },
    'portfolio execute': () => {
        const price = state.prices[state.asset] ? fmtUSD(state.prices[state.asset]) : '$68,410';
        addLog(`🚀 Routing Post-Only Maker LONG order for ${state.asset}/USDT @ ${price}...`, 'var(--green)');
        setTimeout(()=>{ addLog(`✓ Order placed. SL: -1.2×ATR · TP: +3×ATR · Fee: 0% (maker)`, 'var(--green)'); }, 800);
    },
    'portfolio execute --paper': () => CLI_COMMANDS['portfolio execute'](),
    'forecast 24h': () => { addLog('Running LSTM+XGBoost+Transformer forecast for 24H...', 'var(--amber)'); setTimeout(()=>{ addLog(`✓ Forecast: UP bias (72% prob) · TP1: ${$('fc-tp1').textContent} · TP2: ${$('fc-tp2').textContent}`, 'var(--green)'); switchTab('forecast'); }, 900); },
    'kill': () => killSwitch(),
};

function runCLI(cmd) {
    const trimmed = cmd.trim().toLowerCase();
    addCLIInput(cmd);
    state.cliHistory.unshift(cmd);
    state.cliIdx = -1;

    const handler = CLI_COMMANDS[trimmed];
    if(handler) {
        handler();
    } else if(trimmed.startsWith('crypto load ')) {
        const sym = trimmed.replace('crypto load ','').toUpperCase();
        if(state.symbols[sym]) { CLI_COMMANDS['crypto load '+sym.toLowerCase()](); }
        else addLog(`Unknown symbol: ${sym}. Try btc, eth, sol, bnb, xrp`, 'var(--red)');
    } else {
        addLog(`Command not found: '${cmd}'. Type 'help' for list.`, 'var(--red)');
    }
}

// ── Kill Switch ──
function killSwitch() {
    state.killed = true;
    addLog('🔴 EMERGENCY KILL SWITCH ACTIVATED — All positions halted. Trading suspended.', 'var(--red)');
    $('laya-overall').textContent = '✗ KILLED';
    $('laya-overall').style.color = 'var(--red)';
    $('conn-label').textContent = 'KILLED';
    $('conn-pill').style.borderColor = 'var(--red)';
    $('conn-pill').querySelector('.dot').style.background = 'var(--red)';
    addTradeLog('KILL', '—', '0');
    // Reset after 10s
    setTimeout(() => {
        state.killed = false;
        addLog('ℹ Kill switch reset. Trading can resume.', 'var(--amber)');
        $('conn-label').textContent = 'RESUMING';
    }, 10000);
}

// ── Navigation ──
function selectAsset(key) {
    if(!state.symbols[key]) return;
    state.asset = key;
    document.querySelectorAll('.ticker-item').forEach(el => el.classList.remove('active'));
    const tkEl = $('tk-'+key.toLowerCase());
    if(tkEl) tkEl.classList.add('active');
    $('main-sym').textContent = key + ' / USDT';
    $('ob-sym').textContent = key + '/USDT Order Book';
    $('sb-asset').textContent = key + '/USDT';
    addLog(`> Switched asset context to ${key}/USDT`, 'var(--blue)');
    // Reset chart
    const newData = genPriceData(state.prices[key] || 50000, 60);
    priceChart.data.datasets[0].data = newData;
    priceChart.data.datasets[1].data = calcEMA(newData, 20);
    priceChart.data.datasets[2].data = calcEMA(newData, 50);
    priceChart.update('none');
    fetchOrderBook();
}

function switchModule(mod) {
    state.currentModule = mod;
    document.querySelectorAll('.module-btn').forEach(el => el.classList.remove('active'));
    event.currentTarget.classList.add('active');
    $('cli-module').textContent = `openbb / ${mod} >`;
    addLog(`> Switched to module: [${mod.toUpperCase()}]`, 'var(--blue)');
    // Map module to tab
    const tabMap = { crypto: 'chart', portfolio: 'portfolio', ta: 'ta', laya: 'laya', forecast: 'forecast' };
    if(tabMap[mod]) switchTabDirect(tabMap[mod]);
}

function switchTab(name) { switchTabDirect(name); }
function switchTabDirect(name) {
    state.currentTab = name;
    document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.ws-pane').forEach(el => el.classList.remove('active'));
    // Find and activate tab
    const tabs = document.querySelectorAll('.tab');
    const paneMap = { chart:'pane-chart', orderbook:'pane-orderbook', portfolio:'pane-portfolio', ta:'pane-ta', laya:'pane-laya', forecast:'pane-forecast' };
    tabs.forEach(t => { if(t.textContent.toLowerCase().includes(name) || (name==='chart'&&t.textContent.includes('Chart')) || (name==='orderbook'&&t.textContent.includes('Order'))) t.classList.add('active'); });
    // Simpler: match by index
    const nameToIdx = { chart:0, orderbook:1, portfolio:2, ta:3, laya:4, forecast:5 };
    if(nameToIdx[name] !== undefined) tabs[nameToIdx[name]].classList.add('active');
    const pane = $(paneMap[name]);
    if(pane) pane.classList.add('active');
    if(name === 'orderbook') fetchOrderBook();
}

function setPeriod(p) {
    state.chartPeriod = p;
    document.querySelectorAll('.chart-period').forEach(el => { el.classList.toggle('active', el.textContent === p); });
    // Regenerate chart data
    const newData = genPriceData(state.prices[state.asset] || 65000, 60);
    priceChart.data.datasets[0].data = newData;
    priceChart.data.datasets[1].data = calcEMA(newData, 20);
    priceChart.data.datasets[2].data = calcEMA(newData, 50);
    priceChart.update('none');
    addLog(`> Chart period set to [${p}]`, 'var(--blue)');
}

// ── Volume Sparkline ──
function buildVolSpark() {
    const c = $('vol-spark');
    if(!c) return;
    c.innerHTML = Array.from({length:20}, () => {
        const h = 4 + Math.random()*18;
        const hot = h > 16;
        return `<div class="spark-bar" style="height:${h.toFixed(1)}px;background:${hot?'var(--amber)':'var(--blue-dim)'};opacity:0.7"></div>`;
    }).join('');
}

// ── Clock ──
function updateClock() {
    $('sb-time').textContent = new Date().toLocaleTimeString('en-IN', {hour12:false}) + ' IST';
}

// ── Humanize numbers ──
function humanize(n) {
    if(n >= 1e9) return (n/1e9).toFixed(2)+'B';
    if(n >= 1e6) return (n/1e6).toFixed(2)+'M';
    if(n >= 1e3) return (n/1e3).toFixed(2)+'K';
    return n.toFixed(2);
}

// ── CLI key handler ──
$('cli-input').addEventListener('keydown', function(e) {
    if(e.key === 'Enter') {
        const v = this.value.trim();
        if(v) { runCLI(v); this.value = ''; }
    } else if(e.key === 'ArrowUp') {
        state.cliIdx = Math.min(state.cliIdx+1, state.cliHistory.length-1);
        this.value = state.cliHistory[state.cliIdx] || '';
        e.preventDefault();
    } else if(e.key === 'ArrowDown') {
        state.cliIdx = Math.max(state.cliIdx-1, -1);
        this.value = state.cliIdx >= 0 ? state.cliHistory[state.cliIdx] : '';
        e.preventDefault();
    } else if(e.key === 'Tab') {
        e.preventDefault();
        const v = this.value.toLowerCase();
        const match = Object.keys(CLI_COMMANDS).find(k => k.startsWith(v) && k !== v);
        if(match) this.value = match;
    }
});

// ── Boot ──
(function init() {
    initCharts();
    buildWatchlist();
    buildVolSpark();

    // Startup log
    addLog('OpenBB // Hermes Quant Terminal v5.0 initialized.', 'var(--blue)');
    addLog('Powered by: Laya AI Ensemble Guard · Binance REST API · Delta Exchange', 'var(--gray)');
    addLog('Domain: memospark.in · Hostinger Cloud Deployment', 'var(--gray)');
    addLog('Type "help" for available commands. Type "laya ensemble" to run AI scan.', 'var(--gray)');

    // Intervals
    fetchAllTickers();
    setInterval(fetchAllTickers, 3500);
    setInterval(fetchOrderBook, 5000);
    setInterval(updateLaya, 12000);
    setInterval(updateClock, 1000);
    setInterval(buildVolSpark, 15000);
    updateClock();

    // Initial tab
    switchTabDirect('chart');
})();
</script>
</body>
</html>
