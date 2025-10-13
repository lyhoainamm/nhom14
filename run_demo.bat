@echo off
echo Starting Kafka Demo...

REM Kill existing processes
taskkill /f /im java.exe 2>nul

REM Start Kafka server
start "Kafka Server" cmd /k "cd /d C:\kafka && bin\windows\kafka-server-start.bat config\server.properties"

REM Wait for server to start
timeout /t 10 /nobreak >nul

REM Create topic
cd /d C:\kafka
bin\windows\kafka-topics.bat --create --topic test-topic --bootstrap-server localhost:9092 --partitions 3 --replication-factor 1 2>nul

REM Compile Java files
javac -cp "libs\*" SimpleProducer.java SimpleConsumer.java

REM Start Consumer in new window
start "Consumer" cmd /k "cd /d C:\kafka && java -cp ".;libs\*" SimpleConsumer"

REM Wait a bit
timeout /t 3 /nobreak >nul

REM Start Producer
java -cp ".;libs\*" SimpleProducer

echo Demo completed!
pause
