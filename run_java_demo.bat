@echo off
REM Compile and run Java Kafka demo

REM Kiểm tra môi trường
IF NOT EXIST "lib" mkdir lib
IF NOT EXIST "lib\kafka-clients-3.6.0.jar" (
    echo Downloading Kafka libraries...
    curl -o lib\kafka-clients-3.6.0.jar https://repo1.maven.org/maven2/org/apache/kafka/kafka-clients/3.6.0/kafka-clients-3.6.0.jar
    curl -o lib\slf4j-api-2.0.7.jar https://repo1.maven.org/maven2/org/slf4j/slf4j-api/2.0.7/slf4j-api-2.0.7.jar
    curl -o lib\slf4j-simple-2.0.7.jar https://repo1.maven.org/maven2/org/slf4j/slf4j-simple/2.0.7/slf4j-simple-2.0.7.jar
)

REM Set classpath với tất cả jar trong thư mục lib
set CLASSPATH=.
for %%f in (lib\*.jar) do call set CLASSPATH=%%CLASSPATH%%;%%f

REM Compile
echo Compiling...
javac -cp "%CLASSPATH%" SimpleProducer.java SimpleConsumer.java

REM Run producer in one window
start cmd /k "echo Running producer... && java -cp %CLASSPATH% SimpleProducer"

REM Run consumer in another window
start cmd /k "echo Running consumer... && java -cp %CLASSPATH% SimpleConsumer"