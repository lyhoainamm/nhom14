@echo off
setlocal
cd /d %~dp0
docker compose up -d --build
REM chờ các service sẵn sàng
timeout /t 8 >nul
start "" http://localhost:8080/index.php
endlocal
