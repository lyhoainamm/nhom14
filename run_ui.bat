@echo off
setlocal
cd /d %~dp0

rem Compile UI (requires JDK 17+)
javac -encoding UTF-8 KafkaGui.java || goto :eof

rem Run UI
java KafkaGui


