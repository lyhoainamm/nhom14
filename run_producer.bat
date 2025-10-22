@echo off
REM Build producer jar and run container passing message as argument
cd /d %~dp0\producer
mvn -q package
if %ERRORLEVEL% NEQ 0 (
  echo Maven build failed
  exit /b 1
)
REM Build image
docker build -t nhom14/simple-producer:latest .
if %ERRORLEVEL% NEQ 0 (
  echo Docker build failed
  exit /b 1
)
REM Run container on same network as compose (default network name inferred)
set MESSAGE=%~1
if "%MESSAGE%"=="" set MESSAGE=Hello-from-run_producer
docker run --rm --network nhom14_default nhom14/simple-producer:latest "%MESSAGE%"
