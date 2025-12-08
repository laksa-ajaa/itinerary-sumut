#!/bin/bash

# Script untuk menjalankan semua service sekaligus
# Laravel (artisan serve), Vite (npm run dev), dan Reco Service (Python FastAPI)

echo "🚀 Starting all services..."
echo ""

# Check if virtual environment exists for reco service
if [ ! -d "reco_service/.venv" ]; then
    echo "⚠️  Virtual environment tidak ditemukan di reco_service/.venv"
    echo "   Membuat virtual environment..."
    cd reco_service
    python3 -m venv .venv
    source .venv/bin/activate
    pip install -r requirements.txt
    cd ..
    echo "✅ Virtual environment berhasil dibuat"
    echo ""
fi

# Function to cleanup on exit
cleanup() {
    echo ""
    echo "🛑 Stopping all services..."
    kill $(jobs -p) 2>/dev/null
    exit
}

# Trap SIGINT and SIGTERM
trap cleanup SIGINT SIGTERM

# Start Laravel server
echo "📦 Starting Laravel server (port 8000)..."
php artisan serve &
LARAVEL_PID=$!

# Start Vite dev server
echo "⚡ Starting Vite dev server (port 5173)..."
npm run dev &
VITE_PID=$!

# Start Reco Service
echo "🐍 Starting Reco Service (port 8001)..."
cd reco_service
source .venv/bin/activate
uvicorn main:app --host 0.0.0.0 --port 8001 --reload &
RECO_PID=$!
cd ..

echo ""
echo "✅ All services started!"
echo ""
echo "📍 Services running on:"
echo "   - Laravel:  http://localhost:8000"
echo "   - Vite:     http://localhost:5173"
echo "   - Reco API: http://localhost:8001"
echo ""
echo "Press Ctrl+C to stop all services"
echo ""

# Wait for all background jobs
wait

