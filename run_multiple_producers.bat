@echo off
REM Run multiple Kafka producers

REM Set classpath với tất cả jar trong thư mục lib
set CLASSPATH=.
for %%f in (lib\*.jar) do call set CLASSPATH=%%CLASSPATH%%;%%f

REM Compile
echo Compiling MultiProducer...
javac -cp "%CLASSPATH%" MultiProducer.java

REM Start multiple producer instances with different IDs
start cmd /k "java -cp %CLASSPATH% MultiProducer P1"
start cmd /k "java -cp %CLASSPATH% MultiProducer P2"
start cmd /k "java -cp %CLASSPATH% MultiProducer P3"

echo Started 3 producers. Type messages in each window and see them in the consumer.