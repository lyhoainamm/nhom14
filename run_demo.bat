@echo off
echo Starting Kafka Demo...

taskkill /f /im java.exe 2>nul

start "Kafka Server" cmd /k "cd /d C:\kafka && bin\windows\kafka-server-start.bat config\server.properties"

timeout /t 10 /nobreak >nul

cd /d C:\kafka
bin\windows\kafka-topics.bat --create --topic test-topic --bootstrap-server localhost:9092 --partitions 3 --replication-factor 1 2>nul

javac -cp "libs\*" SimpleProducer.java SimpleConsumer.java

start "Consumer" cmd /k "cd /d C:\kafka && java -cp ".;libs\*" SimpleConsumer"

timeout /t 3 /nobreak >nul

java -cp ".;libs\*" SimpleProducer

echo Demo completed!
pause
