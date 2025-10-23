@echo off
REM Run multiple Kafka GUI instances

REM Set classpath với tất cả jar trong thư mục lib
set CLASSPATH=.
for %%f in (lib\*.jar) do call set CLASSPATH=%%CLASSPATH%%;%%f

REM Start multiple GUI instances
echo Starting Producer 1...
start cmd /k "java -cp %CLASSPATH% KafkaGui"

echo Starting Producer 2...
start cmd /k "java -cp %CLASSPATH% KafkaGui"

echo Starting Producer 3...
start cmd /k "java -cp %CLASSPATH% KafkaGui"

echo Started 3 producers. You can close any window to stop that producer.