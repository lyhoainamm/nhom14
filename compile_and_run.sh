#!/bin/bash

echo "=== Kafka Demo Script ==="
echo "Script này sẽ biên dịch và chạy Producer/Consumer"
echo ""

# Kiểm tra Kafka server có chạy không
echo "1. Kiểm tra Kafka server..."
if pgrep -f "KafkaRaftServer" > /dev/null || pgrep -f "kafka" > /dev/null; then
    echo "✓ Kafka server đang chạy"
else
    echo "❌ Kafka server chưa chạy. Vui lòng khởi động trước:"
    echo "   ./bin/kafka-server-start.sh config/server.properties"
    exit 1
fi

# Tạo topic nếu chưa có
echo ""
echo "2. Tạo topic 'test-topic'..."
./bin/kafka-topics.sh --create --topic test-topic --bootstrap-server localhost:9092 --partitions 3 --replication-factor 1 2>/dev/null || echo "Topic đã tồn tại"

# Biên dịch Java files
echo ""
echo "3. Biên dịch Java files..."

# Tìm classpath cho Kafka clients và dependencies
CLASSPATH=""
for jar in $(find . -name "*.jar" | grep -E "(clients|core|slf4j|log4j|jackson)" | head -15); do
    CLASSPATH="$CLASSPATH:$jar"
done

echo "Classpath: $CLASSPATH"

# Biên dịch Producer
echo "Biên dịch SimpleProducer..."
javac -cp "$CLASSPATH" SimpleProducer.java

# Biên dịch Consumer  
echo "Biên dịch SimpleConsumer..."
javac -cp "$CLASSPATH" SimpleConsumer.java

if [ $? -eq 0 ]; then
    echo "✓ Biên dịch thành công!"
else
    echo "❌ Lỗi biên dịch"
    exit 1
fi

echo ""
echo "4. Hướng dẫn chạy:"
echo ""
echo "Terminal 1 - Chạy Consumer (nhận tin nhắn):"
echo "   java -cp \".:$CLASSPATH\" SimpleConsumer"
echo ""
echo "Terminal 2 - Chạy Producer (gửi tin nhắn):"
echo "   java -cp \".:$CLASSPATH\" SimpleProducer"
echo ""
echo "Lưu ý: Chạy Consumer trước, sau đó chạy Producer để thấy tin nhắn"
echo ""
echo "Để dừng: Nhấn Ctrl+C trong terminal chạy Consumer"
