@echo off
setlocal enabledelayedexpansion
echo Starting Kafka Demo...

REM Resolve repository root relative to this script (kafka-development directory)
set "ROOT=%~dp0"
REM Normalize trailing backslash is present already in %~dp0

REM Ensure any previous Java/Kafka processes are not blocking ports
taskkill /f /im java.exe 2>nul

REM Start Kafka server in a new window using repo-local scripts and configs
start "Kafka Server" cmd /k "cd /d %ROOT% && bin\windows\kafka-server-start.bat config\server.properties"

REM Give the broker time to start
timeout /t 10 /nobreak >nul

REM Create demo topic if it doesn't exist
cd /d %ROOT%
bin\windows\kafka-topics.bat --create --if-not-exists --topic test-topic --bootstrap-server localhost:9092 --partitions 3 --replication-factor 1 1>nul 2>nul

REM Start a console consumer in a new window
start "Consumer" cmd /k "cd /d %ROOT% && bin\windows\kafka-console-consumer.bat --topic test-topic --bootstrap-server localhost:9092 --from-beginning"

REM Give consumer a moment to attach
timeout /t 3 /nobreak >nul

REM Produce a few sample messages into the topic using perf-test tool (non-interactive)
bin\windows\kafka-producer-perf-test.bat --topic test-topic --num-records 5 --throughput 5 --record-size 16 --producer-props bootstrap.servers=localhost:9092 1>nul

echo Demo completed!
pause
