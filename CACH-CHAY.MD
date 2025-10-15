# Cách chạy nhanh (1 lệnh duy nhất, Docker, Windows cmd.exe)

Yêu cầu:
- Đã cài và mở Docker Desktop (trạng thái Running).
- Mở Command Prompt (cmd.exe), không dùng PowerShell.

Chạy 1 lệnh duy nhất dưới đây để: bật Kafka 1-node, chờ ngắn, tạo topic, gửi 1 message, đọc 2 message rồi tự thoát.

```bat
cmd.exe /c "cd /d C:\Users\Laptop\Desktop\kafka\kafka-development && set IMAGE=apache/kafka:latest&& docker compose -f docker\examples\docker-compose-files\single-node\plaintext\docker-compose.yml up -d && timeout /t 5 >nul && docker exec broker /opt/kafka/bin/kafka-topics.sh --create --if-not-exists --topic test-topic --bootstrap-server localhost:9092 --partitions 3 --replication-factor 1 && echo Hello from one-liner | docker exec -i broker /opt/kafka/bin/kafka-console-producer.sh --topic test-topic --bootstrap-server localhost:9092 && docker exec broker /opt/kafka/bin/kafka-console-consumer.sh --topic test-topic --bootstrap-server localhost:9092 --from-beginning --max-messages 2 --timeout-ms 5000"
```

Tùy chọn dừng stack khi xong:

```bat
docker compose -f C:\Users\Laptop\Desktop\kafka\kafka-development\docker\examples\docker-compose-files\single-node\plaintext\docker-compose.yml down
```

## Chạy GIAO DIỆN (1 lệnh duy nhất)

- Yêu cầu: Docker Desktop đang chạy.
- Lệnh dưới đây sẽ: bật Kafka 1-node bằng Docker, chờ ngắn, rồi compile + mở GUI (`KafkaGui`):

```bat
cmd.exe /c "cd /d C:\Users\Laptop\Desktop\kafka\kafka-development && set IMAGE=apache/kafka:latest&& docker compose -f docker\examples\docker-compose-files\single-node\plaintext\docker-compose.yml up -d && timeout /t 3 >nul && run_ui.bat"
```

- Nếu broker đã chạy sẵn và bạn chỉ muốn mở GUI:

```bat
cmd.exe /c "cd /d C:\Users\Laptop\Desktop\kafka\kafka-development && run_ui.bat"
```


