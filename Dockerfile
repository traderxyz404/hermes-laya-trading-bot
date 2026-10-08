FROM python:3.10-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

# Run the 24/7 continuous trading background worker
CMD ["python", "continuous_paper_trader.py"]
