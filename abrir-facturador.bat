@echo off
setlocal

cd /d "%~dp0"

docker compose version >nul 2>&1
if %errorlevel%==0 (
    docker compose up -d --build
) else (
    docker-compose up -d --build
)

start http://localhost:8000
