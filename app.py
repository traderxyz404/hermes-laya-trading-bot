import os
import time
import random
import logging
from flask import Flask

logging.basicConfig(level=logging.INFO, format='%(asctime)s - [HERMES-CLOUD] - %(levelname)s - %(message)s')

app = Flask(__name__)

@app.route('/')
def home():
    return "Hermes v5.0 Laya Quant Trading System is Active & Trading 24/7", 200

@app.route('/health')
def health():
    return {"status": "healthy", "engine": "running", "strategy": "v5.0-laya-ensemble"}, 200

if __name__ == '__main__':
    port = int(os.environ.get("PORT", 10000))
    logging.info(f"Starting Hermes cloud web service on port {port}...")
    app.run(host="0.0.0.0", port=port, debug=False)
