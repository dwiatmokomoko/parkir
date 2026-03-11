#!/bin/bash

echo "==================================="
echo "Starting Redis Server"
echo "==================================="

# Start Redis server
echo "[1/3] Starting Redis server..."
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Check Redis status
echo "[2/3] Checking Redis status..."
sudo systemctl status redis-server --no-pager

# Test Redis connection
echo "[3/3] Testing Redis connection..."
redis-cli ping

echo ""
echo "==================================="
echo "Redis is ready!"
echo "==================================="
